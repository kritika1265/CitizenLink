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
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $aadhaar = sanitizeInput($_POST['aadhaar'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $termsAccepted = isset($_POST['terms']);
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($phone) || empty($aadhaar) || empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all fields.';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (!validatePhone($phone)) {
            $error = 'Please enter a valid 10-digit phone number.';
        } elseif (!validateAadhaar($aadhaar)) {
            $error = 'Please enter a valid 12-digit Aadhaar number.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!$termsAccepted) {
            $error = 'Please accept the terms and conditions.';
        } else {
            try {
                // Check if user already exists
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? OR aadhaar = ?");
                $checkStmt->execute([$email, $phone, $aadhaar]);
                
                if ($checkStmt->fetch()) {
                    $error = 'An account with this email, phone, or Aadhaar already exists.';
                } else {
                    // Create new user
                    $hashedPassword = hashPassword($password);
                    $verificationToken = bin2hex(random_bytes(32));
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO users (name, email, phone, aadhaar, password, role, status, email_verification_token, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'citizen', 'active', ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $name,
                        $email,
                        $phone,
                        $aadhaar,
                        $hashedPassword,
                        $verificationToken
                    ]);
                    
                    $userId = $pdo->lastInsertId();
                    
                    // Send verification email
                    $verificationLink = SITE_URL . "/pages/verify-email.php?token=" . $verificationToken;
                    $subject = "Verify Your CitizenLink Account";
                    $message = "
                        <h2>Welcome to CitizenLink!</h2>
                        <p>Thank you for registering with CitizenLink. Please verify your email address by clicking the link below:</p>
                        <p><a href='{$verificationLink}' style='background: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email Address</a></p>
                        <p>If you didn't create this account, please ignore this email.</p>
                        <p>Best regards,<br>CitizenLink Team</p>
                    ";
                    
                    if (sendEmail($email, $subject, $message)) {
                        logActivity($userId, 'register', 'User registered successfully');
                        $success = 'Registration successful! Please check your email to verify your account.';
                        
                        // Clear form data
                        $name = $email = $phone = $aadhaar = '';
                    } else {
                        $error = 'Registration successful, but we couldn\'t send the verification email. Please contact support.';
                    }
                }
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                $error = 'An error occurred during registration. Please try again.';
            }
        }
    }
}

$page_title = "Register - CitizenLink";
$page_css = "auth.css";
include '../includes/header.php';
?>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join CitizenLink to access government services</p>
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
                    <label for="name">Full Name</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?php echo htmlspecialchars($name ?? ''); ?>"
                            required 
                            autocomplete="name"
                            placeholder="Enter your full name"
                        >
                    </div>
                </div>
                
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
                    <label for="phone">Phone Number</label>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                            required 
                            autocomplete="tel"
                            placeholder="Enter 10-digit phone number"
                            pattern="[6-9][0-9]{9}"
                            maxlength="10"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="aadhaar">Aadhaar Number</label>
                    <div class="input-group">
                        <i class="fas fa-id-card"></i>
                        <input 
                            type="text" 
                            id="aadhaar" 
                            name="aadhaar" 
                            value="<?php echo htmlspecialchars($aadhaar ?? ''); ?>"
                            required 
                            placeholder="Enter 12-digit Aadhaar number"
                            pattern="[0-9]{12}"
                            maxlength="12"
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
                            autocomplete="new-password"
                            placeholder="Create a strong password"
                            minlength="8"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required 
                            autocomplete="new-password"
                            placeholder="Confirm your password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#terms" target="_blank">Terms and Conditions</a> and <a href="#privacy" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
        
        <div class="auth-info">
            <div class="info-content">
                <h2>Why Choose CitizenLink?</h2>
                <ul class="benefits-list">
                    <li>
                        <i class="fas fa-certificate"></i>
                        <div>
                            <strong>Digital Certificates</strong>
                            <p>Get official documents instantly</p>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <strong>Online Payments</strong>
                            <p>Pay taxes and fees securely online</p>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-tracking"></i>
                        <div>
                            <strong>Track Applications</strong>
                            <p>Monitor your application status in real-time</p>
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

// Password strength checker
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const strengthIndicator = document.getElementById('password-strength');
    
    passwordField.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        updateStrengthIndicator(strength);
    });
    
    function checkPasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            numbers: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        
        Object.values(checks).forEach(check => {
            if (check) score++;
        });
        
        if (score < 2) return 'weak';
        if (score < 4) return 'medium';
        return 'strong';
    }
    
    function updateStrengthIndicator(strength) {
        const messages = {
            weak: 'Weak password',
            medium: 'Medium strength',
            strong: 'Strong password'
        };
        
        strengthIndicator.textContent = messages[strength] || '';
        strengthIndicator.className = `password-strength ${strength}`;
    }
    
    // Form validation
    const form = document.querySelector('.auth-form');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Clear previous errors
        clearErrors();
        
        // Validate all fields
        const fields = [
            { id: 'name', message: 'Full name is required' },
            { id: 'email', message: 'Email is required', validator: isValidEmail },
            { id: 'phone', message: 'Phone number is required', validator: isValidPhone },
            { id: 'aadhaar', message: 'Aadhaar number is required', validator: isValidAadhaar },
            { id: 'password', message: 'Password is required', validator: isValidPassword },
            { id: 'confirm_password', message: 'Please confirm your password' }
        ];
        
        fields.forEach(field => {
            const element = document.getElementById(field.id);
            const value = element.value.trim();
            
            if (!value) {
                showError(element, field.message);
                isValid = false;
            } else if (field.validator && !field.validator(value)) {
                showError(element, getValidationMessage(field.id));
                isValid = false;
            }
        });
        
        // Check password match
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password && confirmPassword && password !== confirmPassword) {
            showError(document.getElementById('confirm_password'), 'Passwords do not match');
            isValid = false;
        }
        
        // Check terms acceptance
        const termsCheckbox = document.querySelector('input[name="terms"]');
        if (!termsCheckbox.checked) {
            showError(termsCheckbox, 'Please accept the terms and conditions');
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
    
    function isValidPhone(phone) {
        const phoneRegex = /^[6-9]\d{9}$/;
        return phoneRegex.test(phone);
    }
    
    function isValidAadhaar(aadhaar) {
        const aadhaarRegex = /^\d{12}$/;
        return aadhaarRegex.test(aadhaar);
    }
    
    function isValidPassword(password) {
        return password.length >= 8;
    }
    
    function getValidationMessage(fieldId) {
        const messages = {
            email: 'Please enter a valid email address',
            phone: 'Please enter a valid 10-digit phone number',
            aadhaar: 'Please enter a valid 12-digit Aadhaar number',
            password: 'Password must be at least 8 characters long'
        };
        return messages[fieldId] || 'Invalid input';
    }
    
    function showError(field, message) {
        const formGroup = field.closest('.form-group') || field.parentElement;
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