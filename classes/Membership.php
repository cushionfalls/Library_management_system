<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Wallet.php';
require_once __DIR__ . '/MembershipPlan.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/EmailService.php';

class Membership {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            $check = $this->db->query("SHOW COLUMNS FROM `UserMemberships` LIKE 'expiry_warning_sent'");
            if ($check && $check->num_rows === 0) {
                $this->db->query("ALTER TABLE `UserMemberships` ADD COLUMN `expiry_warning_sent` TINYINT NOT NULL DEFAULT 0");
            } else {
                $this->db->query("ALTER TABLE `UserMemberships` MODIFY COLUMN `expiry_warning_sent` TINYINT NOT NULL DEFAULT 0");
            }
        } catch (Exception $e) {
            error_log("Failed to ensure schema in Membership constructor: " . $e->getMessage());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPlans() {
        $rows = [];
        $res = $this->db->query("SELECT id, slug, name, duration_days, price, is_active FROM MembershipPlans WHERE is_active = 1 ORDER BY price ASC, id ASC");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $rows[] = [
                    'id' => (int)$r['id'],
                    'slug' => (string)$r['slug'],
                    'name' => (string)$r['name'],
                    'duration_days' => (int)$r['duration_days'],
                    'price' => (int)$r['price'],
                    'is_active' => (int)$r['is_active'] === 1,
                ];
            }
        }

        if (count($rows) === 0) {
            // Fallback if DB table wasn't created/seeded yet.
            $defaults = MembershipPlan::defaults();
            $id = 1;
            foreach ($defaults as $p) {
                $rows[] = [
                    'id' => $id++,
                    'slug' => $p['slug'],
                    'name' => $p['name'],
                    'duration_days' => (int)$p['duration_days'],
                    'price' => (int)$p['price'],
                    'is_active' => true,
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getActiveMembership($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare(
            "SELECT um.id, um.user_id, um.plan_id, um.starts_at, um.ends_at, um.status,
                    mp.slug AS plan_slug, mp.name AS plan_name, mp.duration_days, mp.price
             FROM UserMemberships um
             INNER JOIN MembershipPlans mp ON mp.id = um.plan_id
             WHERE um.user_id = ? AND um.status = 'ACTIVE' AND um.ends_at > NOW()
             ORDER BY um.ends_at DESC, um.id DESC
             LIMIT 1"
        );
        if (!$stmt) return null;
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;

        return [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'plan_id' => (int)$row['plan_id'],
            'plan_slug' => (string)$row['plan_slug'],
            'plan_name' => (string)$row['plan_name'],
            'starts_at' => (string)$row['starts_at'],
            'ends_at' => (string)$row['ends_at'],
            'status' => (string)$row['status'],
            'duration_days' => (int)$row['duration_days'],
            'price' => (int)$row['price'],
        ];
    }

    /**
     * Purchase or extend membership using wallet balance.
     * - If user already has an active plan, the new duration is added to their current end date.
     *
     * @return array<string, mixed>
     */
    public function purchaseWithWallet($userId, $planId) {
        $userId = (int)$userId;
        $planId = (int)$planId;

        // Load plan
        $stmt = $this->db->prepare("SELECT id, slug, name, duration_days, price, is_active FROM MembershipPlans WHERE id = ? AND is_active = 1 LIMIT 1");
        if (!$stmt) return ['success' => false, 'message' => 'Server error'];
        $stmt->bind_param('i', $planId);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();
        if (!$plan) return ['success' => false, 'message' => 'Invalid plan'];

        $price = (int)$plan['price'];
        $durationDays = (int)$plan['duration_days'];
        if ($price <= 0 || $durationDays <= 0) return ['success' => false, 'message' => 'Invalid plan configuration'];

        $wallet = new Wallet();

        $this->db->begin_transaction();
        try {
            // Determine start/end based on existing active membership.
            $stmtA = $this->db->prepare(
                "SELECT id, ends_at
                 FROM UserMemberships
                 WHERE user_id = ? AND status = 'ACTIVE' AND ends_at > NOW()
                 ORDER BY ends_at DESC, id DESC
                 LIMIT 1
                 FOR UPDATE"
            );
            if (!$stmtA) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Server error'];
            }
            $stmtA->bind_param('i', $userId);
            $stmtA->execute();
            $active = $stmtA->get_result()->fetch_assoc();

            $startAtSql = "NOW()";
            $baseEndSql = "NOW()";
            $extendExistingId = null;
            if ($active) {
                $extendExistingId = (int)$active['id'];
                $baseEndSql = "GREATEST(ends_at, NOW())";

                $currentPlanId = (int)($active['plan_id'] ?? 0);
                $currentPrice = (int)($active['price'] ?? 0);
                if ($price < $currentPrice) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Downgrading membership is not allowed'];
                }
            }

            // Wallet debit (inside same DB transaction).
            // Note: Wallet::spend uses its own begin/commit; we can't nest. So we perform wallet update + tx insert here.
            $stmtW = $this->db->prepare(
                "UPDATE Users
                 SET wallet = wallet - ?, updated_at = NOW()
                 WHERE id = ? AND wallet >= ?"
            );
            $stmtW->bind_param('iii', $price, $userId, $price);
            if (!$stmtW->execute() || $stmtW->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Insufficient wallet balance'];
            }

            $type = 'DEBIT';
            $reason = 'MEMBERSHIP';
            $stmtTx = $this->db->prepare(
                "INSERT INTO WalletTransactions (user_id, amount, type, reason, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmtTx->bind_param('iiss', $userId, $price, $type, $reason);
            if (!$stmtTx->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log wallet transaction'];
            }
            $walletTxId = (int)$this->db->insert_id;

            if ($extendExistingId !== null) {
                $sql = "UPDATE UserMemberships
                        SET plan_id = ?, starts_at = starts_at, ends_at = DATE_ADD($baseEndSql, INTERVAL ? DAY), expiry_warning_sent = 0, updated_at = NOW()
                        WHERE id = ? AND user_id = ?";
                $stmtU = $this->db->prepare($sql);
                $stmtU->bind_param('iiii', $planId, $durationDays, $extendExistingId, $userId);
                if (!$stmtU->execute()) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Failed to update membership'];
                }
                $membershipId = $extendExistingId;
            } else {
                $sql2 = "INSERT INTO UserMemberships (user_id, plan_id, status, starts_at, ends_at, created_at)
                         VALUES (?, ?, 'ACTIVE', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), NOW())";
                $stmtI = $this->db->prepare($sql2);
                $stmtI->bind_param('iii', $userId, $planId, $durationDays);
                if (!$stmtI->execute()) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Failed to create membership'];
                }
                $membershipId = (int)$this->db->insert_id;
            }

            $stmtP = $this->db->prepare(
                "INSERT INTO MembershipPurchases (user_id, membership_id, plan_id, amount, wallet_transaction_id, purchased_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmtP->bind_param('iiiii', $userId, $membershipId, $planId, $price, $walletTxId);
            if (!$stmtP->execute()) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to log membership purchase'];
            }

            $this->db->commit();

            // Send activation email
            try {
                $userObj = new User();
                $userData = $userObj->getUserById($userId);
                if ($userData) {
                    $emailSvc = new EmailService();
                    $activeNow = $this->getActiveMembership($userId);
                    if ($activeNow) {
                        $emailSvc->sendMembershipActivation(
                            $userData['email'],
                            $userData['first_name'],
                            $activeNow['plan_name'],
                            $activeNow['ends_at']
                        );
                    }
                }
            } catch (Exception $e) {
                error_log("Failed to send membership activation email: " . $e->getMessage());
            }

            $activeNow = $this->getActiveMembership($userId);
            return [
                'success' => true,
                'message' => 'Membership activated',
                'membership' => $activeNow,
                'wallet_balance' => $wallet->getBalance($userId),
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Membership purchase failed'];
        }
    }

    /**
     * Deactivate the currently active membership immediately.
     *
     * @return array<string, mixed>
     */
    public function deactivateMembership($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid user'];
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id
                 FROM UserMemberships
                 WHERE user_id = ? AND status = 'ACTIVE' AND ends_at > NOW()
                 ORDER BY ends_at DESC, id DESC
                 LIMIT 1
                 FOR UPDATE"
            );
            if (!$stmt) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Server error'];
            }
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $active = $stmt->get_result()->fetch_assoc();
            if (!$active) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'No active membership to deactivate'];
            }

            $membershipId = (int)$active['id'];
            $stmtU = $this->db->prepare(
                "UPDATE UserMemberships
                 SET status = 'CANCELLED', ends_at = NOW(), updated_at = NOW()
                 WHERE id = ? AND user_id = ?"
            );
            if (!$stmtU) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Server error'];
            }
            $stmtU->bind_param('ii', $membershipId, $userId);
            if (!$stmtU->execute() || $stmtU->affected_rows <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to deactivate membership'];
            }

            // Immediately remove all membership-based book access for the user
            $stmtAccess = $this->db->prepare("DELETE FROM UserBookAccess WHERE user_id = ? AND access_type = 'MEMBERSHIP'");
            if ($stmtAccess) {
                $stmtAccess->bind_param('i', $userId);
                $stmtAccess->execute();
            }

            $this->db->commit();

            // Send deactivation email
            try {
                $userObj = new User();
                $userData = $userObj->getUserById($userId);
                if ($userData) {
                    $emailSvc = new EmailService();
                    $emailSvc->sendMembershipDeactivation(
                        $userData['email'],
                        $userData['first_name'],
                        'Premium Plan'
                    );
                }
            } catch (Exception $e) {
                error_log("Failed to send membership deactivation email: " . $e->getMessage());
            }

            return [
                'success' => true,
                'message' => 'Membership deactivated successfully',
                'membership' => null,
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Failed to deactivate membership'];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPurchaseHistory($userId, $limit = 50, $offset = 0) {
        $userId = (int)$userId;
        $limit = max(1, min(500, (int)$limit));
        $offset = max(0, (int)$offset);

        $stmt = $this->db->prepare(
            "SELECT mpur.id, mpur.amount, mpur.purchased_at, mp.name AS plan_name, mp.slug AS plan_slug
             FROM MembershipPurchases mpur
             INNER JOIN MembershipPlans mp ON mp.id = mpur.plan_id
             WHERE mpur.user_id = ?
             ORDER BY mpur.purchased_at DESC, mpur.id DESC
             LIMIT ? OFFSET ?"
        );
        if (!$stmt) return [];
        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = [
                'id' => (int)$r['id'],
                'amount' => (int)$r['amount'],
                'purchased_at' => (string)$r['purchased_at'],
                'plan_name' => (string)$r['plan_name'],
                'plan_slug' => (string)$r['plan_slug'],
            ];
        }
        return $rows;
    }

    /**
     * Finds memberships expiring in exactly N days and sends notifications.
     * This should be called by a cron job once per day.
     */
    public function notifyExpiringMemberships($maxDays = 3) {
        $maxDays = (int)$maxDays;
        
        // Find ACTIVE memberships expiring within N days
        $sql = "SELECT um.id AS membership_id, um.user_id, um.ends_at, um.expiry_warning_sent, mp.name AS plan_name, u.email, u.first_name
                FROM UserMemberships um
                INNER JOIN MembershipPlans mp ON mp.id = um.plan_id
                INNER JOIN Users u ON u.id = um.user_id
                WHERE um.status = 'ACTIVE' 
                AND um.ends_at <= DATE_ADD(NOW(), INTERVAL ? DAY)
                AND um.ends_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param('i', $maxDays);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $emailSvc = new EmailService();
        $count = 0;
        
        while ($row = $res->fetch_assoc()) {
            $membershipId = (int)$row['membership_id'];
            $currentWarningSent = (int)$row['expiry_warning_sent'];
            
            // Calculate days left using PHP DateTime
            $endsAt = new DateTime($row['ends_at']);
            $now = new DateTime();
            $interval = $now->diff($endsAt);
            $daysLeft = (int)$interval->format('%r%a');
            if ($daysLeft < 0) $daysLeft = 0;
            
            $targetWarningSent = 0;
            if ($daysLeft >= 3) {
                // 3 days left warning
                if ($currentWarningSent < 1) {
                    $targetWarningSent = 1;
                }
            } elseif ($daysLeft == 2) {
                // 2 days left warning
                if ($currentWarningSent < 2) {
                    $targetWarningSent = 2;
                }
            } else {
                // 1 day (or 0 days) left warning
                if ($currentWarningSent < 3) {
                    $targetWarningSent = 3;
                }
            }
            
            if ($targetWarningSent > 0) {
                // Send warning email
                $sent = $emailSvc->sendMembershipExpiryWarning(
                    $row['email'],
                    $row['first_name'],
                    $row['plan_name'],
                    $row['ends_at'],
                    $daysLeft
                );
                
                if ($sent) {
                    // Update the warning sent status level
                    $stmtUpdate = $this->db->prepare("UPDATE UserMemberships SET expiry_warning_sent = ? WHERE id = ?");
                    if ($stmtUpdate) {
                        $stmtUpdate->bind_param('ii', $targetWarningSent, $membershipId);
                        $stmtUpdate->execute();
                    }
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * Process memberships that have expired.
     * Deactivates them (status = 'EXPIRED'), deletes related book access records,
     * and sends email notifications.
     */
    public function processExpiredMemberships() {
        // Find memberships that are ACTIVE but ends_at is in the past
        $sql = "SELECT um.id AS membership_id, um.user_id, um.ends_at, mp.name AS plan_name, u.email, u.first_name
                FROM UserMemberships um
                INNER JOIN MembershipPlans mp ON mp.id = um.plan_id
                INNER JOIN Users u ON u.id = um.user_id
                WHERE um.status = 'ACTIVE' AND um.ends_at <= NOW()";
        
        $res = $this->db->query($sql);
        if (!$res) return 0;
        
        $expiredCount = 0;
        $emailSvc = new EmailService();
        
        while ($row = $res->fetch_assoc()) {
            $membershipId = (int)$row['membership_id'];
            $userId = (int)$row['user_id'];
            $planName = (string)$row['plan_name'];
            $email = (string)$row['email'];
            $firstName = (string)$row['first_name'];
            
            $this->db->begin_transaction();
            try {
                // Update membership status to EXPIRED
                $stmtUm = $this->db->prepare("UPDATE UserMemberships SET status = 'EXPIRED', updated_at = NOW() WHERE id = ?");
                if ($stmtUm) {
                    $stmtUm->bind_param('i', $membershipId);
                    $stmtUm->execute();
                }
                
                // Delete user book access records granted via membership
                $stmtUba = $this->db->prepare("DELETE FROM UserBookAccess WHERE user_id = ? AND access_type = 'MEMBERSHIP'");
                if ($stmtUba) {
                    $stmtUba->bind_param('i', $userId);
                    $stmtUba->execute();
                }
                
                $this->db->commit();
                $expiredCount++;
                
                // Send email
                $emailSvc->sendMembershipExpired($email, $firstName, $planName);
                
            } catch (Exception $e) {
                $this->db->rollback();
                error_log("Failed to expire membership ID $membershipId: " . $e->getMessage());
            }
        }
        
        return $expiredCount;
    }
}

