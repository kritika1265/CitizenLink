<!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="container">
                <div class="footer-sections">
                    <!-- About Section -->
                    <div class="footer-section">
                        <h3>About CitizenLink</h3>
                        <p>CitizenLink is the official digital platform for accessing government services online. We're committed to making civic processes transparent, efficient, and accessible to all citizens.</p>
                        <div class="social-links">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="footer-section">
                        <h3>Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo SITE_URL; ?>/pages/services/application-form.php">Apply for Services</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/services/service-status.php">Track Application</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/services/document-upload.php">Upload Documents</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/services/payment-gateway.php">Make Payment</a></li>
                            <li><a href="#faq">FAQ</a></li>
                        </ul>
                    </div>
                    
                    <!-- Services -->
                    <div class="footer-section">
                        <h3>Services</h3>
                        <ul class="footer-links">
                            <li><a href="#">Birth Certificate</a></li>
                            <li><a href="#">Death Certificate</a></li>
                            <li><a href="#">Marriage Certificate</a></li>
                            <li><a href="#">Business License</a></li>
                            <li><a href="#">Property Tax</a></li>
                            <li><a href="#">Water Connection</a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="footer-section">
                        <h3>Contact Us</h3>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong>Address:</strong><br>
                                    Government Complex<br>
                                    Anand, Gujarat 388001
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <strong>Phone:</strong><br>
                                    1-800-CITIZEN<br>
                                    (1-800-248-4936)
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>Email:</strong><br>
                                    <?php echo SITE_EMAIL; ?>
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Hours:</strong><br>
                                    Mon-Fri: 8:00 AM - 6:00 PM<br>
                                    Sat: 9:00 AM - 2:00 PM
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <div class="copyright">
                        <p>&copy; <?php echo date('Y'); ?> CitizenLink. All rights reserved.</p>
                    </div>
                    <div class="footer-links-bottom">
                        <a href="#privacy">Privacy Policy</a>
                        <a href="#terms">Terms of Service</a>
                        <a href="#accessibility">Accessibility</a>
                        <a href="#sitemap">Site Map</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <?php if (isset($page_js)): ?>
        <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $page_js; ?>"></script>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional Scripts -->
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>