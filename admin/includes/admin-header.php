<?php
// admin/includes/admin-header.php
$adminInfo = getAdminInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - CitizenLink Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Admin Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/admin.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3>
                    <i class="fas fa-shield-alt"></i>
                    CitizenLink Admin
                </h3>
            </div>
            
            <ul class="list-unstyled components">
                <li class="<?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                
                <li class="<?php echo (strpos($currentPage, 'users') !== false) ? 'active' : ''; ?>">
                    <a href="#userSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-users"></i>
                        Users Management
                    </a>
                    <ul class="collapse list-unstyled" id="userSubmenu">
                        <li><a href="<?php echo ADMIN_URL; ?>/users/index.php">All Users</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/users/index.php?status=active">Active Users</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/users/index.php?status=inactive">Inactive Users</a></li>
                    </ul>
                </li>
                
                <li class="<?php echo (strpos($currentPage, 'services') !== false) ? 'active' : ''; ?>">
                    <a href="#serviceSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-cogs"></i>
                        Services
                    </a>
                    <ul class="collapse list-unstyled" id="serviceSubmenu">
                        <li><a href="<?php echo ADMIN_URL; ?>/services/index.php">All Services</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/services/create.php">Add Service</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/services/applications.php">Applications</a></li>
                    </ul>
                </li>
                
                <li class="<?php echo (strpos($currentPage, 'payments') !== false) ? 'active' : ''; ?>">
                    <a href="#paymentSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-credit-card"></i>
                        Payments
                    </a>
                    <ul class="collapse list-unstyled" id="paymentSubmenu">
                        <li><a href="<?php echo ADMIN_URL; ?>/payments/index.php">All Payments</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/payments/transactions.php">Transactions</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/payments/refunds.php">Refunds</a></li>
                    </ul>
                </li>
                
                <li class="<?php echo (strpos($currentPage, 'reports') !== false) ? 'active' : ''; ?>">
                    <a href="#reportSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                    <ul class="collapse list-unstyled" id="reportSubmenu">
                        <li><a href="<?php echo ADMIN_URL; ?>/reports/index.php">Dashboard</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/reports/users.php">User Reports</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/reports/services.php">Service Reports</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/reports/payments.php">Payment Reports</a></li>
                    </ul>
                </li>
                
                <li class="<?php echo (strpos($currentPage, 'system') !== false) ? 'active' : ''; ?>">
                    <a href="#systemSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-cog"></i>
                        System
                    </a>
                    <ul class="collapse list-unstyled" id="systemSubmenu">
                        <li><a href="<?php echo ADMIN_URL; ?>/system/settings.php">Settings</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/system/backup.php">Backup</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/system/logs.php">Logs</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/system/maintenance.php">Maintenance</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light admin-navbar">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                    </button>
                    
                    <div class="navbar-nav ms-auto">
                        <!-- Notifications -->
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger notification-count">3</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><a class="dropdown-item" href="#"><small>New user registration</small></a></li>
                                <li><a class="dropdown-item" href="#"><small>Payment received</small></a></li>
                                <li><a class="dropdown-item" href="#"><small>Application submitted</small></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="#">View All</a></li>
                            </ul>
                        </div>
                        
                        <!-- Admin Profile -->
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($adminInfo['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                                <li><h6 class="dropdown-header">Logged in as: <?php echo htmlspecialchars($adminInfo['role']); ?></h6></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Main Content Area -->
            <div class="main-content">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['warning_message'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['warning_message']; unset($_SESSION['warning_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>