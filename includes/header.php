<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'CitizenLink - Digital Government Services'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/<?php echo $page_css; ?>">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap (if needed) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <meta name="description" content="CitizenLink - Your gateway to digital government services. Apply for certificates, pay taxes, and track applications online.">
    <meta name="keywords" content="government services, digital services, certificates, online applications">
    <meta name="author" content="Government of India">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="navbar">
            <div class="container">
                <!-- Logo -->
                <div class="navbar-brand">
                    <a href="<?php echo SITE_URL; ?>">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="CitizenLink Logo" class="logo">
                        <span class="brand-text">CitizenLink</span>
                    </a>
                </div>
                
                <!-- Navigation Menu -->
                <div class="navbar-menu">
                    <ul class="nav-links">
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">Services <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo SITE_URL; ?>/pages/services/application-form.php">Apply for Services</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/services/service-status.php">Track Status</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/services/document-upload.php">Upload Documents</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/services/payment-gateway.php">Make Payment</a></li>
                            </ul>
                        </li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <!-- User Menu -->
                <div class="navbar-user">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-dropdown">
                            <a href="#" class="user-toggle">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo sanitizeInput($_SESSION['user_name'] ?? 'User'); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="user-menu">
                                <li><a href="<?php echo SITE_URL; ?>/pages/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a href="<?php echo SITE_URL; ?>/admin/"><i class="fas fa-cog"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li class="divider"></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="auth-links">
                            <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn btn-outline">Login</a>
                            <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-primary">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <div class="mobile-menu-toggle">
                    <span class="hamburger"></span>
                </div>
            </div>
        </nav>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu">
            <div class="mobile-menu-content">
                <ul class="mobile-nav-links">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/services/application-form.php">Apply for Services</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/services/service-status.php">Track Status</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/services/document-upload.php">Upload Documents</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/services/payment-gateway.php">Make Payment</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                
                <?php if (isLoggedIn()): ?>
                    <div class="mobile-user-menu">
                        <div class="user-info">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo sanitizeInput($_SESSION['user_name'] ?? 'User'); ?></span>
                        </div>
                        <ul class="mobile-user-links">
                            <li><a href="<?php echo SITE_URL; ?>/pages/dashboard.php">Dashboard</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/profile.php">Profile</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo SITE_URL; ?>/admin/">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo SITE_URL; ?>/pages/logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="mobile-auth-links">
                        <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn btn-outline btn-block">Login</a>
                        <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-primary btn-block">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <?php
    $flashMessages = getFlashMessages();
    if (!empty($flashMessages)):
    ?>
        <div class="flash-messages">
            <?php foreach ($flashMessages as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible">
                    <?php echo sanitizeInput($message['message']); ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Area -->