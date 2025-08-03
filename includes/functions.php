<?php
// includes/functions.php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

// ============================================================================
// INPUT VALIDATION & SANITIZATION FUNCTIONS
// ============================================================================

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}
function validateAadhaar($aadhaar) {
    return preg_match('/^\d{12}$/', $aadhaar);
}

// ============================================================================
// SECURITY FUNCTIONS
// ============================================================================

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /pages/login.php');
        exit();
    }
}

function getUserData($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function updateUserProfile($user_id, $data) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
    return $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $user_id
    ]);
}

function changeUserPassword($user_id, $data) {
    global $pdo;
    $hashed = hashPassword($data['new_password']);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed, $user_id]);
}

function uploadUserAvatar($user_id, $file) {
    $upload = uploadFile($file);
    if ($upload['success']) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$upload['filename'], $user_id]);
        return true;
    }
    return false;
}

function getUserLoginHistory($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM login_logs WHERE user_id = ? ORDER BY login_time DESC LIMIT ?");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

function getUserDocuments($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getDocumentStatusClass($status) {
    $classes = [
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
        'processing' => 'badge-info',
        'completed' => 'badge-success'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// ============================================================================
// FILE UTILITY FUNCTIONS
// ============================================================================

function formatBytes($size, $precision = 2) {
    if ($size == 0) return '0 B';
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image'
    ];
    return $icons[$ext] ?? 'alt';
}

function logActivity($user_id, $action, $description) {
    // Placeholder: Save this activity into a log table if needed
    // global $pdo;
    // $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, ?, ?, NOW())");
    // $stmt->execute([$user_id, $action, $description]);
}
