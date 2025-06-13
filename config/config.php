<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'slitpa_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_CHARSET', 'utf8mb4');

// Add password pepper for additional security
define('PASSWORD_PEPPER', 'slitpa2024#');

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

// Site URLs - Updated for local XAMPP environment
define('BASE_PATH', '/slitpa-web');
define('SITE_URL', 'http://localhost' . BASE_PATH);
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

try {
    // Create PDO instance
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

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Select the database
    $pdo->exec("USE " . DB_NAME);
    
    // Create users table with improved structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        user_type ENUM('admin', 'member', 'partner') NOT NULL,
        status ENUM('active', 'pending', 'inactive') NOT NULL DEFAULT 'pending',
        reset_token VARCHAR(255) DEFAULT NULL,
        reset_token_expiry DATETIME DEFAULT NULL,
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE INDEX idx_email (email),
        UNIQUE INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create members table with improved structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        country VARCHAR(100),
        position VARCHAR(255),
        passport_number VARCHAR(50),
        membership_status ENUM('active', 'expired', 'pending') NOT NULL DEFAULT 'pending',
        member_category ENUM('resident', 'non_resident') NOT NULL,
        profile_image VARCHAR(255),
        linkedin VARCHAR(255),
        visa_status VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create partners table with improved structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS partners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        contact_person VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        website VARCHAR(255),
        partner_type ENUM('gold', 'silver', 'platinum') NOT NULL,
        status ENUM('active', 'pending', 'inactive') NOT NULL DEFAULT 'pending',
        logo_path VARCHAR(255),
        partnership_start_date DATE,
        partnership_end_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE INDEX idx_company_name (company_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create events table with improved structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        short_description VARCHAR(500),
        location VARCHAR(255) NOT NULL,
        event_type ENUM('webinar', 'conference', 'workshop', 'networking', 'other') NOT NULL,
        event_date DATE NOT NULL,
        end_date DATE,
        start_time TIME,
        end_time TIME,
        max_participants INT,
        registration_deadline DATE,
        is_featured BOOLEAN DEFAULT FALSE,
        status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE INDEX idx_slug (slug),
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create event_media table with improved structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS event_media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        media_type ENUM('image', 'video', 'document') NOT NULL,
        title VARCHAR(255),
        description TEXT,
        is_featured BOOLEAN DEFAULT FALSE,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create event_registrations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS event_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('registered', 'attended', 'cancelled', 'waitlisted') NOT NULL DEFAULT 'registered',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_registration (event_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create news table with similar structure to events
    $pdo->exec("CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        image VARCHAR(255),
        video VARCHAR(255),
        published_date DATE NOT NULL,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Insert default admin user if not exists
    $admin_email = 'admin@slitpa.org';
    $admin_password = password_hash('admin123' . PASSWORD_PEPPER, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, user_type, status) VALUES (?, ?, ?, 'admin', 'active')");
        $stmt->execute([$admin_email, $admin_password, $admin_email]);
    }

} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

// Function to hash password with pepper
function hash_password($password) {
    return password_hash($password . PASSWORD_PEPPER, PASSWORD_DEFAULT);
}

// Function to verify password with pepper
function verify_password($password, $hash) {
    return password_verify($password . PASSWORD_PEPPER, $hash);
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