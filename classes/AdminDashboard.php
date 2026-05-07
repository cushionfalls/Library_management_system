<?php
require_once __DIR__ . '/Database.php';

class AdminDashboard {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getOverview() {
        return [
            'total_users' => $this->countTable('Users'),
            'total_books' => $this->countTable('Books'),
            'total_memberships' => $this->countTotalMemberships(),
            'wallet_credits_today' => $this->sumWalletCreditsToday()
        ];
    }

    public function getRecentUsers($limit = 8) {
        $limit = max(1, (int)$limit);
        $stmt = $this->db->prepare(
            "SELECT id, first_name, last_name, email, role, is_active, phone_number, dob, profile_image, created_at
             FROM Users
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRecentTransactions($limit = 100) {
        $limit = max(1, (int)$limit);
        $stmt = $this->db->prepare(
                    "SELECT wt.id,
                    wt.reason AS type,
                    wt.amount,
                    NULL AS due_date,
                    wt.created_at,
                    NULL AS is_returned,
                    IF(wt.reason = 'BOOK_BUY', 'Book Purchase', wt.reason) AS title,
                    u.first_name,
                    u.last_name,
                    'WALLET' as category
             FROM WalletTransactions wt
             INNER JOIN Users u ON u.id = wt.user_id
             WHERE wt.reason IN ('TOP_UP', 'MEMBERSHIP', 'BOOK_BUY')
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function getBooks($limit = 100) {
        $limit = max(1, (int)$limit);
        $stmt = $this->db->prepare(
            "SELECT b.id, b.isbn, b.name, b.description, b.publisher, b.published_at, b.language, b.genre,
                    b.number_of_copies, b.price, b.online_buy_price,
                    b.cover_image, b.online_copy_pdf, b.created_at,
                    COALESCE(GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', '), '') AS authors
             FROM Books b
             LEFT JOIN BookAuthors ba ON ba.book_id = b.id
             LEFT JOIN Authors a ON a.id = ba.author_id
             GROUP BY b.id
             ORDER BY b.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createBook($isbn, $name, $publisher, $copies, $price, $bookData = []) {
        $isbn = trim((string)$isbn);
        $name = trim((string)$name);
        $publisher = trim((string)$publisher);
        $copies = (int)$copies;
        $price = (int)$price;
        $description = trim((string)($bookData['description'] ?? ''));
        $author = trim((string)($bookData['author'] ?? ''));
        $publishedAt = $this->normalizeDatetime($bookData['published_at'] ?? null);
        $language = trim((string)($bookData['language'] ?? 'English'));
        $genre = $this->normalizeGenre($bookData['genre'] ?? 'OTHERS');
        $onlineBuyPrice = $this->normalizeOptionalInt($bookData['online_buy_price'] ?? null);
        $coverImage = trim((string)($bookData['cover_image'] ?? ''));
        $onlineCopyPdf = trim((string)($bookData['online_copy_pdf'] ?? ''));

        if ($isbn === '' || $name === '' || $author === '' || $publisher === '') {
            return ['success' => false, 'message' => 'ISBN, name, author and publisher are required'];
        }
        if ($copies < 0 || $price < 0) {
            return ['success' => false, 'message' => 'Copies and price must be valid numbers'];
        }
        if ($onlineBuyPrice !== null && $onlineBuyPrice < 0) {
            return ['success' => false, 'message' => 'Online buy price must be a valid number'];
        }

        $stmt = $this->db->prepare("SELECT id FROM Books WHERE isbn = ? LIMIT 1");
        $stmt->bind_param('s', $isbn);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'ISBN already exists'];
        }

        $insert = $this->db->prepare(
            "INSERT INTO Books (
                isbn, name, description, publisher, published_at, language, genre,
                number_of_copies, price, online_buy_price, cover_image, online_copy_pdf
            )
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $descriptionOrNull = ($description === '') ? null : $description;
        $language = ($language === '') ? 'English' : $language;
        $coverImageOrNull = ($coverImage === '') ? null : $coverImage;
        $onlineCopyPdfOrNull = ($onlineCopyPdf === '') ? null : $onlineCopyPdf;
        $insert->bind_param(
            'sssssssiiiss',
            $isbn,
            $name,
            $descriptionOrNull,
            $publisher,
            $publishedAt,
            $language,
            $genre,
            $copies,
            $price,
            $onlineBuyPrice,
            $coverImageOrNull,
            $onlineCopyPdfOrNull
        );

        if (!$insert->execute()) {
            $error = $this->db->error;
            return ['success' => false, 'message' => 'Failed to create book: ' . ($error ?: 'Database error')];
        }
        $this->syncBookAuthor((int)$insert->insert_id, $author);

        return ['success' => true, 'message' => 'Book created successfully'];
    }

    public function updateBook($id, $isbn, $name, $publisher, $copies, $price, $bookData = []) {
        $id = (int)$id;
        $isbn = trim((string)$isbn);
        $name = trim((string)$name);
        $publisher = trim((string)$publisher);
        $copies = (int)$copies;
        $price = (int)$price;
        $description = trim((string)($bookData['description'] ?? ''));
        $author = trim((string)($bookData['author'] ?? ''));
        $publishedAt = $this->normalizeDatetime($bookData['published_at'] ?? null);
        $language = trim((string)($bookData['language'] ?? 'English'));
        $genre = $this->normalizeGenre($bookData['genre'] ?? 'OTHERS');
        $onlineBuyPrice = $this->normalizeOptionalInt($bookData['online_buy_price'] ?? null);
        $coverImage = trim((string)($bookData['cover_image'] ?? ''));
        $onlineCopyPdf = trim((string)($bookData['online_copy_pdf'] ?? ''));

        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid book id'];
        }
        if ($isbn === '' || $name === '' || $author === '' || $publisher === '') {
            return ['success' => false, 'message' => 'ISBN, name, author and publisher are required'];
        }
        if ($copies < 0 || $price < 0) {
            return ['success' => false, 'message' => 'Copies and price must be valid numbers'];
        }
        if ($onlineBuyPrice !== null && $onlineBuyPrice < 0) {
            return ['success' => false, 'message' => 'Online buy price must be a valid number'];
        }

        $dup = $this->db->prepare("SELECT id FROM Books WHERE isbn = ? AND id != ? LIMIT 1");
        $dup->bind_param('si', $isbn, $id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'ISBN already in use by another book'];
        }

        $stmt = $this->db->prepare(
            "UPDATE Books
             SET isbn = ?, name = ?, description = ?, publisher = ?, published_at = ?, language = ?, genre = ?,
                 number_of_copies = ?, price = ?, online_buy_price = ?, cover_image = ?, online_copy_pdf = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );
        $descriptionOrNull = ($description === '') ? null : $description;
        $language = ($language === '') ? 'English' : $language;
        $coverImageOrNull = ($coverImage === '') ? null : $coverImage;
        $onlineCopyPdfOrNull = ($onlineCopyPdf === '') ? null : $onlineCopyPdf;
        $stmt->bind_param(
            'sssssssiiissi',
            $isbn,
            $name,
            $descriptionOrNull,
            $publisher,
            $publishedAt,
            $language,
            $genre,
            $copies,
            $price,
            $onlineBuyPrice,
            $coverImageOrNull,
            $onlineCopyPdfOrNull,
            $id
        );
        if (!$stmt->execute()) {
            $error = $this->db->error;
            return ['success' => false, 'message' => 'Failed to update book: ' . ($error ?: 'Database error')];
        }
        $this->syncBookAuthor($id, $author);

        return ['success' => true, 'message' => 'Book updated successfully'];
    }

    public function deleteBook($id) {
        $id = (int)$id;
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid book id'];
        }

        // Skip active physical transactions check as they are obsolete
        /*
        $check = $this->db->prepare(
            "SELECT id FROM BookTransactions WHERE book_id = ? AND is_returned = 0 LIMIT 1"
        );
        $check->bind_param('i', $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => "Cannot delete the book because some users currently have it rented out."];
        }
        */

        // Check if any users own this digital book or have it in their library via membership
        $checkAccess = $this->db->prepare(
            "SELECT id FROM UserBookAccess WHERE book_id = ? LIMIT 1"
        );
        $checkAccess->bind_param('i', $id);
        $checkAccess->execute();
        if ($checkAccess->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => "Cannot delete the book because it is already in users' libraries (purchased or accessed via membership)."];
        }

