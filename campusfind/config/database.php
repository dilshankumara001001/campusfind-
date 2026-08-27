<?php
// ============================================================
// Error Reporting ON
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'campusfind');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'CampusFind');
define('SITE_URL', 'http://localhost/campusfind/');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/campusfind/uploads/');
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// ============================================================
// Session Settings
// ============================================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
date_default_timezone_set('Asia/Colombo');

// ============================================================
// Database Connection
// ============================================================
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("❌ Database Connection Failed: " . $e->getMessage());
}

// ============================================================
// Start Session
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


?>