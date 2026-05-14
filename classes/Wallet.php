<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Wallet {
    private $db;

    // Convention: ALL amounts in the Wallet system are stored and processed as CENTS (integers).
    // Conversions to dollars only happen in the display layer (JS formatUsdFromCents or PHP number_format).

    const TOPUP_MAX_AMOUNT = 100000; // cents ($1,000.00)
    const TX_MAX_LIMIT     = 10000;
    const SPEND_MAX_AMOUNT = 10000000; // cents ($100,000.00)

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

    /** @return array<int, array<string, mixed>> */
    public function getTransactions($userId, $limit = 20, $offset = 0) {
        $userId = (int)$userId;
        $limit  = max(1, min(self::TX_MAX_LIMIT, (int)$limit));
        $offset = max(0, (int)$offset);

        $stmt = $this->db->prepare(
            "SELECT id, amount, type, reason, created_at
             FROM WalletTransactions
             WHERE user_id = ?
             ORDER BY created_at DESC, id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();
        $res  = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $r['id']     = (int)$r['id'];
            $r['amount'] = (int)$r['amount'];
            $rows[]      = $r;
        }
        return $rows;
    }

    public function hasExternalRef($userId, string $externalRef): bool {
        $userId = (int)$userId;
        $externalRef = trim($externalRef);
        if ($externalRef === '') return false;

        $stmt = $this->db->prepare("SELECT 1 FROM WalletTransactions WHERE user_id = ? AND external_ref = ? LIMIT 1");
        if (!$stmt) return false;
        $stmt->bind_param('is', $userId, $externalRef);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (bool)$row;
    }

    public function topUp($userId, $amount, $method = 'OTHER', string $externalRef = '') {
        $userId = (int)$userId;
        $amount = (int)$amount;
        $method = (string)$method;
        $externalRef = trim($externalRef);

        if ($amount <= 0)                         return ['success' => false, 'message' => 'Amount must be greater than 0'];
        if ($amount > self::TOPUP_MAX_AMOUNT)     return ['success' => false, 'message' => 'Amount exceeds limit of ' . self::TOPUP_MAX_AMOUNT];

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("UPDATE Users SET wallet = wallet + ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('ii', $amount, $userId);
            if (!$stmt->execute() || $stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to update wallet'];
            }

            $type   = 'CREDIT';
            $reason = 'TOP_UP';
            if ($externalRef !== '') {
                $stmt2 = $this->db->prepare(
                    "INSERT INTO WalletTransactions (user_id, amount, type, reason, external_ref, created_at)
                     VALUES (?, ?, ?, ?, ?, NOW())"
                );
                if ($stmt2) {
                    $stmt2->bind_param('iisss', $userId, $amount, $type, $reason, $externalRef);
                    if (!$stmt2->execute()) {
                        $this->db->rollback();
                        return ['success' => false, 'message' => 'Failed to log transaction'];
                    }
                } else {
                    // Backward compatible if column doesn't exist
                    $stmt2 = $this->db->prepare(
                        "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at) VALUES (?, ?, ?, ?, NOW())"
                    );
                    $stmt2->bind_param('iiss', $userId, $amount, $type, $reason);
                    if (!$stmt2->execute()) {
                        $this->db->rollback();
                        return ['success' => false, 'message' => 'Failed to log transaction'];
                    }
                }
            } else {
                $stmt2  = $this->db->prepare(
                    "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at) VALUES (?, ?, ?, ?, NOW())"
                );
                $stmt2->bind_param('iiss', $userId, $amount, $type, $reason);
                if (!$stmt2->execute()) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Failed to log transaction'];
                }
            }

            $this->db->commit();
            return [
                'success'        => true,
                'message'        => 'Top up successful',
                'balance'        => $this->getBalance($userId),
                'transaction_id' => (int)$this->db->insert_id,
                'method'         => $method,
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Top up failed'];
        }
    }

    public function spend($userId, $amount, $reason = 'OTHER') {
        $userId = (int)$userId;
        $amount = (int)$amount;
        $reason = (string)$reason;

        if ($amount <= 0)                          return ['success' => false, 'message' => 'Amount must be greater than 0'];
        if ($amount > self::SPEND_MAX_AMOUNT)      return ['success' => false, 'message' => 'Amount exceeds limit of ' . self::SPEND_MAX_AMOUNT];

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE Users SET wallet = wallet - ?, updated_at = NOW() WHERE id = ? AND wallet >= ?"
            );
            $stmt->bind_param('iii', $amount, $userId, $amount);
            if (!$stmt->execute() || $stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Insufficient wallet balance'];
            }

            $type  = 'DEBIT';
            $stmt2 = $this->db->prepare(
                "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at) VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt2->bind_param('iiss', $userId, $amount, $type, $reason);
            if (!$stmt2->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log transaction'];
            }

            $this->db->commit();
            return [
                'success'        => true,
                'message'        => 'Payment successful',
                'balance'        => $this->getBalance($userId),
                'transaction_id' => (int)$this->db->insert_id,
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Payment failed'];
        }
    }

    public function refund($userId, $amount) {
        $userId = (int)$userId;
        $amount = (int)$amount;

        if ($amount <= 0)                     return ['success' => false, 'message' => 'Amount must be greater than 0'];
        if ($amount > self::TOPUP_MAX_AMOUNT) return ['success' => false, 'message' => 'Amount exceeds limit of ' . self::TOPUP_MAX_AMOUNT];

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("UPDATE Users SET wallet = wallet + ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('ii', $amount, $userId);
            if (!$stmt->execute() || $stmt->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to refund wallet'];
            }

            $type   = 'CREDIT';
            $reason = 'REFUND';
            $stmt2  = $this->db->prepare(
                "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at) VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt2->bind_param('iiss', $userId, $amount, $type, $reason);
            if (!$stmt2->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log refund'];
            }

            $this->db->commit();
            return [
                'success'        => true,
                'message'        => 'Refund successful',
                'balance'        => $this->getBalance($userId),
                'transaction_id' => (int)$this->db->insert_id,
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Refund failed'];
        }
    }

    public function getAllTransactions($limit = 50, $offset = 0, $filterUserId = null) {
        $limit  = max(1, min(self::TX_MAX_LIMIT, (int)$limit));
        $offset = max(0, (int)$offset);

        $where  = '';
        $types  = '';
        $params = [];

        if ($filterUserId !== null && $filterUserId !== '') {
            $where    = "WHERE wt.user_id = ?";
            $types    = 'i';
            $params[] = (int)$filterUserId;
        }

        $sql = "SELECT wt.id, wt.user_id, u.first_name, u.last_name, u.email,
                       wt.amount, wt.type, wt.reason, wt.created_at
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
            $r['id']      = (int)$r['id'];
            $r['user_id'] = (int)$r['user_id'];
            $r['amount']  = (int)$r['amount'];
            $rows[]       = $r;
        }
        return $rows;
    }
}