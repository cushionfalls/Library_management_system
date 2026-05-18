<?php
/**
 * JSON API for register, login, OTP, logout.
 * Password reset for views/forgot_password.php + public/js/forgot_password.js:
 *   request-password-reset, verify-password-reset-otp, reset-password
 */
// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/OTP.php';
require_once __DIR__ . '/../classes/EmailService.php';
require_once __DIR__ . '/../classes/Session.php';

class AuthController {
    private $user;
    private $otp;
    private $email;
    private $session;

    public function __construct() {
        $this->user = new User();
        $this->otp = new OTP();
        $this->email = new EmailService();
        $this->session = new Session();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            return ['error' => 'All fields are required'];
        }

        if (strlen($first_name) > 20 || strlen($last_name) > 20) {
            return ['error' => 'First name and last name must be at most 20 characters'];
        }

        if (!preg_match('/^[a-zA-Z]+$/', $first_name) || !preg_match('/^[a-zA-Z]+$/', $last_name)) {
            return ['error' => 'First name and last name must contain only letters (no spaces, numbers or special characters)'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Invalid email format'];
        }

        if (strlen($password) < 6) {
            return ['error' => 'Password must be at least 6 characters'];
        }

        if ($password !== $confirm_password) {
            return ['error' => 'Passwords do not match'];
        }

        // Check if email already exists
        if ($this->user->emailExists($email)) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT is_verified FROM Users WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();

            if ($existing && !$existing['is_verified']) {
                return [
                    'error' => 'Email already registered but not verified.',
                    'unverified_email' => true,
                    'email' => $email
                ];
            }

            return ['error' => 'Email already registered'];
        }

        // Register user
        $result = $this->user->register($first_name, $last_name, $email, $password);

        if ($result['success']) {
            $user_id = $result['user_id'];

            // Generate and send OTP
            $otp_res = $this->otp->generate($email);

            if (is_array($otp_res) && isset($otp_res['on_cooldown'])) {
                return [
                    'error' => "Please wait {$otp_res['remaining']} seconds before requesting another code.",
                    'on_cooldown' => true,
                    'remaining' => $otp_res['remaining']
                ];
            }

            if ($otp_res) {
                $otp_code = $otp_res;
                $sent = $this->email->sendOTP($email, $otp_code, $first_name);
                if (!$sent) {
                    return ['error' => 'Failed to send OTP email. Please try again in a minute.'];
                }

                // Store user_id and email in session temporarily
                $this->session->set('temp_user_id', $user_id);
                $this->session->set('temp_email', $email);

                return [
                    'success' => true,
                    'message' => 'Registration code sent to your email successfully. Please verify your email with the OTP sent to ' . $email,
                    'user_id' => $user_id,
                    'email' => $email
                ];
            } else {
                return ['error' => 'Failed to send OTP. Please try again'];
            }
        }

