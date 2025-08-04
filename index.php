<?php
// index.php - Main Landing Page
session_start();

// Include configuration files
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get basic statistics for display
$stats = [];
try {
    // Total users registered
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Total applications processed
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE status IN ('approved', 'completed')");
    $stats['processed_applications'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Available services
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
    $stats['available_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Get featured services
    $stmt = $pdo->query("SELECT * FROM services WHERE status = 'active' AND featured = 1 LIMIT 6");
    $featured_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get latest news/announcements
    $stmt = $pdo->query("SELECT * FROM announcements WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
    $latest_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Homepage stats error: " . $e->getMessage());
    $stats = ['total_users' => 0, 'processed_applications' => 0, 'available_services' => 0];
    $featured_services = [];
    $latest_news = [];
}

$pageTitle = 'Welcome to CitizenLink';
$currentPage = 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Digital Government Services</title>
    <meta name="description" content="CitizenLink - Your gateway to digital government services. Apply for documents, track applications, make payments, and access government services online.">
    <meta name="keywords" content="government services, digital services, online applications, citizen portal, e-governance">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/public/assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Header Styles */
        .main-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .main-header.scrolled {
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }
        
        .navbar-nav .nav-link {
            font-weight: 500;
            margin: 0 10px;
            color: #333 !important;
            transition: color 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .btn-login {
            background: var(--gradient-1);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        /* Hero Section */
        .hero-section {
            background: var(--gradient-1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="grad1" cx="50%" cy="50%" r="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.1);stop-opacity:1" /><stop offset="100%" style="stop-color:rgba(255,255,255,0);stop-opacity:1" /></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23grad1)"/><circle cx="800" cy="300" r="150" fill="url(%23grad1)"/><circle cx="300" cy="700" r="120" fill="url(%23grad1)"/><circle cx="700" cy="800" r="80" fill="url(%23grad1)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-hero {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-hero-primary {
            background: white;
            color: var(--primary-color);
            border: 2px solid white;
        }
        
        .btn-hero-primary:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        
        .btn-hero-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
            transform: translateY(-3px);
        }
        
        .hero-image {
            position: relative;
        }
        
        .hero-image img {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.2));
        }
        
        /* Stats Section */
        .stats-section {
            background: white;
            padding: 80px 0;
            position: relative;
        }
        
        .stats-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: white;
            border-radius: 50px 50px 0 0;
        }
        
        .stat-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: var(--gradient-1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }
        
        /* Services Section */
        .services-section {
            background: var(--light-color);
            padding: 100px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .section-title p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .service-card:hover::before {
            transform: scaleX(1);
        }
        
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .service-icon {
            width: 70px;
            height: 70px;
            background: var(--gradient-1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 20px;
        }
        
        .service-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .service-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .service-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .service-link:hover {
            color: var(--primary-dark);
            gap: 12px;
        }
        
        /* News Section */
        .news-section {
            padding: 100px 0;
            background: white;
        }
        
        .news-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .news-image {
            height: 200px;
            background: var(--gradient-1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .news-content {
            padding: 25px;
        }
        
        .news-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .news-excerpt {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #999;
        }
        
        /* Footer */
        .main-footer {
            background: var(--dark-color);
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-section h5 {
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid #374151;
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            color: #9ca3af;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .btn-hero {
                padding: 12px 25px;
                font-size: 1rem;
            }
            
            .stat-card {
                margin-bottom: 30px;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
        
        /* Animation Classes */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Floating Elements */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translate(0, 0px); }
            50% { transform: translate(0, -20px); }
            100% { transform: translate(0, -0px); }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-landmark me-2"></i>
                    CitizenLink
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#services">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/public/about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/support/help.php">Help</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/public/contact.php">Contact</a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/dashboard/index.php">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/dashboard/profile.php">
                                        <i class="fas fa-user me-2"></i>Profile
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/auth/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/auth/register.php">Register</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-login ms-2" href="<?php echo BASE_URL; ?>/pages/auth/login.php">
                                    <i class="fas fa-sign-in-alt me-1"></i>
                                    Login
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content" data-aos="fade-up">
                        <h1 class="hero-title">
                            Your Gateway to 
                            <span style="background: linear-gradient(45deg, #ffd700, #ffed4e); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Digital Government</span>
                            Services
                        </h1>
                        <p class="hero-subtitle">
                            Access government services anytime, anywhere. Apply for documents, track applications, make payments, and more - all from the comfort of your home.
                        </p>
                        <div class="hero-buttons">
                            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                                <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="btn-hero btn-hero-primary">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Go to Dashboard
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/pages/auth/register.php" class="btn-hero btn-hero-primary">
                                    <i class="fas fa-user-plus"></i>
                                    Get Started
                                </a>
                            <?php endif; ?>
                            <a href="#services" class="btn-hero btn-hero-outline">
                                <i class="fas fa-search"></i>
                                Explore Services
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image" data-aos="fade-left">
                        <div class="floating">
                            <div style="width: 500px; height: 400px; background: rgba(255,255,255,0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                                <i class="fas fa-laptop-code" style="font-size: 8rem; color: rgba(255,255,255,0.8);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number" data-count="<?php echo $stats['total_users']; ?>">0</div>
                        <div class="stat-label">Registered Citizens</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="stat-icon">
                            <i class="fas fa-file-check"></i>
                        </div>
                        <div class="stat-number" data-count="<?php echo $stats['processed_applications']; ?>">0</div>
                        <div class="stat-label">Applications Processed</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="stat-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="stat-number" data-count="<?php echo $stats['available_services']; ?>">0</div>
                        <div class="stat-label">Available Services</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Popular Services</h2>
                <p>Quick access to the most requested government services</p>
            </div>
            
            <div class="row">
                <?php if (!empty($featured_services)): ?>
                    <?php foreach ($featured_services as $index => $service): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                                <div class="service-icon">
                                    <i class="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-file-alt'); ?>"></i>
                                </div>
                                <h3 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                                <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                                <a href="<?php echo BASE_URL; ?>/pages/services/apply.php?service=<?php echo $service['id']; ?>" class="service-link">
                                    Apply Now <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default services when database is empty -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="service-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <h3 class="service-title">Property Services</h3>
                            <p class="service-description">Property registration, building permits, and land-related documentation.</p>
                            <a href="<?php echo BASE_URL; ?>/pages/auth/login.php" class="service-link">
                                Apply Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card" data-aos="fade-up" data-aos-delay="400">
                            <div class="service-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h3 class="service-title">Business Licenses</h3>
                            <p class="service-description">Register your business, apply for trade licenses, and access business services.</p>
                            <a href="<?php echo BASE_URL; ?>/pages/auth/login.php" class="service-link">
                                Apply Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card" data-aos="fade-up" data-aos-delay="500">
                            <div class="service-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="service-title">Education Services</h3>
                            <p class="service-description">Certificate verification, transcripts, and educational document services.</p>
                            <a href="<?php echo BASE_URL; ?>/pages/auth/login.php" class="service-link">
                                Apply Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card" data-aos="fade-up" data-aos-delay="600">
                            <div class="service-icon">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <h3 class="service-title">Health Services</h3>
                            <p class="service-description">Medical certificates, health cards, and healthcare-related applications.</p>
                            <a href="<?php echo BASE_URL; ?>/pages/auth/login.php" class="service-link">
                                Apply Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="<?php echo BASE_URL; ?>/pages/public/services-info.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-th-large me-2"></i>
                    View All Services
                </a>
            </div>
        </div>
    </section>

    <!-- News & Announcements Section -->
    <section class="news-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Latest News & Announcements</h2>
                <p>Stay updated with the latest government news and service updates</p>
            </div>
            
            <div class="row">
                <?php if (!empty($latest_news)): ?>
                    <?php foreach ($latest_news as $index => $news): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="news-card" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                                <div class="news-image">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div class="news-content">
                                    <h4 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h4>
                                    <p class="news-excerpt"><?php echo htmlspecialchars(substr($news['content'], 0, 120)) . '...'; ?></p>
                                    <div class="news-meta">
                                        <span><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($news['created_at'])); ?></span>
                                        <a href="<?php echo BASE_URL; ?>/pages/public/news.php?id=<?php echo $news['id']; ?>" class="text-primary">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default news when database is empty -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="news-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="news-image">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="news-content">
                                <h4 class="news-title">CitizenLink Platform Launch</h4>
                                <p class="news-excerpt">We're excited to announce the official launch of CitizenLink, your new gateway to digital government services...</p>
                                <div class="news-meta">
                                    <span><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y'); ?></span>
                                    <a href="#" class="text-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="news-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="news-image">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="news-content">
                                <h4 class="news-title">Enhanced Security Features</h4>
                                <p class="news-excerpt">New security measures implemented to protect your personal information and ensure safe transactions...</p>
                                <div class="news-meta">
                                    <span><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime('-2 days')); ?></span>
                                    <a href="#" class="text-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="news-card" data-aos="fade-up" data-aos-delay="300">
                            <div class="news-image">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="news-content">
                                <h4 class="news-title">Mobile App Coming Soon</h4>
                                <p class="news-excerpt">Access government services on the go with our upcoming mobile app. Sign up for early access notifications...</p>
                                <div class="news-meta">
                                    <span><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime('-5 days')); ?></span>
                                    <a href="#" class="text-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section style="background: var(--gradient-1); padding: 80px 0; color: white;">
        <div class="container text-center">
            <div data-aos="fade-up">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 20px;">
                    Ready to Get Started?
                </h2>
                <p style="font-size: 1.2rem; margin-bottom: 40px; opacity: 0.9;">
                    Join thousands of citizens who have already simplified their government interactions
                </p>
                <?php if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']): ?>
                    <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                        <a href="<?php echo BASE_URL; ?>/pages/auth/register.php" class="btn btn-hero btn-hero-primary">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/auth/login.php" class="btn btn-hero btn-hero-outline">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="btn btn-hero btn-hero-primary">
                        <i class="fas fa-tachometer-alt"></i>
                        Go to Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>
                            <i class="fas fa-landmark me-2"></i>
                            CitizenLink
                        </h5>
                        <p>Your trusted gateway to digital government services. Making government accessible, transparent, and efficient for all citizens.</p>
                        <div class="social-links">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Quick Links</h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/pages/public/about.php">About Us</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/pages/public/services-info.php">Services</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/pages/public/news.php">News</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/pages/public/contact.php">Contact</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Services</h5>
                        <ul class="footer-links">
                            <li><a href="#">Identity Documents</a></li>
                            <li><a href="#">Vehicle Registration</a></li>
                            <li><a href="#">Property Services</a></li>
                            <li><a href="#">Business Licenses</a></li>
                            <li><a href="#">Education Services</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Support</h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo BASE_URL; ?>/pages/support/help.php">Help Center</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/pages/support/faq.php">FAQ</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/pages/support/contact.php">Contact Support</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Account</h5>
                        <ul class="footer-links">
                            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                                <li><a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php">Dashboard</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/pages/dashboard/profile.php">My Profile</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/pages/services/status.php">Track Applications</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/pages/auth/logout.php">Logout</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo BASE_URL; ?>/pages/auth/login.php">Login</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/pages/auth/register.php">Register</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/pages/auth/forgot-password.php">Forgot Password</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> CitizenLink. All rights reserved. | Designed for the people, by the government.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px; display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/bootstrap.min.js"></script>
    
    <!-- jQuery -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/jquery.min.js"></script>
    
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        $(document).ready(function() {
            // Initialize AOS
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
            
            // Header scroll effect
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('.main-header').addClass('scrolled');
                    $('#backToTop').fadeIn();
                } else {
                    $('.main-header').removeClass('scrolled');
                    $('#backToTop').fadeOut();
                }
            });
            
            // Back to top button
            $('#backToTop').click(function() {
                $('html, body').animate({scrollTop: 0}, 800);
                return false;
            });
            
            // Smooth scrolling for anchor links
            $('a[href^="#"]').on('click', function(event) {
                var target = $(this.getAttribute('href'));
                if( target.length ) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 80
                    }, 1000);
                }
            });
            
            // Counter animation
            $('.stat-number').each(function() {
                var $this = $(this);
                var countTo = $this.attr('data-count');
                
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            $({ countNum: 0 }).animate({ countNum: countTo }, {
                                duration: 2000,
                                easing: 'linear',
                                step: function() {
                                    $this.text(Math.floor(this.countNum).toLocaleString());
                                },
                                complete: function() {
                                    $this.text(parseInt(countTo).toLocaleString());
                                }
                            });
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe($this[0]);
            });
            
            // Service card hover effect
            $('.service-card').hover(
                function() {
                    $(this).find('.service-icon').css('transform', 'scale(1.1) rotate(5deg)');
                },
                function() {
                    $(this).find('.service-icon').css('transform', 'scale(1) rotate(0deg)');
                }
            );
            
            // News card click animation
            $('.news-card').click(function() {
                $(this).addClass('clicked');
                setTimeout(() => {
                    $(this).removeClass('clicked');
                }, 200);
            });
            
            // Add loading spinner to buttons on click
            $('.btn-hero, .btn-primary').click(function() {
                if (!$(this).hasClass('loading')) {
                    var originalText = $(this).html();
                    $(this).addClass('loading').html('<span class="loading-spinner"></span> Loading...');
                    
                    // Remove loading state after navigation (if it fails)
                    setTimeout(() => {
                        $(this).removeClass('loading').html(originalText);
                    }, 3000);
                }
            });
            
            // Floating animation for hero image
            setInterval(function() {
                $('.floating').css('animation', 'floating 3s ease-in-out infinite');
            }, 100);
            
            // Parallax effect for hero section
            $(window).scroll(function() {
                var scrolled = $(this).scrollTop();
                var parallax = $('.hero-section');
                var speed = 0.5;
                parallax.css('transform', 'translateY(' + (scrolled * speed) + 'px)');
            });
            
            // Add fade-in animation to elements as they come into view
            function checkFadeIn() {
                $('.fade-in-up').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();
                    
                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).addClass('visible');
                    }
                });
            }
            
            $(window).on('scroll resize', checkFadeIn);
            checkFadeIn();
            
            // Add some interactive elements
            $('.stat-card, .service-card, .news-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-10px) scale(1.02)');
                },
                function() {
                    $(this).css('transform', 'translateY(0) scale(1)');
                }
            );
            
            // Preloader (if needed)
            $(window).on('load', function() {
                $('#preloader').fadeOut('slow');
            });
            
            // Dynamic year in footer
            $('#currentYear').text(new Date().getFullYear());
            
            // Form validation for newsletter signup (if added)
            $('#newsletterForm').on('submit', function(e) {
                e.preventDefault();
                var email = $('#newsletterEmail').val();
                if (email && isValidEmail(email)) {
                    // Handle newsletter signup
                    showNotification('Thank you for subscribing!', 'success');
                    $('#newsletterEmail').val('');
                } else {
                    showNotification('Please enter a valid email address.', 'error');
                }
            });
            
            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }
            
            function showNotification(message, type) {
                var notification = $('<div class="notification notification-' + type + '">' + message + '</div>');
                $('body').append(notification);
                
                setTimeout(function() {
                    notification.addClass('show');
                }, 100);
                
                setTimeout(function() {
                    notification.removeClass('show');
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
        });
        
        // Additional CSS for notifications
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 5px;
                    color: white;
                    font-weight: 500;
                    z-index: 10000;
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                }
                .notification.show {
                    transform: translateX(0);
                }
                .notification-success {
                    background: var(--success-color);
                }
                .notification-error {
                    background: var(--danger-color);
                }
                .clicked {
                    transform: scale(0.98) !important;
                    transition: transform 0.1s ease !important;
                }
            `)
            .appendTo('head');
    </script>
</body>
</html>="fas fa-id-card"></i>
                            </div>
                            <h3 class="service-title">Identity Documents</h3>
                            <p class="service-description">Apply for national ID cards, passports, and other identity documents online.</p>
                            <a href="<?php echo BASE_URL; ?>/pages/auth/login.php" class="service-link">
                                Apply Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="service-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <h3 class="service-title">Vehicle Registration</h3>
                            <p class="service-description">Register your vehicle, renew licenses, and access driving-related services.</p>
                            <a href="<?php echo BASE_URL; ?>/pages/auth/login.php" class="service-link">
                                Apply Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                            <div class="service-icon">
                                <i class