        $this->db->begin_transaction();
        try {
            // Delete from BookAuthors first to avoid foreign key failure
            $delAuthors = $this->db->prepare("DELETE FROM BookAuthors WHERE book_id = ?");
            $delAuthors->bind_param('i', $id);
            $delAuthors->execute();

            $stmt = $this->db->prepare("DELETE FROM Books WHERE id = ?");
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to delete book due to a database error.'];
            }

            if ($stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Book not found'];
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Book deleted successfully'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Failed to delete the book. Please try again.'];
        }
    }

    public function updateUser($id, $firstName, $lastName, $email, $role, $isActive, $phoneNumber = '', $dob = '', $profileImage = null) {
        $id = (int)$id;
        $firstName = trim((string)$firstName);
        $lastName = trim((string)$lastName);
        $email = trim((string)$email);
        $role = strtoupper(trim((string)$role));
        $isActive = (int)$isActive === 1 ? 1 : 0;
        $phoneNumber = trim((string)$phoneNumber);
        $dobNormalized = $this->normalizeOptionalDate($dob);
        $profileImage = trim((string)$profileImage);

        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid user id'];
        }
        if ($firstName === '' || $lastName === '' || $email === '') {
            return ['success' => false, 'message' => 'First name, last name and email are required'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        $allowedRoles = ['USER', 'LIBRARIAN', 'ADMIN'];
        if (!in_array($role, $allowedRoles, true)) {
            return ['success' => false, 'message' => 'Invalid role'];
        }

        $dup = $this->db->prepare("SELECT id FROM Users WHERE email = ? AND id != ? LIMIT 1");
        $dup->bind_param('si', $email, $id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already used by another user'];
        }

        $stmt = $this->db->prepare(
            "UPDATE Users
             SET first_name = ?, last_name = ?, email = ?, role = ?, is_active = ?, phone_number = ?, dob = ?, profile_image = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $phoneOrNull = ($phoneNumber === '') ? null : $phoneNumber;
        $profileImageOrNull = ($profileImage === '') ? null : $profileImage;
        $stmt->bind_param('ssssisssi', $firstName, $lastName, $email, $role, $isActive, $phoneOrNull, $dobNormalized, $profileImageOrNull, $id);
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Failed to update user'];
        }

        if ($stmt->affected_rows < 0) {
            return ['success' => false, 'message' => 'User not found'];
        }

        return ['success' => true, 'message' => 'User updated successfully'];
    }

    public function createUser($firstName, $lastName, $email, $password, $role, $isActive, $phoneNumber = '', $dob = '', $profileImage = null) {
        $firstName = trim((string)$firstName);
        $lastName = trim((string)$lastName);
        $email = trim((string)$email);
        $password = (string)$password;
        $role = strtoupper(trim((string)$role));
        $isActive = (int)$isActive === 1 ? 1 : 0;
        $phoneNumber = trim((string)$phoneNumber);
        $dobNormalized = $this->normalizeOptionalDate($dob);
        $profileImage = trim((string)$profileImage);

        if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
            return ['success' => false, 'message' => 'First name, last name, email and password are required'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        }

        $allowedRoles = ['USER', 'LIBRARIAN'];
        if (!in_array($role, $allowedRoles, true)) {
            return ['success' => false, 'message' => 'Role must be USER or LIBRARIAN'];
        }

        $dup = $this->db->prepare("SELECT id FROM Users WHERE email = ? LIMIT 1");
        $dup->bind_param('s', $email);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already used by another user'];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            "INSERT INTO Users (first_name, last_name, email, password, role, is_active, phone_number, dob, profile_image, is_verified, verified_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())"
        );
        $phoneOrNull = ($phoneNumber === '') ? null : $phoneNumber;
        $profileImageOrNull = ($profileImage === '') ? null : $profileImage;
        $stmt->bind_param('sssssisss', $firstName, $lastName, $email, $passwordHash, $role, $isActive, $phoneOrNull, $dobNormalized, $profileImageOrNull);

        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Failed to create user'];
        }

        return ['success' => true, 'message' => 'User created successfully'];
    }

    public function deleteUser($id, $actorId) {
        $id = (int)$id;
        $actorId = (int)$actorId;

        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid user id'];
        }
        if ($id === $actorId) {
            return ['success' => false, 'message' => 'You cannot remove your own account'];
        }

        $roleStmt = $this->db->prepare("SELECT role FROM Users WHERE id = ? LIMIT 1");
        $roleStmt->bind_param('i', $id);
        $roleStmt->execute();
        $row = $roleStmt->get_result()->fetch_assoc();
        if (!$row) {
            return ['success' => false, 'message' => 'User not found'];
        }
        if (($row['role'] ?? '') === 'ADMIN') {
            return ['success' => false, 'message' => 'Admin user cannot be removed'];
        }

        $this->db->begin_transaction();
        try {
            $emailStmt = $this->db->prepare("SELECT email FROM Users WHERE id = ? LIMIT 1");
            $emailStmt->bind_param('i', $id);
            $emailStmt->execute();
            $user = $emailStmt->get_result()->fetch_assoc();
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'User not found'];
            }

            $email = $user['email'];

            $stmt = $this->db->prepare("DELETE FROM WalletTransactions WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM BookReviews WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM BookTransactions WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM Sessions WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM OTP WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM Users WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'User not found'];
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'User removed successfully'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Failed to remove user'];
        }
    }

    private function countTable($tableName) {
        $safe = preg_replace('/[^A-Za-z0-9_]/', '', $tableName);
        $result = $this->db->query("SELECT COUNT(*) AS total FROM {$safe}");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }

    private function countTotalMemberships() {
        $result = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM WalletTransactions
             WHERE reason = 'MEMBERSHIP'"
        );
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }


    private function sumWalletCreditsToday() {
        $result = $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM WalletTransactions
             WHERE type = 'CREDIT'"
        );
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }

    private function normalizeGenre($genre) {
        $genre = strtoupper(trim((string)$genre));
        $allowed = [
            'FANTASY',
            'SCIENCE_FICTION',
            'MYSTERY',
            'ROMANCE',
            'THRILLER',
            'NON_FICTION',
            'BIOGRAPHY',
            'HISTORY',
            'OTHERS'
        ];
        if (!in_array($genre, $allowed, true)) {
            return 'OTHERS';
        }
        return $genre;
    }

    private function normalizeOptionalInt($value) {
        if ($value === null) {
            return null;
        }
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        return (int)$value;
    }

    private function normalizeDatetime($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        $time = strtotime($value);
        if ($time === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $time);
    }

    private function normalizeOptionalDate($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $time = strtotime($value);
        if ($time === false) {
            return null;
        }
        return date('Y-m-d', $time);
    }

    private function syncBookAuthor($bookId, $authorFullName) {
        $bookId = (int)$bookId;
        $authorFullName = trim((string)$authorFullName);
        if ($bookId <= 0 || $authorFullName === '') {
            return;
        }

        $parts = preg_split('/\s+/u', $authorFullName, -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts || count($parts) === 0) {
            return;
        }

        $firstName = trim((string)$parts[0]);
        $lastName = trim((string)implode(' ', array_slice($parts, 1)));
        if ($lastName === '') {
            $lastName = '-';
        }

        $authorId = $this->findOrCreateAuthor($firstName, $lastName);
        if ($authorId <= 0) {
            return;
        }

        $del = $this->db->prepare("DELETE FROM BookAuthors WHERE book_id = ?");
        $del->bind_param('i', $bookId);
        $del->execute();

        $ins = $this->db->prepare("INSERT INTO BookAuthors (book_id, author_id) VALUES (?, ?)");
        $ins->bind_param('ii', $bookId, $authorId);
        $ins->execute();
    }

    private function findOrCreateAuthor($firstName, $lastName) {
        $firstName = trim((string)$firstName);
        $lastName = trim((string)$lastName);
        if ($firstName === '') {
            return 0;
        }
        if ($lastName === '') {
            $lastName = '-';
        }

        $find = $this->db->prepare("SELECT id FROM Authors WHERE first_name = ? AND last_name = ? LIMIT 1");
        $find->bind_param('ss', $firstName, $lastName);
        $find->execute();
        $row = $find->get_result()->fetch_assoc();
        if ($row && !empty($row['id'])) {
            return (int)$row['id'];
        }

        $create = $this->db->prepare("INSERT INTO Authors (first_name, last_name) VALUES (?, ?)");
        $create->bind_param('ss', $firstName, $lastName);
        if (!$create->execute()) {
            return 0;
        }
        return (int)$create->insert_id;
    }
}

