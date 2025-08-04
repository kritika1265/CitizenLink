<?php
// admin/logout.php
session_start();

// Include required files
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once 'includes/admin-functions.php';

// Log the logout activity if admin is logged in
if (isAdminLoggedIn()) {
    logAdminActivity('Logout', 'Admin logged out successfully');
}

// Clear admin session
adminLogout();
?>