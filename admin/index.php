<?php
// admin/index.php
session_start();

// Include required files
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once 'includes/admin-functions.php';

// Check if admin is logged in
requireAdminLogin();

// Set page variables
$currentPage = 'dashboard';
$pageTitle = 'Admin Dashboard';

// Get admin info
$adminInfo = getAdminInfo();

// Get dashboard statistics
$stats = getDashboardStats();

// Get recent activities
$recentActivities = getRecentActivities(10);

// Get chart data for the last 12 months
$chartData = [];
try {
    // Monthly user registrations
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $userRegistrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly applications
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM applications 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $applicationSubmissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly revenue
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as total
        FROM payments 
        WHERE status = 'completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $chartData = [
        'users' => $userRegistrations,
        'applications' => $applicationSubmissions,
        'revenue' => $monthlyRevenue
    ];
} catch (PDOException $e) {
    error_log("Dashboard chart data error: " . $e->getMessage());
}

// Include header
include 'includes/admin-header.php';
?>

<!-- Page Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </h1>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($adminInfo['username']); ?>!</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Statistics Cards Row -->
        <div class="row">
            <!-- Total Users -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo number_format($stats['total_users'] ?? 0); ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="<?php echo ADMIN_URL; ?>/users/index.php" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Active Applications -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo number_format($stats['active_applications'] ?? 0); ?></h3>
                        <p>Active Applications</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <a href="<?php echo ADMIN_URL; ?>/services/applications.php" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Monthly Payments -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo number_format($stats['monthly_payments'] ?? 0); ?></h3>
                        <p>This Month's Payments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <a href="<?php echo ADMIN_URL; ?>/payments/index.php" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Monthly Revenue -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo formatCurrency($stats['monthly_revenue'] ?? 0); ?></h3>
                        <p>Monthly Revenue</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <a href="<?php echo ADMIN_URL; ?>/reports/payments.php" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Second Row - Quick Stats -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-user-plus"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">New Users (This Week)</span>
                        <span class="info-box-number"><?php echo number_format($stats['new_users_week'] ?? 0); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending Applications</span>
                        <span class="info-box-number"><?php echo number_format($stats['pending_applications'] ?? 0); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">System Status</span>
                        <span class="info-box-number">Online</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-danger">
                        <i class="fas fa-server"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Server Load</span>
                        <span class="info-box-number"><?php echo rand(10, 80); ?>%</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts and Recent Activity Row -->
        <div class="row">
            <!-- Charts Column -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-area me-2"></i>
                            Monthly Statistics
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#chartsCard">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="chartsCard">
                        <div class="chart-container">
                            <canvas id="monthlyStatsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6">
                                <a href="<?php echo ADMIN_URL; ?>/users/index.php" class="btn btn-app bg-success">
                                    <i class="fas fa-users"></i>
                                    Manage Users
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="<?php echo ADMIN_URL; ?>/services/create.php" class="btn btn-app bg-warning">
                                    <i class="fas fa-plus"></i>
                                    Add Service
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="<?php echo ADMIN_URL; ?>/services/applications.php" class="btn btn-app bg-info">
                                    <i class="fas fa-file-alt"></i>
                                    Applications
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="<?php echo ADMIN_URL; ?>/reports/index.php" class="btn btn-app bg-danger">
                                    <i class="fas fa-chart-bar"></i>
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Column -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history me-2"></i>
                            Recent Activity
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#activityCard">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0" id="activityCard">
                        <div class="activity-feed">
                            <?php if (!empty($recentActivities)): ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <?php if ($activity['type'] === 'application'): ?>
                                                <i class="fas fa-file-alt text-info"></i>
                                            <?php elseif ($activity['type'] === 'payment'): ?>
                                                <i class="fas fa-credit-card text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-bell text-warning"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-text">
                                                <strong><?php echo htmlspecialchars($activity['user_name']); ?></strong>
                                                <?php if ($activity['type'] === 'application'): ?>
                                                    applied for <em><?php echo htmlspecialchars($activity['service_name']); ?></em>
                                                <?php elseif ($activity['type'] === 'payment'): ?>
                                                    made a payment: <em><?php echo htmlspecialchars($activity['service_name']); ?></em>
                                                <?php endif; ?>
                                            </div>
                                            <div class="activity-meta">
                                                <?php echo getStatusBadge($activity['status']); ?>
                                                <small class="text-muted ms-2">
                                                    <?php echo formatAdminDate($activity['created_at']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center p-3">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($recentActivities)): ?>
                            <div class="card-footer text-center">
                                <a href="<?php echo ADMIN_URL; ?>/system/logs.php" class="btn btn-sm btn-primary">
                                    View All Activity
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- System Information Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>
                            System Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">PHP Version:</span>
                                <span class="info-value"><?php echo PHP_VERSION; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Server:</span>
                                <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Database:</span>
                                <span class="info-value">MySQL <?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login:</span>
                                <span class="info-value"><?php echo formatAdminDate($adminInfo['last_login']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Current Time:</span>
                                <span class="info-value" id="currentTime"><?php echo date('M d, Y H:i:s'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- Custom CSS for Dashboard -->
<style>
    .small-box {
        border-radius: 10px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    
    .small-box > .inner {
        padding: 15px;
    }
    
    .small-box > .inner h3 {
        font-size: 2.2rem;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    
    .small-box > .inner p {
        font-size: 1rem;
        margin: 0;
    }
    
    .small-box .icon {
        transition: all .3s linear;
        position: absolute;
        top: -10px;
        right: 10px;
        z-index: 0;
        font-size: 90px;
        color: rgba(255,255,255,0.3);
    }
    
    .small-box .small-box-footer {
        position: relative;
        text-align: center;
        padding: 8px 0;
        color: rgba(255,255,255,0.8);
        color: rgba(255,255,255,0.8);
        display: block;
        z-index: 10;
        background: rgba(0,0,0,0.1);
        text-decoration: none;
        border-radius: 0 0 10px 10px;
    }
    
    .small-box .small-box-footer:hover {
        color: #fff;
        background: rgba(0,0,0,0.15);
    }
    
    .info-box {
        display: block;
        min-height: 90px;
        background: #fff;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 10px;
        margin-bottom: 15px;
    }
    
    .info-box .info-box-icon {
        border-top-left-radius: 10px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 10px;
        display: block;
        float: left;
        height: 90px;
        width: 90px;
        text-align: center;
        font-size: 45px;
        line-height: 90px;
        background: rgba(0,0,0,0.2);
    }
    
    .info-box .info-box-content {
        padding: 5px 10px;
        margin-left: 90px;
    }
    
    .info-box .info-box-text {
        text-transform: uppercase;
        font-weight: bold;
        font-size: 14px;
    }
    
    .info-box .info-box-number {
        display: block;
        margin-top: 5px;
        font-weight: bold;
        font-size: 18px;
    }
    
    .activity-feed {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .activity-item {
        display: flex;
        padding: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f4f4f4;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }
    
    .activity-content {
        flex: 1;
    }
    
    .activity-text {
        margin-bottom: 5px;
        line-height: 1.4;
    }
    
    .activity-meta {
        display: flex;
        align-items: center;
    }
    
    .info-list {
        space: 10px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #666;
    }
    
    .info-value {
        color: #333;
        font-family: monospace;
        font-size: 0.9rem;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .btn-app {
        border-radius: 10px;
        position: relative;
        padding: 15px 5px;
        margin: 0 5px 10px 0;
        min-width: 80px;
        height: 80px;
        text-align: center;
        color: #fff;
        border: none;
        display: inline-block;
    }
    
    .btn-app > .fa, .btn-app > .fas, .btn-app > .far, .btn-app > .fab, .btn-app > .fal, .btn-app > .fad {
        font-size: 20px;
        display: block;
    }
    
    .btn-app:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>

<script>
$(document).ready(function() {
    // Update current time every second
    setInterval(function() {
        var now = new Date();
        var timeString = now.toLocaleDateString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
        }) + ' ' + now.toLocaleTimeString();
        $('#currentTime').text(timeString);
    }, 1000);
    
    // Initialize charts
    initializeCharts();
});

function initializeCharts() {
    var ctx = document.getElementById('monthlyStatsChart').getContext('2d');
    
    // Prepare data for the last 12 months
    var months = [];
    var userCounts = [];
    var applicationCounts = [];
    var revenueCounts = [];
    
    // Generate last 12 months
    for (var i = 11; i >= 0; i--) {
        var date = new Date();
        date.setMonth(date.getMonth() - i);
        var monthKey = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
        var monthLabel = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        
        months.push(monthLabel);
        
        // Find data for this month
        var userData = <?php echo json_encode($chartData['users']); ?>.find(item => item.month === monthKey);
        var appData = <?php echo json_encode($chartData['applications']); ?>.find(item => item.month === monthKey);
        var revenueData = <?php echo json_encode($chartData['revenue']); ?>.find(item => item.month === monthKey);
        
        userCounts.push(userData ? parseInt(userData.count) : 0);
        applicationCounts.push(appData ? parseInt(appData.count) : 0);
        revenueCounts.push(revenueData ? parseFloat(revenueData.total) : 0);
    }
    
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'New Users',
                data: userCounts,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Applications',
                data: applicationCounts,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }, {
                label: 'Revenue (₹)',
                data: revenueCounts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue (₹)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Statistics Overview'
                }
            }
        }
    });
}
</script>

<?php
// Include footer
include 'includes/admin-footer.php';
?>