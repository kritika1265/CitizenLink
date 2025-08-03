<?php
// admin/includes/admin-functions.php

/**
 * Admin specific functions for CitizenLink
 */

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Check admin session and redirect if not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit();
    }
}

// Get admin info from session
function getAdminInfo() {
    if (isAdminLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'] ?? 0,
            'username' => $_SESSION['admin_username'] ?? '',
            'email' => $_SESSION['admin_email'] ?? '',
            'role' => $_SESSION['admin_role'] ?? 'admin',
            'last_login' => $_SESSION['admin_last_login'] ?? ''
        ];
    }
    return null;
}

// Verify admin credentials
function verifyAdminLogin($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$admin['id']]);
            
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_last_login'] = date('Y-m-d H:i:s');
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Admin login error: " . $e->getMessage());
        return false;
    }
}

// Admin logout
function adminLogout() {
    // Unset admin session variables
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_role']);
    unset($_SESSION['admin_last_login']);
    
    // Redirect to login page
    header('Location: ' . ADMIN_URL . '/login.php');
    exit();
}

// Get dashboard statistics
function getDashboardStats() {
    global $pdo;
    
    $stats = [];
    
    try {
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status != 'deleted'");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Active applications
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE status IN ('pending', 'processing')");
        $stats['active_applications'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total payments this month
        $stmt = $pdo->query("SELECT COUNT(*) as count, SUM(amount) as total FROM payments WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
        $payment_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_payments'] = $payment_data['count'];
        $stats['monthly_revenue'] = $payment_data['total'] ?? 0;
        
        // New users this week
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['new_users_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Pending applications
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'");
        $stats['pending_applications'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        return [];
    }
}

// Get recent activities
function getRecentActivities($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                'application' as type,
                a.id,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                s.name as service_name,
                a.status,
                a.created_at
            FROM applications a
            JOIN users u ON a.user_id = u.id
            JOIN services s ON a.service_id = s.id
            WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            
            UNION ALL
            
            SELECT 
                'payment' as type,
                p.id,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                CONCAT('Payment of ₹', p.amount) as service_name,
                p.status,
                p.created_at
            FROM payments p
            JOIN users u ON p.user_id = u.id
            WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Recent activities error: " . $e->getMessage());
        return [];
    }
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Format date for display
function formatAdminDate($date) {
    return date('M d, Y H:i', strtotime($date));
}

// Get status badge HTML
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'processing' => '<span class="badge badge-info">Processing</span>',
        'approved' => '<span class="badge badge-success">Approved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'completed' => '<span class="badge badge-success">Completed</span>',
        'active' => '<span class="badge badge-success">Active</span>',
        'inactive' => '<span class="badge badge-secondary">Inactive</span>',
        'deleted' => '<span class="badge badge-danger">Deleted</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

// Check admin permissions
function hasAdminPermission($permission) {
    $adminInfo = getAdminInfo();
    if (!$adminInfo) return false;
    
    // Super admin has all permissions
    if ($adminInfo['role'] === 'super_admin') return true;
    
    // Define role permissions
    $permissions = [
        'admin' => ['view_users', 'view_applications', 'view_payments', 'approve_applications'],
        'manager' => ['view_users', 'view_applications', 'view_payments', 'approve_applications', 'manage_services', 'view_reports'],
        'super_admin' => ['*'] // All permissions
    ];
    
    $rolePermissions = $permissions[$adminInfo['role']] ?? [];
    
    return in_array('*', $rolePermissions) || in_array($permission, $rolePermissions);
}

// Log admin activity
function logAdminActivity($action, $details = '') {
    global $pdo;
    
    $adminInfo = getAdminInfo();
    if (!$adminInfo) return false;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $adminInfo['id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Admin activity log error: " . $e->getMessage());
        return false;
    }
}

// Sanitize input for admin forms
function sanitizeAdminInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['admin_csrf_token']) && hash_equals($_SESSION['admin_csrf_token'], $token);
}
?>