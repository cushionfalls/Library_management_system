<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/ProfileManager.php';

class ProfileController {
    private $session;
    private $profile;

    public function __construct() {
        $this->session = new Session();
        $this->profile = new ProfileManager();
    }

    private function requireAuth() {
        if (!$this->session->isLoggedIn()) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }
        return null;
    }

    private function verifyCsrf() {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!$this->session->validateCSRFToken((string)$token)) {
            return ['success' => false, 'error' => 'Security token mismatch. Please refresh and retry.'];
        }
        return null;
    }

    public function getProfile() {
        if ($auth = $this->requireAuth()) return $auth;
        $userId = (int)$this->session->getUserId();
        $user = $this->profile->getByUserId($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Profile not found'];
        }
        return ['success' => true, 'data' => $user];
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }
        if ($auth = $this->requireAuth()) return $auth;
        if ($csrf = $this->verifyCsrf()) return $csrf;

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');

        if ($firstName === '' || $lastName === '') {
            return ['success' => false, 'error' => 'First name and last name are required'];
        }

        if (strlen($firstName) > 20 || strlen($lastName) > 20) {
            return ['success' => false, 'error' => 'First name and last name must be at most 20 characters'];
        }

        if (!preg_match('/^[a-zA-Z]+$/', $firstName) || !preg_match('/^[a-zA-Z]+$/', $lastName)) {
            return ['success' => false, 'error' => 'First name and last name must contain only letters (no spaces, numbers or special characters)'];
        }

        if ($dob !== '') {
            if (strtotime($dob) > time()) {
                return ['success' => false, 'error' => 'Date of birth cannot be in the future'];
            }
        }

        $ok = $this->profile->updateProfile(
            (int)$this->session->getUserId(),
            $firstName,
            $lastName,
            $dob !== '' ? $dob : null,
            $phone !== '' ? $phone : null
        );

        if ($ok) {
            $this->session->set('user_name', $firstName);
        }

        return $ok
            ? ['success' => true, 'message' => 'Profile updated successfully']
            : ['success' => false, 'error' => 'Unable to update profile'];
    }

    public function uploadImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }
        if ($auth = $this->requireAuth()) return $auth;
        if ($csrf = $this->verifyCsrf()) return $csrf;

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $dob = trim($_POST['dob'] ?? '');

        if ($firstName !== '' || $lastName !== '') {
            if (strlen($firstName) > 20 || strlen($lastName) > 20) {
                return ['success' => false, 'error' => 'First name and last name must be at most 20 characters'];
            }
            if (!preg_match('/^[a-zA-Z]+$/', $firstName) || !preg_match('/^[a-zA-Z]+$/', $lastName)) {
                return ['success' => false, 'error' => 'First name and last name must contain only letters (no spaces, numbers or special characters)'];
            }
        }

        if ($dob !== '') {
            if (strtotime($dob) > time()) {
                return ['success' => false, 'error' => 'Date of birth cannot be in the future'];
            }
        }

        if (!isset($_FILES['profile_image']) || (int)($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Please choose an image file'];
        }

        $file = $_FILES['profile_image'];
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > MAX_UPLOAD_SIZE) {
            return ['success' => false, 'error' => 'Image must be less than 5MB'];
        }

        $tmpPath = (string)($file['tmp_name'] ?? '');
        $mimeType = (string)(@mime_content_type($tmpPath) ?: '');
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
            return ['success' => false, 'error' => 'Only JPG, PNG, GIF, WEBP, HEIC, and HEIF are allowed'];
        }

        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if ($ext === '') {
            $ext = 'jpg';
        }

        $userId = (int)$this->session->getUserId();
        $uploadDir = UPLOAD_DIR . '/profiles';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'error' => 'Upload directory could not be created'];
        }
        if (!is_writable($uploadDir)) {
            return ['success' => false, 'error' => 'Upload directory is not writable'];
        }

        $fileName = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $destination = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['success' => false, 'error' => 'Failed to upload image'];
        }

        $firstNameVal = trim((string)($_POST['first_name'] ?? ($this->session->getUserData()['first_name'] ?? '')));
        $lastNameVal = trim((string)($_POST['last_name'] ?? ($this->session->getUserData()['last_name'] ?? '')));

        $ok = $this->profile->updateProfile(
            $userId,
            $firstNameVal,
            $lastNameVal,
            trim((string)($_POST['dob'] ?? ($this->session->getUserData()['dob'] ?? ''))) ?: null,
            trim((string)($_POST['phone_number'] ?? ($this->session->getUserData()['phone_number'] ?? ''))) ?: null,
            $fileName
        );

        if (!$ok) {
            @unlink($destination);
            return ['success' => false, 'error' => 'Image uploaded but profile update failed'];
        }

        if ($firstNameVal !== '') {
            $this->session->set('user_name', $firstNameVal);
        }

        return [
            'success' => true,
            'message' => 'Profile image updated successfully',
            'image_url' => APP_URL . '/public/uploads/profiles/' . rawurlencode($fileName)
        ];
    }

    public function removeImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }
        if ($auth = $this->requireAuth()) return $auth;
        if ($csrf = $this->verifyCsrf()) return $csrf;

        $userId = (int)$this->session->getUserId();
        $user = $this->profile->getByUserId($userId);
        if ($user && !empty($user['profile_image'])) {
            $filePath = UPLOAD_DIR . '/profiles/' . basename($user['profile_image']);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $ok = $this->profile->removeProfileImage($userId);
        return $ok
            ? ['success' => true, 'message' => 'Profile image removed successfully']
            : ['success' => false, 'error' => 'Unable to remove profile image'];
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }
        if ($auth = $this->requireAuth()) return $auth;
        if ($csrf = $this->verifyCsrf()) return $csrf;

        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($current === '' || $new === '' || $confirm === '') {
            return ['success' => false, 'error' => 'Please fill all password fields'];
        }
        if (strlen($new) < 6) {
            return ['success' => false, 'error' => 'New password must be at least 6 characters'];
        }
        if ($new !== $confirm) {
            return ['success' => false, 'error' => 'New passwords do not match'];
        }

        $ok = $this->profile->changePassword((int)$this->session->getUserId(), $current, $new);
        return $ok
            ? ['success' => true, 'message' => 'Password updated successfully']
            : ['success' => false, 'error' => 'Current password is incorrect'];
    }

    public function deleteAccount() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }
        if ($auth = $this->requireAuth()) return $auth;
        if ($csrf = $this->verifyCsrf()) return $csrf;

        $confirmDelete = trim((string)($_POST['confirm_delete'] ?? ''));
        if ($confirmDelete !== 'DELETE') {
            return ['success' => false, 'error' => 'Type DELETE to confirm account deletion'];
        }

        $ok = $this->profile->deleteAccount((int)$this->session->getUserId());
        if (!$ok) {
            return ['success' => false, 'error' => 'Unable to delete account at this time'];
        }

        $this->session->logout();
        return ['success' => true, 'message' => 'Account deleted', 'redirect' => APP_ROUTE . '?page=home'];
    }
}

try {
    $controller = new ProfileController();
    $action = $_GET['action'] ?? 'get-profile';

    switch ($action) {
        case 'get-profile':
            $response = $controller->getProfile();
            break;
        case 'update-profile':
            $response = $controller->updateProfile();
            break;
        case 'upload-image':
            $response = $controller->uploadImage();
            break;
        case 'remove-image':
            $response = $controller->removeImage();
            break;
        case 'change-password':
            $response = $controller->changePassword();
            break;
        case 'delete-account':
            $response = $controller->deleteAccount();
            break;
        default:
            $response = ['success' => false, 'error' => 'Invalid action'];
            break;
    }

    ob_clean();
    echo json_encode($response);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
