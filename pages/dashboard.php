<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require login
requireLogin();

$user = getUserDetails($_SESSION['user_id']);
if (!$user) {
    redirectTo('pages/login.php');
}

// Get user statistics
try {
    // Get application counts
    $applicationStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_applications,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_applications,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing_applications,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_applications
        FROM applications 
        WHERE user_id = ?
    ");
    $applicationStats->execute([STATUS_PENDING, STATUS_PROCESSING, STATUS_COMPLETED, $user['id']]);
    $stats = $applicationStats->fetch();
    
    // Get recent applications
    $recentApplications = $pdo->prepare("
        SELECT id, reference_number, service_type, status, created_at, amount
        FROM applications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentApplications->execute([$user['id']]);
    $applications = $recentApplications->fetchAll();
    
    // Get pending payments
    $pendingPayments = $pdo->prepare("
        SELECT a.id, a.reference_number, a.service_type, a.amount, a.created_at
        FROM applications a
        LEFT JOIN payments p ON a.id = p.application_id
        WHERE a.user_id = ? AND (p.status IS NULL OR p.status = ?)
        ORDER BY a.created_at DESC
        LIMIT 3
    ");
    $pendingPayments->execute([$user['id'], PAYMENT_PENDING]);
    $payments = $pendingPayments->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = ['total_applications' => 0, 'pending_applications' => 0, 'processing_applications' => 0, 'completed_applications' => 0];
    $applications = [];
    $payments = [];
}

$page_title = "Dashboard - CitizenLink";
$page_css = "dashboard.css";
include '../includes/header.php';
?>

<main class="dashboard-page">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo sanitizeInput($user['name']); ?>!</h1>
                <p>Manage your government services and track your applications</p>
            </div>
            <div class="quick-actions">
                <a href="services/application-form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    New Application
                </a>
                <a href="services/service-status.php" class="btn btn-outline">
                    <i class="fas fa-search"></i>
                    Track Status
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_applications']; ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_applications']; ?></h3>
                    <p>Pending Applications</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon processing">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['processing_applications']; ?></h3>
                    <p>In Processing</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['completed_applications']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Recent Applications -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Recent Applications</h2>
                    <a href="services/service-status.php" class="section-link">View All</a>
                </div>
                
                <div class="applications-list">
                    <?php if (empty($applications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h3>No Applications Yet</h3>
                            <p>You haven't submitted any applications. Start by applying for a service.</p>
                            <a href="services/application-form.php" class="btn btn-primary">Apply for Service</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <div class="application-item">
                                <div class="application-info">
                                    <div class="application-header">
                                        <h4><?php echo ucwords(str_replace('_', ' ', $app['service_type'])); ?></h4>
                                        <?php echo getStatusBadge($app['status']); ?>
                                    </div>
                                    <p class="reference">Reference: <?php echo sanitizeInput($app['reference_number']); ?></p>
                                    <p class="date">Applied on: <?php echo formatDate($app['created_at']); ?></p>
                                </div>
                                <div class="application-actions">
                                    <?php if ($app['amount'] > 0): ?>
                                        <span class="amount"><?php echo formatCurrency($app['amount']); ?></span>
                                    <?php endif; ?>
                                    <a href="services/service-status.php?ref=<?php echo $app['reference_number']; ?>" class="btn btn-sm btn-outline">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pending Payments -->
            <?php if (!empty($payments)): ?>
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Pending Payments</h2>
                        <a href="services/payment-gateway.php" class="section-link">View All</a>
                    </div>
                    
                    <div class="payments-list">
                        <?php foreach ($payments as $payment): ?>
                            <div class="payment-item">
                                <div class="payment-info">
                                    <h4><?php echo ucwords(str_replace('_', ' ', $payment['service_type'])); ?></h4>
                                    <p class="reference">Reference: <?php echo sanitizeInput($payment['reference_number']); ?></p>
                                    <p class="date">Due: <?php echo formatDate($payment['created_at']); ?></p>
                                </div>
                                <div class="payment-actions">
                                    <span class="amount"><?php echo formatCurrency($payment['amount']); ?></span>
                                    <a href="services/payment-gateway.php?ref=<?php echo $payment['reference_number']; ?>" class="btn btn-sm btn-primary">
                                        Pay Now
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Quick Services -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Quick Services</h2>
                </div>
                
                <div class="services-grid">
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-baby"></i>
                        </div>
                        <h4>Birth Certificate</h4>
                        <p>Apply for birth certificate online</p>
                        <a href="services/application-form.php?service=birth_certificate" class="service-btn">Apply Now</a>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Marriage Certificate</h4>
                        <p>Get your marriage certificate</p>
                        <a href="services/application-form.php?service=marriage_certificate" class="service-btn">Apply Now</a>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4>Business License</h4>
                        <p>Register your business online</p>
                        <a href="services/application-form.php?service=business_license" class="service-btn">Apply Now</a>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Property Tax</h4>
                        <p>Pay your property tax online</p>
                        <a href="services/payment-gateway.php?service=property_tax" class="service-btn">Pay Now</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Completion -->
        <?php if (!$user['email_verified'] || empty($user['address'])): ?>
            <div class="dashboard-section">
                <div class="profile-completion">
                    <div class="completion-header">
                        <h3>Complete Your Profile</h3>
                        <p>Complete your profile to access all services</p>
                    </div>
                    <div class="completion-items">
                        <?php if (!$user['email_verified']): ?>
                            <div class="completion-item">
                                <i class="fas fa-envelope"></i>
                                <span>Verify your email address</span>
                                <a href="verify-email.php" class="btn btn-sm btn-primary">Verify</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($user['address'])): ?>
                            <div class="completion-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Add your address</span>
                                <a href="profile.php" class="btn btn-sm btn-outline">Update</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>