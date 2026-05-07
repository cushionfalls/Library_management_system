<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/AiService.php';

class RecommendationController
{
    private $session;
    private $ai;
    private $pdo;

    public function __construct()
    {
        $this->session = new Session();
        $this->ai = new AiService();
        $this->pdo = $this->buildPdoConnection();
    }

    public function handle($action): array
    {
        switch ($action) {
            case 'for-user':
                return $this->getForUser((int)($_GET['user_id'] ?? $this->session->getUserId()));
            case 'similar':
                return $this->getSimilar((int)($_GET['book_id'] ?? 0));
            case 'popular-in-genre':
                return $this->getPopularInGenre((string)($_GET['genre'] ?? ''));
            case 'search-suggestions':
                return $this->getSearchSuggestions((string)($_GET['q'] ?? ''));
            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }
    }

    public function getForUser($userId): array
    {
        if (!$this->session->isLoggedIn()) {
            return ['success' => false, 'message' => 'Please login first'];
        }
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid user id'];
        }

        $profile = $this->fetchUserProfile($userId);
        $history = $this->fetchBorrowingHistory($userId);
        $excludedBookIds = $this->fetchUserExcludedBookIds($userId);
        $profile = $this->enrichProfileForRecommendations($profile, $history);
        $genrePriority = array_column($profile['genre_signals_from_history'] ?? [], 'genre');
        $available = $this->fetchAvailableBooksForUser($excludedBookIds, $genrePriority, 120);
        
        if (!$available && !empty($excludedBookIds)) {
            // If the user literally has access to EVERY book in the library, only then do we relax.
            $available = $this->fetchAvailableBooksForUser([], $genrePriority, 120);
        }

        if (!$available) {
            return [
                'success' => true,
                'data' => ['source' => 'no_catalog', 'recommendations' => []],
                'meta' => ['user_id' => $userId, 'history_count' => count($history), 'catalog_count' => 0],
                'message' => 'No books are available in the library right now.'
            ];
        }

        // Cold-start: if the user has no transaction history, ask the AI
        // for general "newcomer" recommendations from the available catalog.
        if (count($history) === 0) {
            $promptCatalog = array_slice($available, 0, 40);
            $coldProfile = $profile;
            $coldProfile['favorite_genres'] = [];
            $ai = $this->ai->getBookRecommendations($coldProfile, [], $promptCatalog);

            if (empty($ai['recommendations']) || !is_array($ai['recommendations']) || count($ai['recommendations']) === 0) {
                $ai = [
                    'source' => 'popular',
                    'recommendations' => $this->buildGenreBasedRecommendations($available, [], 6)
                ];
            }

            return [
                'success' => true,
                'data' => $ai,
                'meta' => [
                    'user_id' => $userId,
                    'history_count' => 0,
                    'catalog_count' => count($available),
                    'prompt_catalog_limit' => 40
                ],
                'message' => 'Here are some books we think you might enjoy!'
            ];
        }

        // Always constrain to books the user does NOT already own / have access to.
        $promptCatalog = array_slice($available, 0, 40);
        $ai = $this->ai->getBookRecommendations($profile, $history, $promptCatalog);
        if (empty($ai['recommendations']) || !is_array($ai['recommendations']) || count($ai['recommendations']) === 0) {
            $ai = [
                'source' => 'genre_based',
                'recommendations' => $this->buildGenreBasedRecommendations($available, $history, 6)
            ];
        }
        $ai = $this->attachBecauseYouReadHints($ai, $history);

