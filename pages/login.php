<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo('pages/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                // Check user credentials
                $stmt = $pdo->prepare("
                    SELECT id, email, password, name, role, status, email_verified, last_login 
                    FROM users 
                    WHERE email = ?
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && verifyPassword($password, $user['password'])) {
                    // Check if account is active
                    if ($user['status'] !== 'active') {
                        $error = 'Your account is inactive. Please contact support.';
                    } elseif (!$user['email_verified']) {
                        $error = 'Please verify your email address before logging in.';
                    } else {
                        // Login successful
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        // Update last login
                        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                        $updateStmt->execute([$user['id']]);
                        
                        // Log activity
                        logActivity($user['id'], 'login', 'User logged in successfully');
                        
                        // Set remember me cookie
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                            
                            // Store token in database
                            $tokenStmt = $pdo->prepare("
                                INSERT INTO remember_tokens (user_id, token, expires_at) 
                                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
                            ");
                            $tokenStmt->execute([$user['id'], hash('sha256', $token)]);
                        }
                        
                        // Redirect to dashboard or intended page
                        $redirect = $_GET['redirect'] ?? 'pages/dashboard.php';
                        redirectTo($redirect);
                    }
                } else {
                    $error = 'Invalid email or password.';
                    
                    // Log failed login attempt
                    if ($user) {
                        logActivity($user['id'], 'login_failed', 'Failed login attempt');
                    }
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

$page_title = "Login - CitizenLink";
$page_css = "auth.css";
include '../includes/header.php';
?>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your CitizenLink account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            required 
                            autocomplete="email"
                            placeholder="Enter your email address"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
            </div>
            
            <div class="auth-divider">
                <span>or</span>
            </div>
            
            <div class="social-login">
                <button type="button" class="btn btn-outline social-btn">
                    <i class="fab fa-google"></i>
                    Continue with Google
                </button>
            </div>
        </div>
        
        <div class="auth-info">
            <div class="info-content">
                <h2>Secure Access to Government Services</h2>
                <ul class="benefits-list">
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Secure Login</strong>
                            <p>Your data is protected with enterprise-grade security</p>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>24/7 Access</strong>
                            <p>Access government services anytime, anywhere</p>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-mobile-alt"></i>
                        <div>
                            <strong>Mobile Friendly</strong>
                            <p>Optimized for all devices and screen sizes</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</main>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.nextElementSibling;
    const icon = toggle.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Clear previous errors
        clearErrors();
        
        // Validate email
        if (!emailField.value.trim()) {
            showError(emailField, 'Email is required');
            isValid = false;
        } else if (!isValidEmail(emailField.value)) {
            showError(emailField, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate password
        if (!passwordField.value.trim()) {
            showError(passwordField, 'Password is required');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showError(field, message) {
        const formGroup = field.closest('.form-group');
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        formGroup.appendChild(errorElement);
        field.classList.add('error');
    }
    
    function clearErrors() {
        const errors = document.querySelectorAll('.field-error');
        errors.forEach(error => error.remove());
        
        const errorFields = document.querySelectorAll('.error');
        errorFields.forEach(field => field.classList.remove('error'));
    }
});
</script>

<?php include '../includes/footer.php'; ?>