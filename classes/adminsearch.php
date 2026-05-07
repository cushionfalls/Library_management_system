<?php
require_once __DIR__ . '/Database.php';

class AdminSearch {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function search($type, $query, $limit = 200) {
        $type = strtolower(trim((string)$type));
        $query = trim((string)$query);
        $limit = max(1, min(500, (int)$limit));

        if ($query === '') {
            return [];
        }

        $like = '%' . $query . '%';

        switch ($type) {
            case 'books':
                return $this->searchBooks($like, $limit);
            case 'users':
                return $this->searchUsers($like, $limit);
            case 'transactions':
                return $this->searchTransactions($like, $limit);
            default:
                return [];
        }
    }

    private function searchBooks($like, $limit) {
        $stmt = $this->db->prepare(
            "SELECT
                b.id,
                b.isbn,
                b.name,
                b.publisher,
                b.number_of_copies,
                b.price,
                b.cover_image,
                b.created_at,
                COALESCE(
                    GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', '),
                    ''
                ) AS authors
             FROM Books b
             LEFT JOIN BookAuthors ba ON ba.book_id = b.id
             LEFT JOIN Authors a ON a.id = ba.author_id
             WHERE b.isbn LIKE ?
                OR b.name LIKE ?
                OR b.publisher LIKE ?
                OR EXISTS (
                    SELECT 1
                    FROM BookAuthors ba2
                    INNER JOIN Authors a2 ON a2.id = ba2.author_id
                    WHERE ba2.book_id = b.id
                      AND CONCAT(a2.first_name, ' ', a2.last_name) LIKE ?
                )
             GROUP BY b.id
             ORDER BY b.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('ssssi', $like, $like, $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function searchUsers($like, $limit) {
        $stmt = $this->db->prepare(
            "SELECT id, first_name, last_name, email, role, is_active, created_at
             FROM Users
             WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?
                OR CONCAT(first_name, ' ', last_name) LIKE ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('ssssi', $like, $like, $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function searchTransactions($like, $limit) {
        $stmt = $this->db->prepare(
            "SELECT wt.id, 
                    wt.reason AS type, 
                    wt.amount, 
                    wt.created_at,
                    u.first_name,
                    u.last_name,
                    IF(wt.reason = 'BOOK_BUY', 'Book Purchase', wt.reason) AS title
             FROM WalletTransactions wt
             INNER JOIN Users u ON u.id = wt.user_id
             WHERE u.first_name LIKE ?
                OR u.last_name LIKE ?
                OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
                OR wt.reason LIKE ?
             ORDER BY wt.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('ssssi', $like, $like, $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
