<?php
require_once __DIR__ . '/Database.php';

class BrowseCatalog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getCatalog($params = []) {
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, min(24, (int)($params['per_page'] ?? 12)));
        $search = trim((string)($params['search'] ?? ''));
        $genre = strtoupper(trim((string)($params['genre'] ?? '')));
        $availableOnly = (int)($params['available_only'] ?? 1) === 1;
        $sort = trim((string)($params['sort'] ?? 'recent'));

        $where = [];
        $bindTypes = '';
        $bindValues = [];

        if ($availableOnly) {
            $where[] = 'b.number_of_copies > 0';
        }

        if ($search !== '') {
            $where[] = '(b.name LIKE ? OR b.publisher LIKE ? OR b.isbn LIKE ? OR EXISTS (
                SELECT 1
                FROM BookAuthors ba2
                INNER JOIN Authors a2 ON a2.id = ba2.author_id
                WHERE ba2.book_id = b.id
                  AND CONCAT(a2.first_name, \' \', a2.last_name) LIKE ?
            ))';
            $q = '%' . $search . '%';
            $bindTypes .= 'ssss';
            $bindValues[] = $q;
            $bindValues[] = $q;
            $bindValues[] = $q;
            $bindValues[] = $q;
        }

        if ($genre !== '' && $genre !== 'ALL') {
            $where[] = 'b.genre = ?';
            $bindTypes .= 's';
            $bindValues[] = $genre;
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $orderSql = $this->resolveOrderBy($sort);
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) AS total FROM Books b {$whereSql}";
        $countStmt = $this->db->prepare($countSql);
        if ($bindTypes !== '') {
            $this->bindDynamic($countStmt, $bindTypes, $bindValues);
        }
        $countStmt->execute();
        $countRow = $countStmt->get_result()->fetch_assoc();
        $total = (int)($countRow['total'] ?? 0);

        $sql = "
            SELECT
                b.id,
                b.isbn,
                b.name,
                b.description,
                b.publisher,
                b.genre,
                b.language,
                b.number_of_copies,
                b.price,
                b.cover_image,
                b.created_at,
                COALESCE(ROUND(AVG(br.rating), 1), 0) AS rating,
                COALESCE(GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', '), '') AS authors
            FROM Books b
            LEFT JOIN BookReviews br ON br.book_id = b.id
            LEFT JOIN BookAuthors ba ON ba.book_id = b.id
            LEFT JOIN Authors a ON a.id = ba.author_id
            {$whereSql}
            GROUP BY b.id
            {$orderSql}
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($sql);
        $types = $bindTypes . 'ii';
        $values = $bindValues;
        $values[] = $perPage;
        $values[] = $offset;
        $this->bindDynamic($stmt, $types, $values);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $items = array_map([$this, 'mapBookRow'], $rows);
        $totalPages = max(1, (int)ceil($total / $perPage));

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages
            ],
            'genres' => $this->getGenreOptions()
        ];
    }

    public function getSuggestions($search, $limit = 6) {
        $search = trim((string)$search);
        $limit = max(1, min(15, (int)$limit));
        if ($search === '') {
            return [];
        }

        $query = '%' . $search . '%';
        $stmt = $this->db->prepare(
            "SELECT id, name
             FROM Books
             WHERE name LIKE ? OR isbn LIKE ? OR publisher LIKE ?
                OR EXISTS (
                    SELECT 1
                    FROM BookAuthors ba
                    INNER JOIN Authors a ON a.id = ba.author_id
                    WHERE ba.book_id = Books.id
                      AND CONCAT(a.first_name, ' ', a.last_name) LIKE ?
                )
             ORDER BY name ASC
             LIMIT ?"
        );
        $stmt->bind_param('ssssi', $query, $query, $query, $query, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getBookDetail($bookId) {
        $bookId = (int)$bookId;
        if ($bookId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT
                b.id,
                b.isbn,
                b.name,
                b.description,
                b.publisher,
                b.published_at,
                b.genre,
                b.language,
                b.number_of_copies,
                b.price,
                b.online_rent_price,
                b.online_buy_price,
                b.cover_image,
                b.online_copy_pdf,
                b.created_at,
                COALESCE(ROUND(AVG(br.rating), 1), 0) AS rating,
                COUNT(br.id) AS total_reviews,
                COALESCE(GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', '), '') AS authors
             FROM Books b
             LEFT JOIN BookReviews br ON br.book_id = b.id
             LEFT JOIN BookAuthors ba ON ba.book_id = b.id
             LEFT JOIN Authors a ON a.id = ba.author_id
             WHERE b.id = ?
             GROUP BY b.id
             LIMIT 1"
        );
        $stmt->bind_param('i', $bookId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return null;
        }

        $book = $this->mapBookRow($row);
        $book['published_at'] = (string)($row['published_at'] ?? '');
        $book['online_rent_price'] = $row['online_rent_price'] !== null ? (int)$row['online_rent_price'] : null;
        $book['online_buy_price'] = $row['online_buy_price'] !== null ? (int)$row['online_buy_price'] : null;
        $book['online_copy_pdf'] = (string)($row['online_copy_pdf'] ?? '');
        $book['total_reviews'] = (int)($row['total_reviews'] ?? 0);
        $book['synopsis'] = $book['description'] !== '' ? $book['description'] : 'No synopsis available for this title yet.';

        $book['reviews'] = $this->getBookReviews($bookId);
        $book['related_books'] = $this->getRelatedBooks($bookId, $book['genre'], 6);
        return $book;
    }

    public function createReview($bookId, $userId, $rating, $reviewText) {
        $bookId = (int)$bookId;
        $userId = (int)$userId;
        $rating = (int)$rating;
        $reviewText = trim((string)$reviewText);

        if ($bookId <= 0 || $userId <= 0) {
            return ['success' => false, 'message' => 'Invalid review request'];
        }
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
        }
        if ($reviewText === '') {
            return ['success' => false, 'message' => 'Review text is required'];
        }

        $bookCheck = $this->db->prepare("SELECT id FROM Books WHERE id = ? LIMIT 1");
        $bookCheck->bind_param('i', $bookId);
        $bookCheck->execute();
        if ($bookCheck->get_result()->num_rows === 0) {
            return ['success' => false, 'message' => 'Book not found'];
        }

        $dup = $this->db->prepare("SELECT id FROM BookReviews WHERE book_id = ? AND user_id = ? LIMIT 1");
        $dup->bind_param('ii', $bookId, $userId);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'You already reviewed this book'];
        }

        $stmt = $this->db->prepare("INSERT INTO BookReviews (book_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiis', $bookId, $userId, $rating, $reviewText);
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Failed to submit review'];
        }

        return ['success' => true, 'message' => 'Review submitted successfully'];
    }

    private function mapBookRow($row) {
        $cover = trim((string)($row['cover_image'] ?? ''));
        if ($cover !== '' && stripos($cover, 'http://') !== 0 && stripos($cover, 'https://') !== 0) {
            $cover = APP_URL . '/' . ltrim($cover, '/');
        }
        $publisher = trim((string)($row['publisher'] ?? ''));

        $genre = strtoupper((string)($row['genre'] ?? 'OTHERS'));

        return [
            'id' => (int)$row['id'],
            'isbn' => (string)($row['isbn'] ?? ''),
            'name' => (string)($row['name'] ?? ''),
            'description' => (string)($row['description'] ?? ''),
            'publisher' => $publisher,
            'publisher_display' => $publisher !== '' ? $publisher : 'Unknown Publisher',
            'genre' => $genre,
            'genre_label' => $this->genreLabel($genre),
            'language' => (string)($row['language'] ?? 'English'),
            'number_of_copies' => (int)($row['number_of_copies'] ?? 0),
            'price' => (int)($row['price'] ?? 0),
            'cover_image_url' => $cover,
            'rating' => (float)($row['rating'] ?? 0),
            'authors' => (string)($row['authors'] ?? ''),
            'author_display' => trim((string)($row['authors'] ?? '')) !== '' ? (string)$row['authors'] : 'Unknown Author',
            'created_at' => (string)($row['created_at'] ?? '')
        ];
    }

    private function resolveOrderBy($sort) {
        switch ($sort) {
            case 'title':
                return 'ORDER BY b.name ASC';
            case 'price_low':
                return 'ORDER BY b.price ASC, b.created_at DESC';
            case 'price_high':
                return 'ORDER BY b.price DESC, b.created_at DESC';
            case 'rating':
                return 'ORDER BY rating DESC, b.created_at DESC';
            case 'recent':
            default:
                return 'ORDER BY b.created_at DESC';
        }
    }

    private function getGenreOptions() {
        return [
            ['value' => 'ALL', 'label' => 'All Categories'],
            ['value' => 'FANTASY', 'label' => 'Fantasy'],
            ['value' => 'SCIENCE_FICTION', 'label' => 'Science Fiction'],
            ['value' => 'MYSTERY', 'label' => 'Mystery'],
            ['value' => 'ROMANCE', 'label' => 'Romance'],
            ['value' => 'THRILLER', 'label' => 'Thriller'],
            ['value' => 'NON_FICTION', 'label' => 'Non-Fiction'],
            ['value' => 'BIOGRAPHY', 'label' => 'Biography'],
            ['value' => 'HISTORY', 'label' => 'History'],
            ['value' => 'OTHERS', 'label' => 'Others']
        ];
    }

    private function genreLabel($value) {
        $map = [
            'FANTASY' => 'Fantasy',
            'SCIENCE_FICTION' => 'Science Fiction',
            'MYSTERY' => 'Mystery',
            'ROMANCE' => 'Romance',
            'THRILLER' => 'Thriller',
            'NON_FICTION' => 'Non-Fiction',
            'BIOGRAPHY' => 'Biography',
            'HISTORY' => 'History',
            'OTHERS' => 'Others'
        ];
        return $map[$value] ?? 'Others';
    }

    private function getBookReviews($bookId) {
        $stmt = $this->db->prepare(
            "SELECT
                br.id,
                br.rating,
                br.review,
                br.created_at,
                u.first_name,
                u.last_name,
                u.profile_image
             FROM BookReviews br
             INNER JOIN Users u ON u.id = br.user_id
             WHERE br.book_id = ?
             ORDER BY br.created_at DESC
             LIMIT 20"
        );
        $stmt->bind_param('i', $bookId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_map(function ($row) {
            $profileImage = trim((string)($row['profile_image'] ?? ''));
            if ($profileImage !== '' && stripos($profileImage, 'http://') !== 0 && stripos($profileImage, 'https://') !== 0) {
                $profileImage = APP_URL . '/public/uploads/profiles/' . basename($profileImage);
            }

            return [
                'id' => (int)$row['id'],
                'rating' => (int)($row['rating'] ?? 0),
                'review' => (string)($row['review'] ?? ''),
                'created_at' => (string)($row['created_at'] ?? ''),
                'reviewer_name' => trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')),
                'profile_image_url' => $profileImage
            ];
        }, $rows);
    }

    private function getRelatedBooks($bookId, $genre, $limit = 6) {
        $limit = max(1, min(12, (int)$limit));
        $genre = strtoupper(trim((string)$genre));

        $stmt = $this->db->prepare(
            "SELECT id, isbn, name, description, publisher, genre, language, number_of_copies, price, cover_image, created_at, 0 AS rating, '' AS authors
             FROM Books
             WHERE id != ? AND genre = ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('isi', $bookId, $genre, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($rows) < $limit) {
            $fallbackLimit = $limit - count($rows);
            $stmt2 = $this->db->prepare(
                "SELECT id, isbn, name, description, publisher, genre, language, number_of_copies, price, cover_image, created_at, 0 AS rating, '' AS authors
                 FROM Books
                 WHERE id != ? AND genre != ?
                 ORDER BY created_at DESC
                 LIMIT ?"
            );
            $stmt2->bind_param('isi', $bookId, $genre, $fallbackLimit);
            $stmt2->execute();
            $rows = array_merge($rows, $stmt2->get_result()->fetch_all(MYSQLI_ASSOC));
        }

        return array_map([$this, 'mapBookRow'], $rows);
    }

    private function bindDynamic($stmt, $types, $values) {
        $params = [];
        $params[] = $types;
        foreach ($values as $i => $value) {
            $params[] = &$values[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $params);
    }
}
?>
