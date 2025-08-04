<?php
// admin/includes/admin-sidebar.php
$adminInfo = getAdminInfo();
$currentPage = $currentPage ?? '';
?>

<div class="sidebar-content">
    <!-- User Profile Section -->
    <div class="admin-profile">
        <div class="profile-img">
            <i class="fas fa-user-circle fa-3x"></i>
        </div>
        <div class="profile-info">
            <h5><?php echo htmlspecialchars($adminInfo['username']); ?></h5>
            <small class="text-muted"><?php echo ucfirst($adminInfo['role']); ?></small>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
        
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="<?php echo ADMIN_URL; ?>/index.php" class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        
        <!-- Users Management -->
        <li class="nav-item <?php echo (strpos($currentPage, 'users') !== false) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo (strpos($currentPage, 'users') !== false) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-users"></i>
                <p>
                    Users Management
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/users/index.php" class="nav-link <?php echo ($currentPage === 'users-list') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/users/index.php?status=active" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Active Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/users/index.php?status=inactive" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Inactive Users</p>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Services Management -->
        <li class="nav-item <?php echo (strpos($currentPage, 'services') !== false) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo (strpos($currentPage, 'services') !== false) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-cogs"></i>
                <p>
                    Services
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/services/index.php" class="nav-link <?php echo ($currentPage === 'services-list') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Services</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/services/create.php" class="nav-link <?php echo ($currentPage === 'services-create') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Service</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/services/applications.php" class="nav-link <?php echo ($currentPage === 'services-applications') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Applications</p>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Payments -->
        <li class="nav-item <?php echo (strpos($currentPage, 'payments') !== false) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo (strpos($currentPage, 'payments') !== false) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-credit-card"></i>
                <p>
                    Payments
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/payments/index.php" class="nav-link <?php echo ($currentPage === 'payments-list') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Payments</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/payments/transactions.php" class="nav-link <?php echo ($currentPage === 'payments-transactions') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Transactions</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/payments/refunds.php" class="nav-link <?php echo ($currentPage === 'payments-refunds') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Refunds</p>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Reports -->
        <li class="nav-item <?php echo (strpos($currentPage, 'reports') !== false) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo (strpos($currentPage, 'reports') !== false) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>
                    Reports & Analytics
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/reports/index.php" class="nav-link <?php echo ($currentPage === 'reports-dashboard') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Overview</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/reports/users.php" class="nav-link <?php echo ($currentPage === 'reports-users') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>User Reports</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/reports/services.php" class="nav-link <?php echo ($currentPage === 'reports-services') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Service Reports</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/reports/payments.php" class="nav-link <?php echo ($currentPage === 'reports-payments') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Payment Reports</p>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- System Settings -->
        <?php if (hasAdminPermission('system_settings')): ?>
        <li class="nav-item <?php echo (strpos($currentPage, 'system') !== false) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo (strpos($currentPage, 'system') !== false) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-tools"></i>
                <p>
                    System
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/system/settings.php" class="nav-link <?php echo ($currentPage === 'system-settings') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Settings</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/system/backup.php" class="nav-link <?php echo ($currentPage === 'system-backup') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Backup & Restore</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/system/logs.php" class="nav-link <?php echo ($currentPage === 'system-logs') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>System Logs</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo ADMIN_URL; ?>/system/maintenance.php" class="nav-link <?php echo ($currentPage === 'system-maintenance') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Maintenance</p>
                    </a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
        
        <!-- Quick Links -->
        <li class="nav-header">QUICK LINKS</li>
        
        <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>" target="_blank" class="nav-link">
                <i class="nav-icon fas fa-external-link-alt"></i>
                <p>View Site</p>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="<?php echo ADMIN_URL; ?>/profile.php" class="nav-link <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-user"></i>
                <p>My Profile</p>
            </a>
        </li>
        
        <!-- Logout -->
        <li class="nav-item">
            <a href="<?php echo ADMIN_URL; ?>/logout.php" class="nav-link text-danger">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Logout</p>
            </a>
        </li>
        
    </ul>
</div>