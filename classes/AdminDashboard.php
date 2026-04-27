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
            'active_rentals' => $this->countActiveRentals(),
            'pending_fines' => $this->countPendingFines(),
            'overdue_books' => $this->countOverdueBooks(),
            'wallet_credits_today' => $this->sumWalletCreditsToday()
        ];
    }

    public function getRecentUsers($limit = 8) {
        $limit = max(1, (int)$limit);
        $stmt = $this->db->prepare(
            "SELECT id, first_name, last_name, email, role, is_active, created_at
             FROM Users
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRecentTransactions($limit = 8) {
        $limit = max(1, (int)$limit);
        $stmt = $this->db->prepare(
            "SELECT bt.id, bt.transaction_type, bt.amount_paid, bt.due_date, bt.created_at, bt.is_returned,
                    b.name AS book_name,
                    u.first_name,
                    u.last_name
             FROM BookTransactions bt
             INNER JOIN Books b ON b.id = bt.book_id
             INNER JOIN Users u ON u.id = bt.user_id
             ORDER BY bt.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getOverdueBooks($limit = 8) {
        $limit = max(1, (int)$limit);
        $stmt = $this->db->prepare(
            "SELECT bt.id, bt.due_date, bt.created_at,
                    b.name AS book_name,
                    u.first_name,
                    u.last_name
             FROM BookTransactions bt
             INNER JOIN Books b ON b.id = bt.book_id
             INNER JOIN Users u ON u.id = bt.user_id
             WHERE bt.transaction_type = 'RENT'
               AND bt.is_returned = 0
               AND bt.due_date IS NOT NULL
               AND bt.due_date < NOW()
             ORDER BY bt.due_date ASC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getBooks($limit = 100) {
        $limit = max(1, (int)$limit);
        $stmt = $this->db->prepare(
            "SELECT id, isbn, name, description, publisher, published_at, language, genre,
                    number_of_copies, price, online_rent_price, online_buy_price,
                    cover_image, online_copy_pdf, created_at
             FROM Books
             ORDER BY created_at DESC
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
        $publishedAt = $this->normalizeDatetime($bookData['published_at'] ?? null);
        $language = trim((string)($bookData['language'] ?? 'English'));
        $genre = $this->normalizeGenre($bookData['genre'] ?? 'OTHERS');
        $onlineRentPrice = $this->normalizeOptionalInt($bookData['online_rent_price'] ?? null);
        $onlineBuyPrice = $this->normalizeOptionalInt($bookData['online_buy_price'] ?? null);
        $coverImage = trim((string)($bookData['cover_image'] ?? ''));
        $onlineCopyPdf = trim((string)($bookData['online_copy_pdf'] ?? ''));

        if ($isbn === '' || $name === '' || $publisher === '') {
            return ['success' => false, 'message' => 'ISBN, name and publisher are required'];
        }
        if ($copies < 0 || $price < 0) {
            return ['success' => false, 'message' => 'Copies and price must be valid numbers'];
        }
        if ($onlineRentPrice !== null && $onlineRentPrice < 0) {
            return ['success' => false, 'message' => 'Online rent price must be a valid number'];
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
                number_of_copies, price, online_rent_price, online_buy_price, cover_image, online_copy_pdf
            )
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $descriptionOrNull = ($description === '') ? null : $description;
        $language = ($language === '') ? 'English' : $language;
        $coverImageOrNull = ($coverImage === '') ? null : $coverImage;
        $onlineCopyPdfOrNull = ($onlineCopyPdf === '') ? null : $onlineCopyPdf;
        $insert->bind_param(
            'sssssssiiiiss',
            $isbn,
            $name,
            $descriptionOrNull,
            $publisher,
            $publishedAt,
            $language,
            $genre,
            $copies,
            $price,
            $onlineRentPrice,
            $onlineBuyPrice,
            $coverImageOrNull,
            $onlineCopyPdfOrNull
        );

        if (!$insert->execute()) {
            return ['success' => false, 'message' => 'Failed to create book'];
        }

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
        $publishedAt = $this->normalizeDatetime($bookData['published_at'] ?? null);
        $language = trim((string)($bookData['language'] ?? 'English'));
        $genre = $this->normalizeGenre($bookData['genre'] ?? 'OTHERS');
        $onlineRentPrice = $this->normalizeOptionalInt($bookData['online_rent_price'] ?? null);
        $onlineBuyPrice = $this->normalizeOptionalInt($bookData['online_buy_price'] ?? null);
        $coverImage = trim((string)($bookData['cover_image'] ?? ''));
        $onlineCopyPdf = trim((string)($bookData['online_copy_pdf'] ?? ''));

        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid book id'];
        }
        if ($isbn === '' || $name === '' || $publisher === '') {
            return ['success' => false, 'message' => 'ISBN, name and publisher are required'];
        }
        if ($copies < 0 || $price < 0) {
            return ['success' => false, 'message' => 'Copies and price must be valid numbers'];
        }
        if ($onlineRentPrice !== null && $onlineRentPrice < 0) {
            return ['success' => false, 'message' => 'Online rent price must be a valid number'];
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
                 number_of_copies = ?, price = ?, online_rent_price = ?, online_buy_price = ?, cover_image = ?, online_copy_pdf = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );
        $descriptionOrNull = ($description === '') ? null : $description;
        $language = ($language === '') ? 'English' : $language;
        $coverImageOrNull = ($coverImage === '') ? null : $coverImage;
        $onlineCopyPdfOrNull = ($onlineCopyPdf === '') ? null : $onlineCopyPdf;
        $stmt->bind_param(
            'sssssssiiiissi',
            $isbn,
            $name,
            $descriptionOrNull,
            $publisher,
            $publishedAt,
            $language,
            $genre,
            $copies,
            $price,
            $onlineRentPrice,
            $onlineBuyPrice,
            $coverImageOrNull,
            $onlineCopyPdfOrNull,
            $id
        );
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Failed to update book'];
        }

        return ['success' => true, 'message' => 'Book updated successfully'];
    }

    public function deleteBook($id) {
        $id = (int)$id;
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid book id'];
        }

        $check = $this->db->prepare(
            "SELECT id FROM BookTransactions WHERE book_id = ? AND is_returned = 0 LIMIT 1"
        );
        $check->bind_param('i', $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Book has active transactions and cannot be deleted'];
        }

        $stmt = $this->db->prepare("DELETE FROM Books WHERE id = ?");
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Failed to delete book'];
        }

        if ($stmt->affected_rows <= 0) {
            return ['success' => false, 'message' => 'Book not found'];
        }

        return ['success' => true, 'message' => 'Book deleted successfully'];
    }

    public function updateUser($id, $firstName, $lastName, $email, $role, $isActive) {
        $id = (int)$id;
        $firstName = trim((string)$firstName);
        $lastName = trim((string)$lastName);
        $email = trim((string)$email);
        $role = strtoupper(trim((string)$role));
        $isActive = (int)$isActive === 1 ? 1 : 0;

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
             SET first_name = ?, last_name = ?, email = ?, role = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->bind_param('ssssii', $firstName, $lastName, $email, $role, $isActive, $id);
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Failed to update user'];
        }

        if ($stmt->affected_rows < 0) {
            return ['success' => false, 'message' => 'User not found'];
        }

        return ['success' => true, 'message' => 'User updated successfully'];
    }

    public function createUser($firstName, $lastName, $email, $password, $role, $isActive) {
        $firstName = trim((string)$firstName);
        $lastName = trim((string)$lastName);
        $email = trim((string)$email);
        $password = (string)$password;
        $role = strtoupper(trim((string)$role));
        $isActive = (int)$isActive === 1 ? 1 : 0;

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
            "INSERT INTO Users (first_name, last_name, email, password, role, is_active, is_verified, verified_at)
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW())"
        );
        $stmt->bind_param('sssssi', $firstName, $lastName, $email, $passwordHash, $role, $isActive);

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

            $stmt = $this->db->prepare("DELETE FROM Fines WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM Fines WHERE book_transaction_id IN (SELECT id FROM BookTransactions WHERE user_id = ?)");
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

    private function countActiveRentals() {
        $result = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM BookTransactions
             WHERE transaction_type = 'RENT' AND is_returned = 0"
        );
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }

    private function countPendingFines() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM Fines WHERE is_paid = 0");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }

    private function countOverdueBooks() {
        $result = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM BookTransactions
             WHERE transaction_type = 'RENT'
               AND is_returned = 0
               AND due_date IS NOT NULL
               AND due_date < NOW()"
        );
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }

    private function sumWalletCreditsToday() {
        $result = $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM WalletTransactions
             WHERE type = 'CREDIT' AND DATE(created_at) = CURDATE()"
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
}

