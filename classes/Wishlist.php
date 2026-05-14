<?php
require_once __DIR__ . '/Database.php';

class Wishlist {
    private $db;
    private $maxItems = 50;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function toggle($userId, $bookId) {
        $userId = (int)$userId;
        $bookId = (int)$bookId;

        if ($userId <= 0 || $bookId <= 0) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        // Check if already in wishlist
        $stmt = $this->db->prepare("SELECT id FROM Wishlist WHERE user_id = ? AND book_id = ? LIMIT 1");
        $stmt->bind_param('ii', $userId, $bookId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Remove from wishlist
            $stmt = $this->db->prepare("DELETE FROM Wishlist WHERE user_id = ? AND book_id = ?");
            $stmt->bind_param('ii', $userId, $bookId);
            if ($stmt->execute()) {
                return ['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist'];
            }
            return ['success' => false, 'message' => 'Failed to remove from wishlist'];
        } else {
            // Check limit
            $countStmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM Wishlist WHERE user_id = ?");
            $countStmt->bind_param('i', $userId);
            $countStmt->execute();
            $count = $countStmt->get_result()->fetch_assoc()['cnt'];

            if ($count >= $this->maxItems) {
                return ['success' => false, 'message' => "Wishlist is limited to {$this->maxItems} books"];
            }

            // Add to wishlist
            $stmt = $this->db->prepare("INSERT INTO Wishlist (user_id, book_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $userId, $bookId);
            if ($stmt->execute()) {
                return ['success' => true, 'action' => 'added', 'message' => 'Added to wishlist'];
            }
            return ['success' => false, 'message' => 'Failed to add to wishlist'];
        }
    }

    public function getWishlist($userId) {
        $userId = (int)$userId;
        $sql = "SELECT b.id, b.name, b.price, b.online_buy_price, b.cover_image, 
                       COALESCE(GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', '), '') AS authors
                FROM Wishlist w
                JOIN Books b ON w.book_id = b.id
                LEFT JOIN BookAuthors ba ON ba.book_id = b.id
                LEFT JOIN Authors a ON a.id = ba.author_id
                WHERE w.user_id = ?
                GROUP BY b.id
                ORDER BY w.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $items = [];
        while ($r = $res->fetch_assoc()) {
            $cover = trim((string)($r['cover_image'] ?? ''));
            if ($cover !== '' && stripos($cover, 'http://') !== 0 && stripos($cover, 'https://') !== 0) {
                $cover = APP_URL . '/' . ltrim($cover, '/');
            }
            
            $items[] = [
                'id' => (int)$r['id'],
                'name' => (string)$r['name'],
                'price' => (int)$r['price'],
                'online_buy_price' => (int)($r['online_buy_price'] ?? 0),
                'cover_image_url' => $cover,
                'authors' => trim((string)$r['authors']) ?: 'Unknown Author'
            ];
        }
        return $items;
    }

    public function getCount($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM Wishlist WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public function removeFromWishlist($userId, $bookId) {
        $userId = (int)$userId;
        $bookId = (int)$bookId;
        $stmt = $this->db->prepare("DELETE FROM Wishlist WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param('ii', $userId, $bookId);
        return $stmt->execute();
    }
}
