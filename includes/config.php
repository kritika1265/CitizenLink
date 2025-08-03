<?php
// includes/config.php
session_start();

// Define global config constant
define('CITIZENLINK_CONFIG', true);

// Load environment-specific configuration
$environment = $_ENV['APP_ENV'] ?? 'development';

if ($environment === 'production') {
    require_once __DIR__ . '/config/production.php';
} else {
    require_once __DIR__ . '/config/development.php';
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Site configuration
define('SITE_NAME', 'CitizenLink');
define('SITE_URL', 'http://localhost/citizenlink');
define('SITE_EMAIL', 'support@citizenlink.gov');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'citizenlink_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// File upload configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.use_strict_mode', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// User authentication check
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Admin authentication check
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect functions
function redirectTo($url) {
    header("Location: " . SITE_URL . "/" . ltrim($url, '/'));
    exit();
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirectTo('pages/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirectTo('pages/login.php');
    }
}

// Application status constants
define('STATUS_PENDING', 'pending');
define('STATUS_PROCESSING', 'processing');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');
define('STATUS_COMPLETED', 'completed');

// Service types
define('SERVICE_BIRTH_CERTIFICATE', 'birth_certificate');
define('SERVICE_DEATH_CERTIFICATE', 'death_certificate');
define('SERVICE_MARRIAGE_CERTIFICATE', 'marriage_certificate');
define('SERVICE_BUSINESS_LICENSE', 'business_license');
define('SERVICE_PROPERTY_TAX', 'property_tax');
define('SERVICE_WATER_CONNECTION', 'water_connection');

// Payment status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');
?>
