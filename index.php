<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = "CitizenLink - Digital Government Services Portal";
include 'includes/header.php';
?>

<main class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Welcome to CitizenLink</h1>
                <p class="hero-subtitle">Your Gateway to Digital Government Services</p>
                <p class="hero-description">Access government services online, track applications, and manage your civic needs all in one place.</p>
                <div class="hero-actions">
                    <a href="pages/register.php" class="btn btn-primary">Get Started</a>
                    <a href="pages/login.php" class="btn btn-secondary">Sign In</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/hero-illustration.svg" alt="Digital Services">
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <h2>Available Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="icon-document"></i>
                    </div>
                    <h3>Document Services</h3>
                    <p>Apply for certificates, licenses, and official documents online</p>
                    <a href="pages/services/application-form.php" class="service-link">Apply Now</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="icon-payment"></i>
                    </div>
                    <h3>Tax & Payments</h3>
                    <p>Pay taxes, fees, and fines securely online</p>
                    <a href="pages/services/payment-gateway.php" class="service-link">Pay Now</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="icon-status"></i>
                    </div>
                    <h3>Track Applications</h3>
                    <p>Check the status of your submitted applications</p>
                    <a href="pages/services/service-status.php" class="service-link">Track Status</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="icon-upload"></i>
                    </div>
                    <h3>Document Upload</h3>
                    <p>Upload required documents for your applications</p>
                    <a href="pages/services/document-upload.php" class="service-link">Upload Documents</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2>Why Choose CitizenLink?</h2>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="icon-secure"></i>
                    </div>
                    <h3>Secure & Safe</h3>
                    <p>Your data is protected with enterprise-grade security</p>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <i class="icon-fast"></i>
                    </div>
                    <h3>Fast Processing</h3>
                    <p>Quick turnaround times for all your applications</p>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <i class="icon-support"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Get help whenever you need it from our support team</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <h2>Need Help?</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="icon-phone"></i>
                    <div>
                        <h4>Phone Support</h4>
                        <p>1-800-CITIZEN</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="icon-email"></i>
                    <div>
                        <h4>Email Support</h4>
                        <p>support@citizenlink.gov</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="icon-hours"></i>
                    <div>
                        <h4>Office Hours</h4>
                        <p>Mon-Fri: 8AM-6PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>