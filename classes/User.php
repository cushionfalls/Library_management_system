<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/EmailService.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($first_name, $last_name, $email, $password, $dob = null, $phone = null) {
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);

        $stmt = $this->db->prepare("INSERT INTO Users (first_name, last_name, email, password, dob, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $first_name, $last_name, $email, $hashedPassword, $dob, $phone);

        if ($stmt->execute()) {
            return ['success' => true, 'user_id' => $this->db->insert_id, 'message' => 'Registration successful'];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, first_name, password, role, is_active FROM Users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Email not found'];
        }

        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid password'];
        }

        return [
            'success' => true,
            'user_id' => $user['id'],
            'name' => $user['first_name'],
            'role' => $user['role'],
            'message' => 'Login successful'
        ];
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, role, dob, phone_number, profile_image, is_active, wallet, created_at FROM Users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM Users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function updateProfile($id, $first_name, $last_name, $dob, $phone_number, $profile_image = null) {
        if ($profile_image) {
            $stmt = $this->db->prepare("UPDATE Users SET first_name = ?, last_name = ?, dob = ?, phone_number = ?, profile_image = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('sssssi', $first_name, $last_name, $dob, $phone_number, $profile_image, $id);
        } else {
            $stmt = $this->db->prepare("UPDATE Users SET first_name = ?, last_name = ?, dob = ?, phone_number = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('ssssi', $first_name, $last_name, $dob, $phone_number, $id);
        }

        return $stmt->execute();
    }

    public function changePassword($id, $oldPassword, $newPassword) {
        $stmt = $this->db->prepare("SELECT password FROM Users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        $user = $result->fetch_assoc();

        if (!password_verify($oldPassword, $user['password'])) {
            return false;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
        $stmt = $this->db->prepare("UPDATE Users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('si', $hashedPassword, $id);

        return $stmt->execute();
    }

    // Resets password by email (views/forgot_password.php OTP flow).
    public function resetPasswordByEmail($email, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
        $stmt = $this->db->prepare("UPDATE Users SET password = ?, updated_at = NOW() WHERE email = ? LIMIT 1");
        $stmt->bind_param('ss', $hashedPassword, $email);
        return $stmt->execute();
    }

    public function getWallet($user_id) {
        $stmt = $this->db->prepare("SELECT wallet FROM Users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['wallet'] ?? 0;
    }

    public function updateWallet($user_id, $amount) {
        $stmt = $this->db->prepare("UPDATE Users SET wallet = wallet + ? WHERE id = ?");
        $stmt->bind_param('ii', $amount, $user_id);
        return $stmt->execute();
    }

    public function getAllUsers($limit = null, $offset = 0) {
        $sql = "SELECT * FROM Users WHERE role != 'ADMIN' ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        return $this->db->query($sql);
    }

    public function deactivateUser($id) {
        $stmt = $this->db->prepare("UPDATE Users SET is_active = 0 WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function activateUser($id) {
        $stmt = $this->db->prepare("UPDATE Users SET is_active = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function updateRole($id, $role) {
        $allowedRoles = ['USER', 'LIBRARIAN'];
        if (!in_array($role, $allowedRoles, true)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE Users SET role = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('si', $role, $id);
        return $stmt->execute();
    }

    public function deleteUser($id) {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT email FROM Users WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if (!$user) {
                $this->db->rollback();
                return false;
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

            $stmt = $this->db->prepare("DELETE FROM Users WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
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