        return ['error' => $result['message'] ?? 'Registration failed'];
    }

    public function verifyOTP() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $email = trim($_POST['email'] ?? '');
        $otp = trim($_POST['otp'] ?? '');

        if (empty($email) || empty($otp)) {
            return ['error' => 'Email and OTP are required'];
        }

        // Treat already-verified accounts as success to avoid confusion on duplicate submits.
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, first_name, is_verified FROM Users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $existingUser = $stmt->get_result()->fetch_assoc();

        if ($existingUser && intval($existingUser['is_verified']) === 1) {
            return [
                'success' => true,
                'message' => 'Email is already verified. You can now login.',
                'user_id' => $existingUser['id']
            ];
        }

        if ($this->otp->verify($email, $otp)) {
            // Get user by email
            $stmt = $db->prepare("SELECT id FROM Users WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result) {
                $user_id = $result['id'];

                // Mark user as verified
                $this->otp->markUserVerified($user_id);

                // Send welcome email
                $user = $this->user->getUserById($user_id);
                $this->email->sendWelcome($email, $user['first_name']);

                // Clear temporary session data
                $this->session->remove('temp_user_id');
                $this->session->remove('temp_email');

                return [
                    'success' => true,
                    'message' => 'Email verified successfully. You can now login',
                    'user_id' => $user_id
                ];
            }

            return ['error' => 'User not found'];
        }

        return ['error' => 'Invalid or expired OTP'];
    }

    public function resendOTP() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            return ['error' => 'Email is required'];
        }

        // Check if email exists and is not verified
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, first_name, is_verified FROM Users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            return ['error' => 'Email not found'];
        }

        if ($result['is_verified']) {
            return ['error' => 'Email already verified'];
        }

        // Generate and send new OTP
        $otp_res = $this->otp->generate($email);

        if (is_array($otp_res) && isset($otp_res['on_cooldown'])) {
            return [
                'error' => "Please wait {$otp_res['remaining']} seconds before requesting another code.",
                'on_cooldown' => true,
                'remaining' => $otp_res['remaining']
            ];
        }

        if ($otp_res) {
            $otp_code = $otp_res;
            $sent = $this->email->sendOTP($email, $otp_code, '');
            if (!$sent) {
                return ['error' => 'Failed to send OTP email. Please try again in a minute.'];
            }
            return ['success' => true, 'message' => 'OTP sent to ' . $email];
        }

        return ['error' => 'Failed to send OTP'];
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            return ['error' => 'Email and password are required'];
        }

        $loginResult = $this->user->login($email, $password);

        if ($loginResult['success']) {
            $this->session->login($loginResult['user_id'], $loginResult['role'], $loginResult['name'], $loginResult['is_verified'] ?? 0);
            return ['success' => true, 'message' => 'Login successful', 'role' => $loginResult['role']];
        }

        return ['error' => $loginResult['message'] ?? 'Login failed'];
    }

    // —— Password reset (views/forgot_password.php) —— Sends OTP to the user's email.
    public function requestPasswordReset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $email = trim($_POST['email'] ?? '');
        if (empty($email)) return ['error' => 'Email is required'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['error' => 'Invalid email format'];

        if (!$this->user->emailExists($email)) {
            return ['error' => 'Email not found'];
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT first_name FROM Users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();
        $firstName = $userRow['first_name'] ?? '';

        $otp_res = $this->otp->generate($email);
        if (is_array($otp_res) && isset($otp_res['on_cooldown'])) {
            return [
                'error' => "Please wait {$otp_res['remaining']} seconds before requesting another code.",
                'on_cooldown' => true,
                'remaining' => $otp_res['remaining']
            ];
        }
        if (!$otp_res) {
            return ['error' => 'Failed to send OTP. Please try again'];
        }
        $otp_code = $otp_res;

        $sent = $this->email->sendPasswordResetOTP($email, $otp_code, $firstName);
        if (!$sent) {
            return ['error' => 'Failed to send OTP email. Please try again in a minute'];
        }

        return [
            'success' => true,
            'message' => 'OTP sent to ' . $email
        ];
    }

    /**
     * Step 2 (views/forgot_password.php): confirm OTP; session is marked until resetPassword completes or expires.
     */
    public function verifyPasswordResetOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $email = trim($_POST['email'] ?? '');
        $otp = trim($_POST['otp'] ?? '');

        if (empty($email) || empty($otp)) {
            return ['error' => 'Email and OTP are required'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Invalid email format'];
        }
        if (!$this->user->emailExists($email)) {
            return ['error' => 'Invalid or expired OTP'];
        }
        if (!$this->otp->verify($email, $otp)) {
            return ['error' => 'Invalid or expired OTP'];
        }

        $this->session->set('password_reset_email', $email);
        $this->session->set('password_reset_expires', time() + 900);

        return [
            'success' => true,
            'message' => 'Code verified. You can set a new password.',
            'email' => $email
        ];
    }

    /**
     * Step 3: new password (requires a prior verifyPasswordResetOtp in the same session).
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $email = trim($_POST['email'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($email) || $newPassword === '' || $confirmPassword === '') {
            return ['error' => 'Email, new password, and confirm password are required'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Invalid email format'];
        }

        $authorizedEmail = $this->session->get('password_reset_email');
        $expires = (int) $this->session->get('password_reset_expires', 0);
        if ($authorizedEmail === null || $authorizedEmail === '' || strcasecmp($authorizedEmail, $email) !== 0 || time() > $expires) {
            return ['error' => 'Session expired or not verified. Please start the password reset process again.'];
        }

        if (strlen($newPassword) < 6) {
            return ['error' => 'Password must be at least 6 characters'];
        }
        if ($newPassword !== $confirmPassword) {
            return ['error' => 'Passwords do not match'];
        }

        if (!$this->user->resetPasswordByEmail($email, $newPassword)) {
            return ['error' => 'Failed to reset password'];
        }

        $this->session->remove('password_reset_email');
        $this->session->remove('password_reset_expires');

        return ['success' => true, 'message' => 'Password reset successfully. You can sign in.'];
    }

    public function logout() {
        $this->session->logout();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
}

// Handle requests
try {
    $action = $_GET['action'] ?? 'register';
    
    // CSRF Protection for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $session = new Session();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$session->validateCSRFToken($token)) {
            ob_clean();
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }

    $controller = new AuthController();

    switch ($action) {
        case 'register':
            $response = $controller->register();
            break;
        case 'verify-otp':
            $response = $controller->verifyOTP();
            break;
        case 'resend-otp':
            $response = $controller->resendOTP();
            break;
        case 'login':
            $response = $controller->login();
            break;
        case 'request-password-reset':
            $response = $controller->requestPasswordReset();
            break;
        case 'verify-password-reset-otp':
            $response = $controller->verifyPasswordResetOtp();
            break;
        case 'reset-password':
            $response = $controller->resetPassword();
            break;
        case 'logout':
            $response = $controller->logout();
            break;
        default:
            $response = ['error' => 'Invalid action'];
    }

    // Clear output buffer and send clean JSON
    ob_clean();
    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
