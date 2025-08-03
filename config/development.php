<?php
/**
 * Development Environment Configuration
 * CitizenLink Application
 * 
 * This file contains development-specific settings
 * DO NOT use these settings in production!
 */

// Prevent direct access
if (!defined('CITIZENLINK_CONFIG')) {
    die('Direct access not permitted');
}

// Environment
define('ENVIRONMENT', 'development');
define('DEBUG_MODE', true);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'citizenlink_dev');
define('DB_USER', 'dev_user');
define('DB_PASS', 'dev_password');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// Application URLs
define('BASE_URL', 'http://localhost/citizenlink/');
define('API_BASE_URL', BASE_URL . 'api/');
define('ADMIN_URL', BASE_URL . 'admin/');

// File Paths
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('LOG_PATH', ROOT_PATH . 'logs/');
define('CACHE_PATH', ROOT_PATH . 'cache/');

// Security Settings
define('SECRET_KEY', 'dev-secret-key-change-in-production');
define('JWT_SECRET', 'dev-jwt-secret-change-in-production');
define('ENCRYPTION_KEY', 'dev-encryption-key-32-chars-long');
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 6); // Relaxed for development

// File Upload Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Email Configuration (Development - using local SMTP or mail catcher)
define('MAIL_HOST', 'localhost');
define('MAIL_PORT', 1025); // MailHog or similar
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', '');
define('MAIL_FROM_ADDRESS', 'noreply@citizenlink.dev');
define('MAIL_FROM_NAME', 'CitizenLink Development');

// Payment Gateway (Development/Sandbox)
define('PAYMENT_GATEWAY', 'stripe_sandbox');
define('STRIPE_PUBLIC_KEY', 'pk_test_your_stripe_public_key_here');
define('STRIPE_SECRET_KEY', 'sk_test_your_stripe_secret_key_here');
define('PAYMENT_CURRENCY', 'USD');

// API Keys (Development)
define('RECAPTCHA_SITE_KEY', 'dev_recaptcha_site_key');
define('RECAPTCHA_SECRET_KEY', 'dev_recaptcha_secret_key');

// Logging Configuration
define('LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE', 'citizenlink_dev.log');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_ROTATION', true);

// Cache Configuration
define('CACHE_ENABLED', false); // Disabled for development
define('CACHE_DRIVER', 'file'); // file, redis, memcached
define('CACHE_TTL', 300); // 5 minutes

// Session Configuration
define('SESSION_NAME', 'CITIZENLINK_DEV_SESSION');
define('SESSION_SECURE', false); // HTTP allowed in development
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// Rate Limiting (Relaxed for development)
define('RATE_LIMIT_ENABLED', false);
define('RATE_LIMIT_REQUESTS', 1000);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Development Tools
define('SHOW_ERRORS', true);
define('DISPLAY_STARTUP_ERRORS', true);
define('ERROR_REPORTING_LEVEL', E_ALL);
define('SQL_DEBUG', true);
define('PROFILING_ENABLED', true);

// External Services URLs (Development/Staging)
define('GOVERNMENT_API_URL', 'https://api-staging.government.gov/');
define('GOVERNMENT_API_KEY', 'dev_api_key_here');
define('DOCUMENT_VERIFICATION_URL', 'https://verify-staging.gov/api/');

// Feature Flags
define('FEATURE_DOCUMENT_UPLOAD', true);
define('FEATURE_ONLINE_PAYMENT', true);
define('FEATURE_SMS_NOTIFICATIONS', false); // Disabled in dev
define('FEATURE_EMAIL_VERIFICATION', true);
define('FEATURE_TWO_FACTOR_AUTH', false); // Disabled in dev

// Development Database Seeding
define('DB_SEED_DATA', true);
define('ADMIN_DEFAULT_EMAIL', 'admin@citizenlink.dev');
define('ADMIN_DEFAULT_PASSWORD', 'admin123'); // Change this!

// Error Handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set development timezone
date_default_timezone_set('America/New_York');

// Development Headers (CORS for local development)
if (isset($_SERVER['REQUEST_METHOD'])) {
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
}

?>