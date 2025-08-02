<?php
// includes/functions.php

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Indian format)
 */
function validatePhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

/**
 * Validate Aadhaar number
 */
function validateAadhaar($aadhaar) {
    return preg_match('/^\d{12}$/', $aadhaar);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate application reference number
 */
function generateReferenceNumber($serviceType) {
    $prefix = strtoupper(substr($serviceType, 0, 3));
    $timestamp = date('YmdHis');
    $random = sprintf('%04d', mt_rand(1000, 9999));
    return $prefix . $timestamp . $random;
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $action, 
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Send email notification
 */
function sendEmail($to, $subject, $message, $headers = '') {
    $defaultHeaders = "From: " . SITE_EMAIL . "\r\n";
    $defaultHeaders .= "Reply-To: " . SITE_EMAIL . "\r\n";
    $defaultHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $finalHeaders = $defaultHeaders . $headers;
    
    return mail($to, $subject, $message, $finalHeaders);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Get user details
 */
function getUserDetails($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Failed to get user details: " . $e->getMessage());
        return false;
    }
}

/**
 * Get application status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        STATUS_PENDING => '<span class="badge badge-warning">Pending</span>',
        STATUS_PROCESSING => '<span class="badge badge-info">Processing</span>',
        STATUS_APPROVED => '<span class="badge badge-success">Approved</span>',
        STATUS_REJECTED => '<span class="badge badge-danger">Rejected</span>',
        STATUS_COMPLETED => '<span class="badge badge-success">Completed</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

/**
 * Upload file
 */
function uploadFile($file, $allowedTypes = null, $maxSize = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed'];
    }
    
    $allowedTypes = $allowedTypes ?: ALLOWED_FILE_TYPES;
    $maxSize = $maxSize ?: MAX_FILE_SIZE;
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large'];
    }
    
    // Validate file type
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $fileExt;
    $filepath = UPLOAD_PATH . $filename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'original_name' => $file['name']
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

/**
 * Get service fee
 */
function getServiceFee($serviceType) {
    $fees = [
        SERVICE_BIRTH_CERTIFICATE => 50,
        SERVICE_DEATH_CERTIFICATE => 50,
        SERVICE_MARRIAGE_CERTIFICATE => 100,
        SERVICE_BUSINESS_LICENSE => 500,
        SERVICE_PROPERTY_TAX => 0, // Variable amount
        SERVICE_WATER_CONNECTION => 1000
    ];
    
    return $fees[$serviceType] ?? 0;
}

/**
 * Paginate results
 */
function paginate($totalRecords, $recordsPerPage, $currentPage) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $offset = ($currentPage - 1) * $recordsPerPage;
    
    return [
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'limit' => $recordsPerPage,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Flash messages
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Generate OTP
 */
function generateOTP($length = 6) {
    return sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
}

/**
 * Send SMS (Mock implementation)
 */
function sendSMS($phone, $message) {
    // Mock implementation - integrate with SMS gateway
    error_log("SMS to {$phone}: {$message}");
    return true;
}
?>