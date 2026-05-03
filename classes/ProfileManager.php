<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/config.php';

class ProfileManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, dob, phone_number, profile_image, wallet, is_active, created_at FROM Users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfile($userId, $firstName, $lastName, $dob = null, $phone = null, $profileImage = null) {
        if ($profileImage !== null && $profileImage !== '') {
            $stmt = $this->db->prepare("UPDATE Users SET first_name = ?, last_name = ?, dob = ?, phone_number = ?, profile_image = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('sssssi', $firstName, $lastName, $dob, $phone, $profileImage, $userId);
        } else {
            $stmt = $this->db->prepare("UPDATE Users SET first_name = ?, last_name = ?, dob = ?, phone_number = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('ssssi', $firstName, $lastName, $dob, $phone, $userId);
        }

        return $stmt->execute();
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        $stmt = $this->db->prepare("SELECT password FROM Users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($currentPassword, (string)$user['password'])) {
            return false;
        }

        $newHash = password_hash($newPassword, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
        $update = $this->db->prepare("UPDATE Users SET password = ?, updated_at = NOW() WHERE id = ?");
        $update->bind_param('si', $newHash, $userId);
        return $update->execute();
    }

    public function deleteAccount($userId) {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT email FROM Users WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if (!$row) {
                $this->db->rollback();
                return false;
            }
            $email = (string)$row['email'];

            $queries = [
                ["DELETE FROM WalletTransactions WHERE user_id = ?", 'i', $userId],
                ["DELETE FROM BookReviews WHERE user_id = ?", 'i', $userId],
                ["DELETE FROM BookTransactions WHERE user_id = ?", 'i', $userId],
                ["DELETE FROM Sessions WHERE user_id = ?", 'i', $userId]
            ];

            foreach ($queries as $item) {
                $q = $this->db->prepare($item[0]);
                $q->bind_param($item[1], $item[2]);
                $q->execute();
            }

            $deleteOtp = $this->db->prepare("DELETE FROM OTP WHERE email = ?");
            $deleteOtp->bind_param('s', $email);
            $deleteOtp->execute();

            $deleteUser = $this->db->prepare("DELETE FROM Users WHERE id = ?");
            $deleteUser->bind_param('i', $userId);
            $deleteUser->execute();

            if ($deleteUser->affected_rows <= 0) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
?>