        return [
            'success' => true,
            'data' => $ai,
            'meta' => [
                'user_id' => $userId,
                'history_count' => count($history),
                'catalog_count' => count($available),
                'prompt_catalog_limit' => 40,
                'top_genres_from_history' => $genrePriority
            ]
        ];
    }

    public function getSimilar($bookId): array
    {
        if (!$this->session->isLoggedIn()) {
            return ['success' => false, 'message' => 'Please login first'];
        }
        if ($bookId <= 0) {
            return ['success' => false, 'message' => 'Invalid book id'];
        }

        $stmt = $this->pdo->prepare('SELECT id, name AS title, genre FROM Books WHERE id = :book_id LIMIT 1');
        $stmt->execute([':book_id' => $bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found'];
        }

        $available = $this->fetchAvailableBooksForUser([], [$book['genre']], 40);
        $ai = $this->ai->getSimilarBooks($book['title'], $book['genre'], $available);

        return ['success' => true, 'data' => $ai, 'book' => $book];
    }

    public function getPopularInGenre($genre): array
    {
        if (!$this->session->isLoggedIn()) {
            return ['success' => false, 'message' => 'Please login first'];
        }
        if ($genre === '') {
            return ['success' => false, 'message' => 'Genre is required'];
        }

        $sql = "SELECT b.id, b.isbn, b.name AS title, b.genre, COUNT(bt.id) AS borrow_count,
                       COALESCE((
                           SELECT GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ')
                           FROM BookAuthors ba
                           INNER JOIN Authors a ON a.id = ba.author_id
                           WHERE ba.book_id = b.id
                       ), '') AS author
                FROM Books b
                LEFT JOIN BookTransactions bt ON bt.book_id = b.id
                WHERE b.number_of_copies > 0 AND b.genre = :genre
                GROUP BY b.id, b.isbn, b.name, b.genre
                ORDER BY borrow_count DESC, b.name ASC
                LIMIT 6";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':genre' => $genre]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['success' => true, 'genre' => $genre, 'books' => $rows];
    }

    public function getSearchSuggestions($query): array
    {
        if ($query === '') {
            return ['success' => false, 'message' => 'Search query is required'];
        }
        $catalog = $this->fetchAvailableBooksForUser([], [], 40);
        $ai = $this->ai->getSearchSuggestions($query, $catalog);
        return ['success' => true, 'data' => $ai];
    }

    private function fetchBorrowingHistory($userId): array
    {
        // "History" now only includes digital access purchases/unlocks (OWNED / MEMBERSHIP).
        // RENT/INHAND/ONLINE physical logic is obsolete.
        $sql = "SELECT
                    b.id AS book_id,
                    b.isbn,
                    b.name AS title,
                    b.genre,
                    uba.access_type AS activity_type,
                    uba.created_at AS activity_at,
                    (SELECT br.rating FROM BookReviews br
                     WHERE br.book_id = b.id AND br.user_id = :user_id
                     ORDER BY br.created_at DESC LIMIT 1) AS rating,
                    COALESCE((
                        SELECT GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ')
                        FROM BookAuthors ba
                        INNER JOIN Authors a ON a.id = ba.author_id
                        WHERE ba.book_id = b.id
                    ), '') AS author
                FROM UserBookAccess uba
                INNER JOIN Books b ON b.id = uba.book_id
                WHERE uba.user_id = :user_id
                ORDER BY activity_at DESC
                LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchAvailableBooksForUser(array $excludeBookIds, array $genrePriority, $limit = 40): array
    {
        $excludeBookIds = array_values(array_filter(array_map('intval', $excludeBookIds), function ($v) {
            return $v > 0;
        }));
        $limit = (int)$limit;
        $limit = max(1, min(300, $limit));

        $where = "WHERE b.number_of_copies > 0";
        $params = [];

        if (!empty($excludeBookIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeBookIds), '?'));
            $where .= " AND b.id NOT IN ($placeholders)";
            $params = array_merge($params, $excludeBookIds);
        }

        $order = "ORDER BY b.name ASC";
        if (!empty($genrePriority)) {
            $quoted = [];
            foreach ($genrePriority as $g) {
                $g = strtoupper(trim((string)$g));
                if ($g !== '') {
                    $quoted[] = $this->pdo->quote($g);
                }
            }
            if (!empty($quoted)) {
                // FIELD() returns 0 for non-matches. We sort matches (0) first, then by priority.
                $order = "ORDER BY (FIELD(b.genre, " . implode(',', $quoted) . ") = 0) ASC, FIELD(b.genre, " . implode(',', $quoted) . ") ASC, b.name ASC";
            }
        }

        $sql = "SELECT
                    b.id,
                    b.isbn,
                    b.name AS title,
                    b.genre,
                    b.cover_image,
                    (SELECT COUNT(*) FROM BookTransactions bt WHERE bt.book_id = b.id) AS borrow_count,
                    COALESCE((
                        SELECT GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ')
                        FROM BookAuthors ba
                        INNER JOIN Authors a ON a.id = ba.author_id
                        WHERE ba.book_id = b.id
                    ), '') AS author
                FROM Books b
                $where
                $order
                LIMIT $limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'withCoverImageUrl'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Category/genre-based recommendations (no AI): prioritize genres from the user's history and popular books.
     */
    private function buildGenreBasedRecommendations(array $availableBooks, array $history, $limit = 6): array
    {
        $limit = max(1, (int)$limit);

        $anchors = [];
        foreach ($history as $row) {
            $g = (string)($row['genre'] ?? '');
            $t = (string)($row['title'] ?? '');
            if ($g !== '' && $t !== '' && !isset($anchors[$g])) {
                $anchors[$g] = $t;
            }
        }

        usort($availableBooks, function ($a, $b) {
            $ba = (int)($a['borrow_count'] ?? 0);
            $bb = (int)($b['borrow_count'] ?? 0);
            if ($ba !== $bb) {
                return $bb <=> $ba;
            }
            return strcmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
        });

        $out = [];
        foreach ($availableBooks as $book) {
            if (count($out) >= $limit) {
                break;
            }
            $genre = (string)($book['genre'] ?? '');
            $because = '';
            $reason = $genre !== '' ? ('Popular in ' . $genre . ' right now.') : 'Popular in the library right now.';
            $score = 65 + min(35, (int)($book['borrow_count'] ?? 0));
            $out[] = [
                'book_id' => (int)($book['id'] ?? 0),
                'isbn' => (string)($book['isbn'] ?? ''),
                'title' => (string)($book['title'] ?? ''),
                'author' => (string)($book['author'] ?? ''),
                'genre' => $genre,
                'cover_image_url' => (string)($book['cover_image_url'] ?? ''),
                'reason' => $reason,
                'because' => $because,
                'score' => min(100, max(0, $score))
            ];
        }
        return $out;
    }

    private function fetchUserExcludedBookIds($userId): array
    {
        // Exclude books the user already has digital access to.
        $stmt = $this->pdo->prepare("SELECT DISTINCT book_id FROM UserBookAccess WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ids = [];
        foreach ($rows as $r) {
            $ids[] = (int)($r['book_id'] ?? 0);
        }
        return array_values(array_filter($ids, function ($v) {
            return $v > 0;
        }));
    }

    private function fetchUserProfile($userId): array
    {
        $sql = "SELECT id, first_name, last_name, email
                FROM Users
                WHERE id = :user_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function enrichProfileForRecommendations(array $profile, array $history): array
    {
        $profile['display_name'] = trim((string)($profile['first_name'] ?? '') . ' ' . (string)($profile['last_name'] ?? ''));
        $counts = [];
        foreach ($history as $row) {
            $g = (string)($row['genre'] ?? '');
            if ($g !== '') {
                $counts[$g] = ($counts[$g] ?? 0) + 1;
            }
        }
        arsort($counts);

        $signals = [];
        foreach ($counts as $genre => $n) {
            $signals[] = ['genre' => $genre, 'activity_count' => $n];
        }
        $profile['genre_signals_from_history'] = array_slice($signals, 0, 5);
        $profile['favorite_genres'] = array_column($profile['genre_signals_from_history'], 'genre');
        return $profile;
    }

    private function attachBecauseYouReadHints(array $ai, array $history): array
    {
        if (empty($ai['recommendations']) || !is_array($ai['recommendations'])) {
            return $ai;
        }

        $anchors = [];
        foreach ($history as $row) {
            $g = $row['genre'] ?? '';
            $t = $row['title'] ?? '';
            if ($g !== '' && $t !== '' && !isset($anchors[$g])) {
                $anchors[$g] = $t;
            }
        }

        foreach ($ai['recommendations'] as &$rec) {
            $g = $rec['genre'] ?? '';
            if ($g !== '' && isset($anchors[$g])) {
                $rec['because'] = 'Because you read ' . $anchors[$g] . ' (' . $g . ')...';
            } elseif (!empty($history[0]['title'])) {
                $rec['because'] = 'Because your recent reads include ' . $history[0]['title'] . '...';
            }
        }
        unset($rec);
        return $ai;
    }

    private function withCoverImageUrl(array $book): array
    {
        $cover = trim((string)($book['cover_image'] ?? ''));
        $url = '';
        if ($cover !== '') {
            if (stripos($cover, 'http://') === 0 || stripos($cover, 'https://') === 0) {
                $url = $cover;
            } else {
                $url = APP_URL . '/' . ltrim($cover, '/');
            }
        }
        $book['cover_image_url'] = $url;
        return $book;
    }

    private function buildPdoConnection(): PDO
    {
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            return $GLOBALS['pdo'];
        }
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}

try {
    $controller = new RecommendationController();
    $action = $_GET['action'] ?? 'for-user';
    $response = $controller->handle($action);
    ob_clean();
    echo json_encode($response);
} catch (Throwable $e) {
    ob_clean();
    $errorMsg = $e->getMessage();
    error_log('RecommendationController error: ' . $errorMsg);
    
    // Attempt to log more context if it's a JSON parse error
    if (strpos($errorMsg, 'JSON') !== false) {
        error_log('Debug context: ' . json_encode($_GET));
    }

    echo json_encode(['success' => false, 'message' => 'Recommendation service unavailable: ' . $errorMsg]);
}
?>
