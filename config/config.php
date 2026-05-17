<?php
/**
 * Application Configuration
 * Loads environment variables from .env file
 */

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');

// Helper to get env with default
function env($key, $default = null) {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

// App Settings
define('APP_NAME', env('APP_NAME', 'Paper Library'));
define('APP_URL', env('APP_URL', 'http://localhost/library_management_system'));
define('APP_ROUTE', APP_URL . '/index.php');
define('APP_ENV', env('APP_ENV', 'development'));

// Database
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'final'));
define('DB_PORT', env('DB_PORT', 3306));

// Mail
define('MAIL_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', env('MAIL_PORT', 587));
define('MAIL_USERNAME', env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', ''));
define('MAIL_FROM', env('MAIL_FROM', MAIL_USERNAME));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', APP_NAME));
define('SMTP_CONNECT_TIMEOUT', 4);
define('SMTP_READ_TIMEOUT', 6);

// Keys
define('GOOGLE_BOOKS_API_KEY', env('GOOGLE_BOOKS_API_KEY', ''));
define('OPENAI_API_KEY', env('OPENAI_API_KEY', ''));
define('OPENAI_ENDPOINT', 'https://api.openai.com/v1/responses');
define('OPENAI_MODEL', 'gpt-5.4-mini');

// Stripe
define('STRIPE_PUBLISHABLE_KEY', env('STRIPE_PUBLISHABLE_KEY', ''));
define('STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY', ''));
define('STRIPE_CURRENCY', env('STRIPE_CURRENCY', 'usd'));

// Constants
define('OTP_VALIDITY', 300);
define('OTP_LENGTH', 6);
define('SESSION_TIMEOUT', 1800);
define('SESSION_COOKIE_SECURE', true);
define('SESSION_COOKIE_HTTPONLY', true);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 12]);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif', 'image/pjpeg', 'image/x-png']);
define('ALLOWED_PDF_TYPES', ['application/pdf', 'application/epub+zip', 'application/octet-stream', 'application/zip']);
define('ALLOWED_EPUB_TYPES', ['application/epub+zip']);
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
define('ITEMS_PER_PAGE', 12);
define('BASE_URL', APP_URL . '/index.php');

error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'development' ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
