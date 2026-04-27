<?php
/**
 * Database Setup Script
 * Run this file once to set up the database with all tables
 */

require_once __DIR__ . '/config/config.php';

echo "=== Library Management System - Database Setup ===\n\n";

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "1. Creating database '" . DB_NAME . "'...\n";
$create_db = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`";
if ($conn->query($create_db) === TRUE) {
    echo "   ✓ Database created/exists\n";
} else {
    die("   ✗ Error creating database: " . $conn->error);
}

// Select database
$conn->select_db(DB_NAME);

echo "\n2. Reading SQL schema...\n";
$sql_file = __DIR__ . '/db.sql';
if (!file_exists($sql_file)) {
    die("   ✗ Error: db.sql file not found");
}

$sql_content = file_get_contents($sql_file);
echo "   ✓ Schema file loaded\n";

echo "\n3. Executing SQL statements...\n";
$statements = array_filter(array_map('trim', explode(';', $sql_content)), 'strlen');
$count = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;

    if ($conn->query($statement) === TRUE) {
        $count++;
    } else {
        echo "   ✗ Error: " . $conn->error . "\n";
    }
}

echo "   ✓ Executed " . $count . " SQL statements\n";

echo "\n4. Creating directories...\n";
$dirs = [
    __DIR__ . '/logs',
    __DIR__ . '/public/uploads',
    __DIR__ . '/public/uploads/images',
    __DIR__ . '/public/uploads/profiles',
    __DIR__ . '/public/uploads/pdfs'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "   ✓ Created: $dir\n";
        } else {
            echo "   ✗ Failed to create: $dir\n";
        }
    } else {
        echo "   ✓ Exists: $dir\n";
    }
}

echo "\n5. Creating default admin user...\n";
$admin_email = 'admin@librarymanagement.com';
$admin_password = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $conn->prepare("INSERT IGNORE INTO Users (first_name, last_name, email, password, role, is_verified, verified_at) VALUES (?, ?, ?, ?, 'ADMIN', 1, NOW())");
$first = 'System';
$last = 'Administrator';
$role = 'ADMIN';

$stmt->bind_param('ssss', $first, $last, $admin_email, $admin_password);
if ($stmt->execute()) {
    echo "   ✓ Admin user created\n";
    echo "   Email: $admin_email\n";
    echo "   Password: admin123\n";
    echo "   NOTE: Change this password after first login!\n";
} else {
    echo "   ✗ Error creating admin user: " . $stmt->error . "\n";
}

$conn->close();

echo "\n" . str_repeat("=", 45) . "\n";
echo "✓ Database setup completed successfully!\n\n";
echo "Next steps:\n";
echo "1. Update config/config.php with your email credentials\n";
echo "2. Login to http://localhost/library_management_system/index.php\n";
echo "3. Email: admin@librarymanagement.com\n";
echo "4. Password: admin123\n";
echo "5. Change password immediately after login\n";
echo "\n" . str_repeat("=", 45) . "\n";
?>

