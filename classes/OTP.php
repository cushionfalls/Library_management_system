<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class OTP {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function generate($email) {
        $cooldownTime = date('Y-m-d H:i:s', time() + OTP_VALIDITY - 60);
        $stmt = $this->db->prepare("SELECT id FROM OTP WHERE email = ? AND expires_at > ? LIMIT 1");
        $stmt->bind_param('ss', $email, $cooldownTime);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return false;
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiryTime = date('Y-m-d H:i:s', time() + OTP_VALIDITY);

        // Remove old OTP
        $stmt = $this->db->prepare("DELETE FROM OTP WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        // Insert new OTP
        $stmt = $this->db->prepare("INSERT INTO OTP (email, otp, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $email, $otp, $expiryTime);

        if ($stmt->execute()) {
            return $otp;
        }

        return false;
    }

    public function verify($email, $otp) {
        $currentTime = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("SELECT id FROM OTP WHERE email = ? AND otp = ? AND expires_at > ? LIMIT 1");
        $stmt->bind_param('sss', $email, $otp, $currentTime);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        // Mark as used
        $stmt = $this->db->prepare("DELETE FROM OTP WHERE email = ? AND otp = ?");
        $stmt->bind_param('ss', $email, $otp);
        $stmt->execute();

        return true;
    }

    public function isValidEmail($email) {
        $stmt = $this->db->prepare("SELECT id FROM OTP WHERE email = ? AND expires_at > NOW() LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function markUserVerified($user_id) {
        $stmt = $this->db->prepare("UPDATE Users SET is_verified = 1, verified_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        return $stmt->execute();
    }
}
?>

