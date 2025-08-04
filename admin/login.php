<?php
// admin/login.php
session_start();

// Include configuration files
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once 'includes/admin-functions.php';

// If admin is already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeAdminInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Basic validation
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Attempt login
        if (verifyAdminLogin($username, $password)) {
            // Set remember me cookie if requested
            if ($remember_me) {
                $cookie_value = base64_encode($username . ':' . time());
                setcookie('admin_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/'); // 30 days
            }
            
            // Log the login activity
            logAdminActivity('Login', 'Admin logged in successfully');
            
            // Redirect to dashboard or intended page
            $redirect_url = $_SESSION['admin_intended_url'] ?? ADMIN_URL . '/index.php';
            unset($_SESSION['admin_intended_url']);
            
            header('Location: ' . $redirect_url);
            exit();
        } else {
            $error_message = 'Invalid username or password. Please try again.';
            
            // Log failed login attempt
            error_log("Failed admin login attempt for username: " . $username . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
    }
}

// Check for remember me cookie
if (isset($_COOKIE['admin_remember']) && !isAdminLoggedIn()) {
    $cookie_data = base64_decode($_COOKIE['admin_remember']);
    list($remembered_username, $timestamp) = explode(':', $cookie_data);
    
    // Check if cookie is still valid (not older than 30 days)
    if (time() - $timestamp < (30 * 24 * 60 * 60)) {
        $remembered_username = htmlspecialchars($remembered_username);
    } else {
        // Delete expired cookie
        setcookie('admin_remember', '', time() - 3600, '/');
        $remembered_username = '';
    }
} else {
    $remembered_username = '';
}

$pageTitle = 'Admin Login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CitizenLink</title>
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Login CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 2rem 1.5rem 1.5rem;
            border-radius: 15px 15px 0 0;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .login-body {
            padding: 2rem 1.5rem;
        }
        
        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }
        
        .input-group .form-control {
            padding-left: 45px;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #764ba2;
        }
        
        .login-footer {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid #e0e0e0;
            background: #f8f9fa;
            border-radius: 0 0 15px 15px;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            z-index: 10;
        }
        
        .loading {
            display: none;
        }
        
        .btn-login.loading {
            pointer-events: none;
        }
        
        .btn-login.loading .loading {
            display: inline-block;
        }
        
        .btn-login.loading .normal-text {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>CitizenLink Admin</h3>
                    <p class="mb-0">Secure Admin Access</p>
                </div>
                
                <div class="login-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm" novalidate>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Username" 
                                   value="<?php echo $remembered_username; ?>"
                                   required
                                   autocomplete="username">
                            <div class="invalid-feedback">
                                Please enter your username.
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Password" 
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="passwordToggleIcon"></i>
                            </button>
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>
                        
                        <div class="remember-forgot">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="remember_me" 
                                       name="remember_me"
                                       <?php echo $remembered_username ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="remember_me">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="forgot-password" onclick="showForgotPassword()">
                                Forgot Password?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login w-100">
                            <span class="normal-text">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login
                            </span>
                            <span class="loading">
                                <i class="fas fa-spinner fa-spin me-2"></i>
                                Logging in...
                            </span>
                        </button>
                    </form>
                </div>
                
                <div class="login-footer">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secure Admin Portal &copy; <?php echo date('Y'); ?> CitizenLink
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>
                        Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>To reset your admin password, please contact the system administrator.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>System Administrator:</strong><br>
                        Email: admin@citizenlink.gov<br>
                        Phone: +1 (555) 123-4567
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/jquery.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Focus on username field
            $('#username').focus();
            
            // Form validation
            $('#loginForm').on('submit', function(e) {
                var isValid = true;
                
                // Reset validation states
                $('.form-control').removeClass('is-invalid');
                
                // Check username
                if (!$('#username').val().trim()) {
                    $('#username').addClass('is-invalid');
                    isValid = false;
                }
                
                // Check password
                if (!$('#password').val()) {
                    $('#password').addClass('is-invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    return false;
                } else {
                    // Show loading state
                    $('.btn-login').addClass('loading');
                }
            });
            
            // Clear validation on input
            $('.form-control').on('input', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Handle Enter key
            $('.form-control').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#loginForm').submit();
                }
            });
            
            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        function togglePassword() {
            var passwordField = document.getElementById('password');
            var toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        function showForgotPassword() {
            var modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        }
        
        // Prevent back button after login attempt
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>