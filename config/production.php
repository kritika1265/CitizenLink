<?php
/**
 * Production Environment Configuration
 * CitizenLink Application
 * 
 * This file contains production-specific settings
 * SECURITY CRITICAL: Ensure all credentials are properly secured!
 */

// Prevent direct access
if (!defined('CITIZENLINK_CONFIG')) {
    die('Direct access not permitted');
}

// Environment
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'your-production-db-host');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'citizenlink_prod');
define('DB_USER', $_ENV['DB_USER'] ?? 'prod_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'your-secure-db-password');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);

// Application URLs
define('BASE_URL', 'https://www.citizenlink.gov/');
define('API_BASE_URL', BASE_URL . 'api/');
define('ADMIN_URL', BASE_URL . 'admin/');

// File Paths
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('LOG_PATH', ROOT_PATH . 'logs/');
define('CACHE_PATH', ROOT_PATH . 'cache/');

// Security Settings (Use environment variables in production)
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'your-production-secret-key-here');
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-production-jwt-secret-here');
define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY'] ?? 'your-32-character-encryption-key-here');
define('SESSION_LIFETIME', 1800); // 30 minutes
define('PASSWORD_MIN_LENGTH', 12); // Strong passwords required

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB in production
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Email Configuration (Production SMTP)
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? 'noreply@citizenlink.gov');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? 'your-email-password');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', 'noreply@citizenlink.gov');
define('MAIL_FROM_NAME', 'CitizenLink Portal');

// Payment Gateway (Production)
define('PAYMENT_GATEWAY', 'stripe_live');
define('STRIPE_PUBLIC_KEY', $_ENV['STRIPE_PUBLIC_KEY'] ?? 'pk_live_your_stripe_public_key');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? 'sk_live_your_stripe_secret_key');
define('PAYMENT_CURRENCY', 'USD');

// API Keys (Production)
define('RECAPTCHA_SITE_KEY', $_ENV['RECAPTCHA_SITE_KEY'] ?? 'your_recaptcha_site_key');
define('RECAPTCHA_SECRET_KEY', $_ENV['RECAPTCHA_SECRET_KEY'] ?? 'your_recaptcha_secret_key');

// Logging Configuration
define('LOG_LEVEL', 'ERROR'); // Only log errors in production
define('LOG_FILE', 'citizenlink_prod.log');
define('LOG_MAX_SIZE', 50 * 1024 * 1024); // 50MB
define('LOG_ROTATION', true);

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_DRIVER', $_ENV['CACHE_DRIVER'] ?? 'redis'); // redis preferred for production
define('CACHE_TTL', 3600); // 1 hour
define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? 'localhost');
define('REDIS_PORT', $_ENV['REDIS_PORT'] ?? 6379);
define('REDIS_PASSWORD', $_ENV['REDIS_PASSWORD'] ?? '');

// Session Configuration
define('SESSION_NAME', 'CITIZENLINK_SESSION');
define('SESSION_SECURE', true); // HTTPS only
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict');
define('SESSION_DOMAIN', '.citizenlink.gov');

// Rate Limiting (Strict in production)
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Production Security
define('SHOW_ERRORS', false);
define('DISPLAY_STARTUP_ERRORS', false);
define('ERROR_REPORTING_LEVEL', E_ERROR | E_WARNING | E_PARSE);
define('SQL_DEBUG', false);
define('PROFILING_ENABLED', false);

// External Services URLs (Production)
define('GOVERNMENT_API_URL', $_ENV['GOVERNMENT_API_URL'] ?? 'https://api.government.gov/');
define('GOVERNMENT_API_KEY', $_ENV['GOVERNMENT_API_KEY'] ?? 'your_production_api_key');
define('DOCUMENT_VERIFICATION_URL', $_ENV['DOCUMENT_VERIFICATION_URL'] ?? 'https://verify.gov/api/');

// Feature Flags
define('FEATURE_DOCUMENT_UPLOAD', true);
define('FEATURE_ONLINE_PAYMENT', true);
define('FEATURE_SMS_NOTIFICATIONS', true);
define('FEATURE_EMAIL_VERIFICATION', true);
define('FEATURE_TWO_FACTOR_AUTH', true);

// SSL and Security Headers
define('FORCE_HTTPS', true);
define('HSTS_MAX_AGE', 31536000); // 1 year
define('CSP_ENABLED', true);

// Backup Configuration
define('BACKUP_ENABLED', true);
define('BACKUP_SCHEDULE', '0 2 * * *'); // Daily at 2 AM
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_STORAGE', $_ENV['BACKUP_STORAGE'] ?? 'aws_s3');

// Monitoring and Analytics
define('MONITORING_ENABLED', true);
define('ANALYTICS_ID', $_ENV['ANALYTICS_ID'] ?? 'GA-XXXXXXXXX');
define('ERROR_TRACKING_DSN', $_ENV['ERROR_TRACKING_DSN'] ?? '');

// API Rate Limiting per User
define('API_RATE_LIMIT_PER_USER', 1000);
define('API_RATE_LIMIT_WINDOW', 86400); // 24 hours

// Production Database Connection Pool
define('DB_POOL_MIN', 2);
define('DB_POOL_MAX', 20);
define('DB_TIMEOUT', 30);

// Error Handling (Production)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . 'php_errors.log');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Set production timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');

// Security Headers for Production
if (isset($_SERVER['REQUEST_METHOD'])) {
    // Force HTTPS
    if (FORCE_HTTPS && !isset($_SERVER['HTTPS'])) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
        exit();
    }
    
    // Security Headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Strict-Transport-Security: max-age=' . HSTS_MAX_AGE . '; includeSubDomains; preload');
    
    // Content Security Policy
    if (CSP_ENABLED) {
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://js.stripe.com https://www.google.com/recaptcha/; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' https://api.stripe.com; " .
               "frame-src https://js.stripe.com https://www.google.com/recaptcha/;";
        header('Content-Security-Policy: ' . $csp);
    }
    
    // Remove server information
    header_remove('X-Powered-By');
    header_remove('Server');
}

// Production-specific PHP settings
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.name', SESSION_NAME);

?>