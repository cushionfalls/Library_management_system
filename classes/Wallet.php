<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Wallet {
    private $db;
    const TOPUP_MAX_AMOUNT = 10000;
    const TX_MAX_LIMIT = 10000;
    const SPEND_MAX_AMOUNT = 100000;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getBalance($userId) {
        $stmt = $this->db->prepare("SELECT wallet FROM Users WHERE id = ? LIMIT 1");
        $userId = (int)$userId;
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['wallet'] ?? 0);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTransactions($userId, $limit = 20, $offset = 0) {
        $userId = (int)$userId;
        $limit = (int)$limit;
        $offset = (int)$offset;
        $limit = max(1, min(self::TX_MAX_LIMIT, $limit));
        $offset = max(0, $offset);

        $stmt = $this->db->prepare(
            "SELECT id, amount, type, reason, created_at
             FROM WalletTransactions
             WHERE user_id = ?
             ORDER BY created_at DESC, id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $r['id'] = (int)$r['id'];
            $r['amount'] = (int)$r['amount'];
            $rows[] = $r;
        }
        return $rows;
    }

    public function topUp($userId, $amount, $method = 'OTHER') {
        $userId = (int)$userId;
        $amount = (int)$amount;
        $method = (string)$method;
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than 0'];
        }
        if ($amount > self::TOPUP_MAX_AMOUNT) {
            return ['success' => false, 'message' => 'Amount exceeds limit of ' . self::TOPUP_MAX_AMOUNT];
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("UPDATE Users SET wallet = wallet + ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('ii', $amount, $userId);
            if (!$stmt->execute() || $stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to update wallet'];
            }

            $type = 'CREDIT';
            $reason = 'TOP_UP';
            $stmt2 = $this->db->prepare(
                "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt2->bind_param('iiss', $userId, $amount, $type, $reason);
            if (!$stmt2->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log transaction'];
            }

            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Top up successful',
                'balance' => $this->getBalance($userId),
                'transaction_id' => (int)$this->db->insert_id,
                'method' => $method
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Top up failed'];
        }
    }

    /**
     * Debit funds from wallet and log transaction.
     * @param string $reason One of WalletTransactions.reason enum values
     */
    public function spend($userId, $amount, $reason = 'OTHER') {
        $userId = (int)$userId;
        $amount = (int)$amount;
        $reason = (string)$reason;
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than 0'];
        }
        if ($amount > self::SPEND_MAX_AMOUNT) {
            return ['success' => false, 'message' => 'Amount exceeds limit of ' . self::SPEND_MAX_AMOUNT];
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE Users
                 SET wallet = wallet - ?, updated_at = NOW()
                 WHERE id = ? AND wallet >= ?"
            );
            $stmt->bind_param('iii', $amount, $userId, $amount);
            if (!$stmt->execute() || $stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Insufficient wallet balance'];
            }

            $type = 'DEBIT';
            $stmt2 = $this->db->prepare(
                "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt2->bind_param('iiss', $userId, $amount, $type, $reason);
            if (!$stmt2->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log transaction'];
            }

            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Payment successful',
                'balance' => $this->getBalance($userId),
                'transaction_id' => (int)$this->db->insert_id
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Payment failed'];
        }
    }

    public function refund($userId, $amount) {
        $userId = (int)$userId;
        $amount = (int)$amount;
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than 0'];
        }
        if ($amount > self::TOPUP_MAX_AMOUNT) {
            return ['success' => false, 'message' => 'Amount exceeds limit of ' . self::TOPUP_MAX_AMOUNT];
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("UPDATE Users SET wallet = wallet + ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('ii', $amount, $userId);
            if (!$stmt->execute() || $stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to refund wallet'];
            }

            $type = 'CREDIT';
            $reason = 'REFUND';
            $stmt2 = $this->db->prepare(
                "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt2->bind_param('iiss', $userId, $amount, $type, $reason);
            if (!$stmt2->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log refund'];
            }

            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Refund successful',
                'balance' => $this->getBalance($userId),
                'transaction_id' => (int)$this->db->insert_id
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Refund failed'];
        }
    }

    public function getAllTransactions($limit = 50, $offset = 0, $filterUserId = null) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $limit = max(1, min(self::TX_MAX_LIMIT, $limit));
        $offset = max(0, $offset);

        $where = '';
        $types = '';
        $params = [];

        if ($filterUserId !== null && $filterUserId !== '') {
            $where = "WHERE wt.user_id = ?";
            $types = 'i';
            $params[] = (int)$filterUserId;
        }

        $sql = "SELECT wt.id, wt.user_id, u.first_name, u.last_name, u.email, wt.amount, wt.type, wt.reason, wt.created_at
                FROM WalletTransactions wt
                INNER JOIN Users u ON u.id = wt.user_id
                $where
                ORDER BY wt.created_at DESC, wt.id DESC
                LIMIT $limit OFFSET $offset";

        if ($where !== '') {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $this->db->query($sql);
        }

        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $r['id'] = (int)$r['id'];
            $r['user_id'] = (int)$r['user_id'];
            $r['amount'] = (int)$r['amount'];
            $rows[] = $r;
        }
        return $rows;
    }
}

