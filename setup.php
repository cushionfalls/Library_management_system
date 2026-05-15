<?php
/**
 * Paper Library - Comprehensive Setup Script
 * This script initializes the database, creates tables, and seeds initial data.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

echo "<h1>LMS Setup Utility</h1>";
echo "<p>Initializing system...</p>";

// 1. Connect to MySQL without database selection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color:green'>Successfully connected to MySQL server.</p>";

// 2. Create Database if not exists
$dbName = DB_NAME;
if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbName`")) {
    die("<p style='color:red'>Error creating database: " . $conn->error . "</p>");
}

echo "<p style='color:green'>Database `$dbName` is ready.</p>";

// 3. Select the database
$conn->select_db($dbName);

// 4. Create Tables
$tables = [
    "Users" => "CREATE TABLE IF NOT EXISTS `Users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `first_name` varchar(100) NOT NULL,
        `last_name` varchar(100) NOT NULL,
        `email` varchar(255) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `role` enum('ADMIN','LIBRARIAN','USER') NOT NULL DEFAULT 'USER',
        `dob` datetime DEFAULT NULL,
        `phone_number` varchar(15) DEFAULT NULL,
        `profile_image` varchar(500) DEFAULT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `is_verified` tinyint(1) NOT NULL DEFAULT 0,
        `verified_at` datetime DEFAULT NULL,
        `wallet` int(11) NOT NULL DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "Books" => "CREATE TABLE IF NOT EXISTS `Books` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `isbn` varchar(20) NOT NULL UNIQUE,
        `name` varchar(255) NOT NULL,
        `description` text DEFAULT NULL,
        `publisher` varchar(255) DEFAULT NULL,
        `published_at` datetime DEFAULT NULL,
        `language` varchar(50) DEFAULT 'English',
        `genre` enum('FANTASY','SCIENCE_FICTION','MYSTERY','ROMANCE','THRILLER','NON_FICTION','BIOGRAPHY','HISTORY','OTHERS') DEFAULT 'OTHERS',
        `number_of_copies` int(11) NOT NULL DEFAULT 1,
        `price` int(11) NOT NULL DEFAULT 0,
        `online_rent_price` int(11) DEFAULT 0,
        `online_buy_price` int(11) DEFAULT 0,
        `cover_image` varchar(500) DEFAULT NULL,
        `online_copy_pdf` varchar(500) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "Authors" => "CREATE TABLE IF NOT EXISTS `Authors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `first_name` varchar(100) NOT NULL,
        `last_name` varchar(100) NOT NULL,
        `bio` text DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "BookAuthors" => "CREATE TABLE IF NOT EXISTS `BookAuthors` (
        `book_id` int(11) NOT NULL,
        `author_id` int(11) NOT NULL,
        PRIMARY KEY (`book_id`,`author_id`),
        KEY `author_id` (`author_id`),
        CONSTRAINT `bookauthors_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`) ON DELETE CASCADE,
        CONSTRAINT `bookauthors_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `Authors` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "BookTransactions" => "CREATE TABLE IF NOT EXISTS `BookTransactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `book_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `transaction_type` enum('RENT','INHAND','ONLINE') NOT NULL,
        `amount_paid` int(11) NOT NULL DEFAULT 0,
        `due_date` datetime DEFAULT NULL,
        `returned_at` datetime DEFAULT NULL,
        `is_returned` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `book_id` (`book_id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `booktransactions_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`) ON DELETE CASCADE,
        CONSTRAINT `booktransactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "WalletTransactions" => "CREATE TABLE IF NOT EXISTS `WalletTransactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `amount` int(11) NOT NULL,
        `type` enum('CREDIT','DEBIT') NOT NULL,
        `reason` enum('TOP_UP','BOOK_RENT','BOOK_BUY','FINE_PAYMENT','REFUND','MEMBERSHIP') NOT NULL,
        `external_ref` varchar(255) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `wallettransactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "Sessions" => "CREATE TABLE IF NOT EXISTS `Sessions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `session_id` varchar(255) NOT NULL,
        `device_info` varchar(255) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `valid_till` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "OTP" => "CREATE TABLE IF NOT EXISTS `OTP` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` varchar(255) NOT NULL,
        `otp` varchar(10) NOT NULL,
        `expires_at` datetime NOT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "BookReviews" => "CREATE TABLE IF NOT EXISTS `BookReviews` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `book_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `rating` int(11) NOT NULL,
        `review` text DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `book_id` (`book_id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `bookreviews_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`) ON DELETE CASCADE,
        CONSTRAINT `bookreviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "MembershipPlans" => "CREATE TABLE IF NOT EXISTS `MembershipPlans` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `slug` varchar(50) NOT NULL UNIQUE,
        `name` varchar(100) NOT NULL,
        `duration_days` int(11) NOT NULL DEFAULT 30,
        `price` int(11) NOT NULL DEFAULT 0,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "UserMemberships" => "CREATE TABLE IF NOT EXISTS `UserMemberships` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `plan_id` int(11) NOT NULL,
        `status` enum('ACTIVE','EXPIRED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
        `starts_at` datetime NOT NULL,
        `ends_at` datetime NOT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `plan_id` (`plan_id`),
        CONSTRAINT `usermemberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `usermemberships_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `MembershipPlans` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "MembershipPurchases" => "CREATE TABLE IF NOT EXISTS `MembershipPurchases` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `membership_id` int(11) NOT NULL,
        `plan_id` int(11) NOT NULL,
        `amount` int(11) NOT NULL DEFAULT 0,
        `wallet_transaction_id` int(11) DEFAULT NULL,
        `purchased_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `membership_id` (`membership_id`),
        KEY `plan_id` (`plan_id`),
        CONSTRAINT `membershippurchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `membershippurchases_ibfk_2` FOREIGN KEY (`membership_id`) REFERENCES `UserMemberships` (`id`) ON DELETE CASCADE,
        CONSTRAINT `membershippurchases_ibfk_3` FOREIGN KEY (`plan_id`) REFERENCES `MembershipPlans` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "Wishlist" => "CREATE TABLE IF NOT EXISTS `Wishlist` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `book_id` int(11) NOT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_user_book` (`user_id`,`book_id`),
        KEY `user_id` (`user_id`),
        KEY `book_id` (`book_id`),
        CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "UserBookAccess" => "CREATE TABLE IF NOT EXISTS `UserBookAccess` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `book_id` int(11) NOT NULL,
        `access_type` ENUM('OWNED', 'MEMBERSHIP') NOT NULL,
        `source_ref` int(11) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_user_book_access` (`user_id`,`book_id`),
        KEY `user_id` (`user_id`),
        KEY `book_id` (`book_id`),
        CONSTRAINT `userbookaccess_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `userbookaccess_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "UserBookProgress" => "CREATE TABLE IF NOT EXISTS `UserBookProgress` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `book_id` int(11) NOT NULL,
        `progress_percent` int(11) NOT NULL DEFAULT 0,
        `current_location` text,
        `last_opened_at` datetime DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_user_book_progress` (`user_id`,`book_id`),
        KEY `user_id` (`user_id`),
        KEY `book_id` (`book_id`),
        CONSTRAINT `userbookprogress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `userbookprogress_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

echo "<div style='background: #fdf8ff; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: \"Manrope\", sans-serif; padding: 20px;'>";
echo "<div style='background: white; padding: 50px; border-radius: 32px; box-shadow: 0 25px 80px -12px rgba(56, 0, 191, 0.12); max-width: 650px; width: 100%; border: 1px solid rgba(56, 0, 191, 0.08);'>";
echo "<div style='text-align: center; margin-bottom: 40px;'>";
echo "<div style='width: 80px; height: 80px; background: #3800bf; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 12px 30px rgba(56, 0, 191, 0.3);'>";
echo "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"40\" height=\"40\" viewBox=\"0 0 24 24\" fill=\"white\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\"/></svg>";
echo "</div>";
echo "<h1 style='color: #1c1a25; font-size: 32px; font-weight: 800; margin: 0; letter-spacing: -0.02em;'>Lumina Setup Utility</h1>";
echo "<p style='color: #474557; margin: 12px 0 0; font-size: 16px;'>Initializing your premium library experience</p>";
echo "</div>";

echo "<div style='background: #f8f7ff; padding: 32px; border-radius: 24px; margin-bottom: 40px; border: 1px solid rgba(56, 0, 191, 0.05);'>";
echo "<h3 style='margin: 0 0 16px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.1em; color: #5a30fb; font-weight: 800;'>Schema Initialization</h3>";
echo "<div style='display: grid; grid-template-cols: 1fr 1fr; gap: 8px;'>";
foreach ($tables as $name => $sql) {
    if ($conn->query($sql)) {
        echo "<div style='color: #2e7d32; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;'><span style='font-size: 10px;'>●</span> $name</div>";
    } else {
        echo "<div style='color: #d32f2f; font-size: 13px; font-weight: 600;'>✗ $name: error</div>";
    }
}
echo "</div>";
echo "</div>";

// Migrations & Seeding
$checkPlans = $conn->query("SELECT COUNT(*) AS cnt FROM `MembershipPlans` ");
$planCount = $checkPlans ? (int)$checkPlans->fetch_assoc()['cnt'] : 0;
if ($planCount === 0) {
    $defaultPlans = [
        "('basic',   'Basic',    30,  500, 1)",
        "('standard','Standard', 90,  1200, 1)",
        "('premium', 'Premium',  365, 3500, 1)",
    ];
    $conn->query("INSERT INTO `MembershipPlans` (slug, name, duration_days, price, is_active) VALUES " . implode(',', $defaultPlans));
}

$adminEmail = 'admin@lms.com';
$checkAdmin = $conn->query("SELECT id FROM Users WHERE email = '$adminEmail'");
if ($checkAdmin->num_rows === 0) {
    $pass = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
    $sql = "INSERT INTO Users (first_name, last_name, email, password, role, is_active, is_verified, verified_at) 
            VALUES ('System', 'Admin', '" . $conn->real_escape_string($adminEmail) . "', '" . $conn->real_escape_string($pass) . "', 'ADMIN', 1, 1, NOW())";
    $conn->query($sql);
}

echo "<div style='text-align: center;'>";
echo "<h2 style='color: #2e7d32; font-size: 24px; font-weight: 800; margin: 0 0 12px;'>Setup Successful!</h2>";
echo "<p style='color: #474557; font-size: 15px; margin-bottom: 32px; line-height: 1.6;'>Database tables and core features (Wishlist, Digital Access, Reviews) have been successfully initialized.</p>";
echo "<a href='index.php' style='display: inline-block; background: #3800bf; color: white; padding: 16px 48px; border-radius: 16px; text-decoration: none; font-weight: 700; font-size: 16px; transition: all 0.2s; box-shadow: 0 10px 25px rgba(56, 0, 191, 0.2);'>Go to Homepage</a>";
echo "</div>";

echo "</div>";
echo "</div>";

$conn->close();
exit;
