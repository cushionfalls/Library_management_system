<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Wallet.php';
require_once __DIR__ . '/Membership.php';
require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/Session.php';

class DigitalLibrary {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureSchema();
    }

    public function purchaseOnlineBook($userId, $bookId) {
        $userId = (int)$userId;
        $bookId = (int)$bookId;
        if ($userId <= 0 || $bookId <= 0) {
            return ['success' => false, 'message' => 'Invalid purchase request'];
        }

        $book = $this->getBookForDigitalAccess($bookId);
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found'];
        }
        if (empty($book['online_copy_pdf'])) {
            return ['success' => false, 'message' => 'Digital copy is not available'];
        }
        $price = (int)($book['online_buy_price'] ?? 0);

        if ($this->hasOwnedAccess($userId, $bookId)) {
            return ['success' => false, 'message' => 'You already own this book'];
        }

        $this->db->begin_transaction();
        try {
            if ($price > 0) {
                $stmtW = $this->db->prepare("UPDATE Users SET wallet = wallet - ?, updated_at = NOW() WHERE id = ? AND wallet >= ?");
                $stmtW->bind_param('iii', $price, $userId, $price);
                if (!$stmtW->execute() || $stmtW->affected_rows <= 0) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Insufficient wallet balance'];
                }
            }

            $type = 'DEBIT';
            $reason = 'BOOK_BUY';
            $stmtTx = $this->db->prepare("INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmtTx->bind_param('iiss', $userId, $price, $type, $reason);
            if (!$stmtTx->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log wallet transaction'];
            }
            $walletTxId = (int)$this->db->insert_id;

            $accessType = 'OWNED';
            $stmtA = $this->db->prepare("INSERT INTO UserBookAccess (user_id, book_id, access_type, source_ref, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmtA->bind_param('iisi', $userId, $bookId, $accessType, $walletTxId);
            if (!$stmtA->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to grant access'];
            }

            $this->db->commit();
            $wallet = new Wallet();
            $walletBalance = $wallet->getBalance($userId);
            $this->sendPurchaseNotification($userId, $book, $price, $walletBalance);
            return [
                'success' => true,
                'message' => 'Book purchased successfully',
                'wallet_balance' => $walletBalance,
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Purchase failed'];
        }
    }

    public function unlockWithMembership($userId, $bookId) {
        $userId = (int)$userId;
        $bookId = (int)$bookId;
        if ($userId <= 0 || $bookId <= 0) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $book = $this->getBookForDigitalAccess($bookId);
        if (!$book) return ['success' => false, 'message' => 'Book not found'];
        if (empty($book['online_copy_pdf'])) return ['success' => false, 'message' => 'Digital copy is not available'];

        $membership = new Membership();
        $active = $membership->getActiveMembership($userId);
        if (!$active) {
            return ['success' => false, 'message' => 'Active membership is required'];
        }

        if ($this->hasAnyAccess($userId, $bookId)) {
            return ['success' => false, 'message' => 'Book is already in your library'];
        }

        $accessType = 'MEMBERSHIP';
        $sourceRef = (int)($active['id'] ?? 0);
        $stmt = $this->db->prepare("INSERT INTO UserBookAccess (user_id, book_id, access_type, source_ref, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param('iisi', $userId, $bookId, $accessType, $sourceRef);
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Failed to add book to your library'];
        }

        return ['success' => true, 'message' => 'Book added to My Books using membership'];
    }

    public function getMyBooks($userId) {
        $session = new Session();
        $isAdminOrLibrarian = $session->isAdmin() || $session->isLibrarian();
        
        $sql = "SELECT uba.id, uba.book_id, uba.access_type, uba.created_at,
                       b.name, b.description, b.genre, b.cover_image, b.online_copy_pdf,
                       COALESCE(GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', '), '') AS authors,
                       ubp.progress_percent, ubp.current_location, ubp.last_opened_at
                FROM Books b
                LEFT JOIN UserBookAccess uba ON uba.book_id = b.id AND uba.user_id = ?
                LEFT JOIN UserBookProgress ubp ON ubp.user_id = ? AND ubp.book_id = b.id
                LEFT JOIN BookAuthors ba ON ba.book_id = b.id
                LEFT JOIN Authors a ON a.id = ba.author_id
                WHERE (uba.user_id = ?" . ($isAdminOrLibrarian ? " OR 1=1" : "") . ")
                GROUP BY b.id
                ORDER BY COALESCE(ubp.last_opened_at, uba.created_at) DESC, uba.id DESC";
        $stmt = $this->db->prepare($sql);
        if ($isAdminOrLibrarian) {
            $stmt->bind_param('iii', $userId, $userId, $userId);
        } else {
            $stmt->bind_param('iii', $userId, $userId, $userId);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = [
                'access_id' => (int)$r['id'],
                'book_id' => (int)$r['book_id'],
                'name' => (string)($r['name'] ?? ''),
                'description' => (string)($r['description'] ?? ''),
                'genre' => (string)($r['genre'] ?? ''),
                'authors' => trim((string)($r['authors'] ?? '')) ?: 'Unknown Author',
                'cover_image_url' => $this->assetUrl($r['cover_image'] ?? ''),
                'epub_url' => $this->assetUrl($r['online_copy_pdf'] ?? ''),
                'access_type' => (string)($r['access_type'] ?? 'ADMIN_ACCESS'),
                'progress_percent' => (int)($r['progress_percent'] ?? 0),
                'current_location' => (string)($r['current_location'] ?? ''),
                'last_opened_at' => (string)($r['last_opened_at'] ?? ''),
                'added_at' => (string)($r['created_at'] ?? ''),
            ];
        }
        return $rows;
    }

    public function saveProgress($userId, $bookId, $progressPercent, $currentLocation) {
        $userId = (int)$userId;
        $bookId = (int)$bookId;
        $progress = max(0, min(100, (int)$progressPercent));
        $location = substr(trim((string)$currentLocation), 0, 255);
        if (!$this->hasAnyAccess($userId, $bookId)) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $sql = "INSERT INTO UserBookProgress (user_id, book_id, progress_percent, current_location, last_opened_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    progress_percent = VALUES(progress_percent),
                    current_location = VALUES(current_location),
                    last_opened_at = NOW(),
                    updated_at = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iiis', $userId, $bookId, $progress, $location);
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Unable to save progress'];
        }
        return ['success' => true, 'message' => 'Progress saved'];
    }

    public function getReaderState($userId, $bookId) {
        $userId = (int)$userId;
        $bookId = (int)$bookId;
        if (!$this->hasAnyAccess($userId, $bookId)) return null;
        $stmt = $this->db->prepare("SELECT progress_percent, current_location, last_opened_at FROM UserBookProgress WHERE user_id = ? AND book_id = ? LIMIT 1");
        $stmt->bind_param('ii', $userId, $bookId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return ['progress_percent' => 0, 'current_location' => '', 'last_opened_at' => null];
        return [
            'progress_percent' => (int)($row['progress_percent'] ?? 0),
            'current_location' => (string)($row['current_location'] ?? ''),
            'last_opened_at' => (string)($row['last_opened_at'] ?? ''),
        ];
    }

    public function getAccessForUserBook($userId, $bookId) {
        $userId = (int)$userId;
        $bookId = (int)$bookId;
        if ($userId <= 0 || $bookId <= 0) return null;
        $stmt = $this->db->prepare("SELECT access_type, created_at FROM UserBookAccess WHERE user_id = ? AND book_id = ? LIMIT 1");
        $stmt->bind_param('ii', $userId, $bookId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;
        return [
            'access_type' => (string)($row['access_type'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? '')
        ];
    }

    private function hasOwnedAccess($userId, $bookId) {
        $accessType = 'OWNED';
        $stmt = $this->db->prepare("SELECT id FROM UserBookAccess WHERE user_id = ? AND book_id = ? AND access_type = ? LIMIT 1");
        $stmt->bind_param('iis', $userId, $bookId, $accessType);
        $stmt->execute();
        return (bool)$stmt->get_result()->fetch_assoc();
    }

    private function hasAnyAccess($userId, $bookId) {
        $session = new Session();
        if ($session->isAdmin() || $session->isLibrarian()) return true;
        
        $stmt = $this->db->prepare("SELECT id FROM UserBookAccess WHERE user_id = ? AND book_id = ? LIMIT 1");
        $stmt->bind_param('ii', $userId, $bookId);
        $stmt->execute();
        return (bool)$stmt->get_result()->fetch_assoc();
    }

    private function getBookForDigitalAccess($bookId) {
        $stmt = $this->db->prepare("SELECT id, name, online_buy_price, online_copy_pdf FROM Books WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $bookId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function getUserContact($userId) {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email FROM Users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function assetUrl($path) {
        $raw = trim((string)$path);
        if ($raw === '') return '';
        if (stripos($raw, 'http://') === 0 || stripos($raw, 'https://') === 0) return $raw;
        if ($raw[0] === '/') return APP_URL . $raw;
        return APP_URL . '/' . ltrim($raw, '/');
    }

    private function sendPurchaseNotification($userId, array $book, $amountPaidCents, $walletBalanceCents) {
        $user = $this->getUserContact((int)$userId);
        if (!$user || empty($user['email'])) {
            return;
        }

        $recipientName = trim(((string)($user['first_name'] ?? '')) . ' ' . ((string)($user['last_name'] ?? '')));
        if ($recipientName === '') {
            $recipientName = (string)($user['first_name'] ?? 'Reader');
        }

        $bookName = (string)($book['name'] ?? 'Your Book');

        try {
            $emailService = new EmailService();
            $emailService->sendBookPurchaseConfirmation(
                (string)$user['email'],
                $recipientName,
                $bookName,
                (int)$amountPaidCents,
                (int)$walletBalanceCents
            );
        } catch (Exception $e) {
            error_log('Purchase confirmation email failed: ' . $e->getMessage());
        }
    }

    private function ensureSchema() {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS UserBookAccess (
                id int PRIMARY KEY AUTO_INCREMENT,
                user_id int NOT NULL,
                book_id int NOT NULL,
                access_type ENUM('OWNED', 'MEMBERSHIP') NOT NULL,
                source_ref int DEFAULT NULL,
                created_at datetime NOT NULL DEFAULT (now()),
                updated_at datetime,
                UNIQUE KEY uniq_user_book_access (user_id, book_id),
                KEY idx_user_book_access_user (user_id),
                KEY idx_user_book_access_book (book_id),
                CONSTRAINT fk_uba_user FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
                CONSTRAINT fk_uba_book FOREIGN KEY (book_id) REFERENCES Books(id) ON DELETE CASCADE
            )"
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS UserBookProgress (
                id int PRIMARY KEY AUTO_INCREMENT,
                user_id int NOT NULL,
                book_id int NOT NULL,
                progress_percent int NOT NULL DEFAULT 0,
                current_location varchar(255),
                last_opened_at datetime,
                created_at datetime NOT NULL DEFAULT (now()),
                updated_at datetime,
                UNIQUE KEY uniq_user_book_progress (user_id, book_id),
                KEY idx_user_book_progress_user (user_id),
                KEY idx_user_book_progress_book (book_id),
                CONSTRAINT fk_ubp_user FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
                CONSTRAINT fk_ubp_book FOREIGN KEY (book_id) REFERENCES Books(id) ON DELETE CASCADE
            )"
        );
        // Allow storing richer reader markers (CFI + page metadata).
        $this->db->query("ALTER TABLE UserBookProgress MODIFY current_location TEXT");
    }
}

