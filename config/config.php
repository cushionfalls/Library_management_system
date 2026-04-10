<?php
// Application Configuration

define('APP_NAME', 'Library Management System');
define('APP_URL', 'http://localhost/Library_management_system');
/** Single front controller at project root (not public/index.php in the URL). */
define('APP_ROUTE', APP_URL . '/index.php');
define('APP_ENV', 'development');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'final');
define('DB_PORT', 3306);

// Google Email Configuration (using App Password)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'librarymanagementslibs@gmail.com'); 
define('MAIL_PASSWORD', 'jnef oloi tlcl dxrx');     
define('MAIL_FROM', MAIL_USERNAME);
define('MAIL_FROM_NAME', 'Library Management System');

// OTP Configuration
define('OTP_VALIDITY', 300); // 5 minutes in seconds
define('OTP_LENGTH', 6);

// Google Books API (for autofilling book details in admin) — keep server-side only; restrict key in Google Cloud Console
define('GOOGLE_BOOKS_API_KEY', 'AIzaSyA7O2B6DT-4k4_qyqBfEN_fX6Q9lE30ulk');

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('SESSION_COOKIE_SECURE', false); // Set to true in production with HTTPS
define('SESSION_COOKIE_HTTPONLY', true);

// Security
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 12]);

// File Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif', 'image/pjpeg', 'image/x-png']);
define('ALLOWED_PDF_TYPES', ['application/pdf']);
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Fine Configuration
define('FINE_PER_DAY', 5); // Amount per day
define('MAX_FINE', 100);   // Maximum fine amount

error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'development' ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
?>

