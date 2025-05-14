<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change this to your database username
define('DB_PASSWORD', '');   // Change this to your database password
define('DB_NAME', 'slitpa_db');
define('DB_CHARSET', 'utf8mb4');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');  // Change this
define('SMTP_PASSWORD', 'your-app-password');     // Change this
define('SMTP_FROM', 'noreply@slitpa.org');
define('SMTP_FROM_NAME', 'SLITPA');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4']);

// Membership settings
define('MEMBERSHIP_DURATION_DAYS', 365);  // 1 year
define('RENEWAL_REMINDER_DAYS', 30);      // Send reminder 30 days before expiry

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Asia/Dubai');  // UAE timezone

// Site URLs
define('SITE_URL', 'http://localhost/slitpa-web');  // Change this to your domain
define('ADMIN_URL', SITE_URL . '/admin');
define('MEMBER_URL', SITE_URL . '/member');
define('PARTNER_URL', SITE_URL . '/partner');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('MEMBER_UPLOAD_DIR', UPLOAD_DIR . '/members');
define('PARTNER_UPLOAD_DIR', UPLOAD_DIR . '/partners');
define('EVENT_UPLOAD_DIR', UPLOAD_DIR . '/events');

// Create upload directories if they don't exist
$directories = [UPLOAD_DIR, MEMBER_UPLOAD_DIR, PARTNER_UPLOAD_DIR, EVENT_UPLOAD_DIR];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Create global PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Database connection function (for backward compatibility)
function get_db_connection() {
    global $pdo;
    return $pdo;
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate random string
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length));
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check user type
function get_user_type() {
    return $_SESSION['user_type'] ?? null;
}

// Function to redirect with message
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}