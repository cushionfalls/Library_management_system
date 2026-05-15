<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Session {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->start();
    }

    public function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_TIMEOUT,
                'path' => '/',
                'secure' => SESSION_COOKIE_SECURE,
                'httponly' => SESSION_COOKIE_HTTPONLY,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function destroy() {
        session_destroy();
        session_unset();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'ADMIN';
    }

    public function isLibrarian() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'LIBRARIAN';
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public function getRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function isVerified() {
        return (int)($this->get('is_verified', 0)) === 1;
    }

    private static $userDataCache = null;

    public function getUserData() {
        if (!$this->isLoggedIn()) return null;

        if (self::$userDataCache !== null) {
            return self::$userDataCache;
        }

        $userId = $this->getUserId();
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        self::$userDataCache = $stmt->get_result()->fetch_assoc();
        return self::$userDataCache;
    }

    public function login($user_id, $role, $name = '', $is_verified = 1) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $name;
        $_SESSION['is_verified'] = (int)$is_verified;
        $_SESSION['login_time'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    public function logout() {
        session_unset();
        session_destroy();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'] ?? '',
                $params['secure'],
                $params['httponly']
            );
        }
    }

    public function checkTimeout() {
        if (!$this->isLoggedIn()) return true;

        $currentTime = time();
        $loginTime = $_SESSION['login_time'] ?? 0;

        if ($currentTime - $loginTime > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }

        $_SESSION['login_time'] = $currentTime;
        return true;
    }

    public function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken($token) {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
?>

