<?php
require_once __DIR__ . '/Database.php';

class AdminSearch {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * @param string $type books|users|transactions|overdue
     * @param string $query
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
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
            case 'overdue':
                return $this->searchOverdue($like, $limit);
            default:
                return [];
        }
    }

    private function searchBooks($like, $limit) {
        $stmt = $this->db->prepare(
            "SELECT id, isbn, name, publisher, number_of_copies, price, created_at
             FROM Books
             WHERE isbn LIKE ? OR name LIKE ? OR publisher LIKE ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('sssi', $like, $like, $like, $limit);
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
            "SELECT bt.id, bt.transaction_type, bt.amount_paid, bt.due_date, bt.created_at, bt.is_returned,
                    b.name AS book_name,
                    u.first_name,
                    u.last_name
             FROM BookTransactions bt
             INNER JOIN Books b ON b.id = bt.book_id
             INNER JOIN Users u ON u.id = bt.user_id
             WHERE b.name LIKE ?
                OR u.first_name LIKE ?
                OR u.last_name LIKE ?
                OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
                OR bt.transaction_type LIKE ?
             ORDER BY bt.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('sssssi', $like, $like, $like, $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function searchOverdue($like, $limit) {
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
               AND (b.name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?
                    OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)
             ORDER BY bt.due_date ASC
             LIMIT ?"
        );
        $stmt->bind_param('ssssi', $like, $like, $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
