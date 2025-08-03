<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user data for pre-filling form
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_application'])) {
    try {
        $application_type = $_POST['application_type'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $date_of_birth = $_POST['date_of_birth'];
        $ssn = trim($_POST['ssn']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $zip_code = trim($_POST['zip_code']);
        $emergency_contact_name = trim($_POST['emergency_contact_name']);
        $emergency_contact_phone = trim($_POST['emergency_contact_phone']);
        $purpose = trim($_POST['purpose']);
        $additional_info = trim($_POST['additional_info']);
        
        // Generate application number
        $application_number = 'APP-' . date('Y') . '-' . sprintf('%06d', rand(100000, 999999));
        
        // Insert application into database
        $stmt = $pdo->prepare("
            INSERT INTO applications (
                user_id, application_number, application_type, first_name, last_name, 
                email, phone, date_of_birth, ssn, address, city, state, zip_code,
                emergency_contact_name, emergency_contact_phone, purpose, additional_info,
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $user_id, $application_number, $application_type, $first_name, $last_name,
            $email, $phone, $date_of_birth, $ssn, $address, $city, $state, $zip_code,
            $emergency_contact_name, $emergency_contact_phone, $purpose, $additional_info
        ]);
        
        $application_id = $pdo->lastInsertId();
        
        // Log the application submission
        logActivity($user_id, 'application_submitted', "Application {$application_number} submitted");
        
        // Send confirmation email (if email feature is enabled)
        if (FEATURE_EMAIL_VERIFICATION) {
            sendApplicationConfirmationEmail($email, $application_number, $first_name);
        }
        
        $message = "Application submitted successfully! Your application number is: <strong>{$application_number}</strong>";
        
    } catch (PDOException $e) {
        $error = "Error submitting application: " . $e->getMessage();
    }
}

$page_title = "New Application";
include '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-file-alt"></i> Government Services Application</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="../profile.php">Profile</a></li>
                    <li class="breadcrumb-item active">New Application</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Success!</strong> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <hr>
            <p class="mb-0">
                <a href="service-status.php" class="btn btn-info btn-sm">Check Application Status</a>
                <a href="../dashboard.php" class="btn btn-secondary btn-sm">Return to Dashboard</a>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$message): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-form"></i> Application Form</h5>
                    <small class="text-muted">Please fill out all required fields marked with *</small>
                </div>
                <div class="card-body">
                    <form method="POST" id="applicationForm" novalidate>
                        <input type="hidden" name="submit_application" value="1">
                        
                        <!-- Application Type Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-clipboard-list"></i> Application Type</h6>
                                <hr>
                            </div>
                            <div class="col-md-12">
                                <label for="application_type" class="form-label">Select Service Type *</label>
                                <select class="form-select" id="application_type" name="application_type" required>
                                    <option value="">Choose a service...</option>
                                    <option value="passport">Passport Application</option>
                                    <option value="drivers_license">Driver's License</option>
                                    <option value="birth_certificate">Birth Certificate</option>
                                    <option value="marriage_certificate">Marriage Certificate</option>
                                    <option value="business_license">Business License</option>
                                    <option value="property_permit">Property Permit</option>
                                    <option value="tax_documents">Tax Documents</option>
                                    <option value="social_services">Social Services</option>
                                    <option value="other">Other Services</option>
                                </select>
                                <div class="invalid-feedback">Please select an application type.</div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-user"></i> Personal Information</h6>
                                <hr>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please enter your phone number.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth *</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                <div class="invalid-feedback">Please enter your date of birth.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ssn" class="form-label">Social Security Number *</label>
                                <input type="text" class="form-control" id="ssn" name="ssn" 
                                       placeholder="XXX-XX-XXXX" maxlength="11" required>
                                <div class="invalid-feedback">Please enter your SSN in XXX-XX-XXXX format.</div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Address Information</h6>
                                <hr>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">Street Address *</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please enter your street address.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please enter your city.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State *</label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="">Select State</option>
                                    <option value="AL" <?php echo ($user['state'] === 'AL') ? 'selected' : ''; ?>>Alabama</option>
                                    <option value="AK" <?php echo ($user['state'] === 'AK') ? 'selected' : ''; ?>>Alaska</option>
                                    <option value="AZ" <?php echo ($user['state'] === 'AZ') ? 'selected' : ''; ?>>Arizona</option>
                                    <option value="AR" <?php echo ($user['state'] === 'AR') ? 'selected' : ''; ?>>Arkansas</option>
                                    <option value="CA" <?php echo ($user['state'] === 'CA') ? 'selected' : ''; ?>>California</option>
                                    <option value="CO" <?php echo ($user['state'] === 'CO') ? 'selected' : ''; ?>>Colorado</option>
                                    <option value="CT" <?php echo ($user['state'] === 'CT') ? 'selected' : ''; ?>>Connecticut</option>
                                    <option value="DE" <?php echo ($user['state'] === 'DE') ? 'selected' : ''; ?>>Delaware</option>
                                    <option value="FL" <?php echo ($user['state'] === 'FL') ? 'selected' : ''; ?>>Florida</option>
                                    <option value="GA" <?php echo ($user['state'] === 'GA') ? 'selected' : ''; ?>>Georgia</option>
                                    <!-- Add more states as needed -->
                                </select>
                                <div class="invalid-feedback">Please select your state.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="zip_code" class="form-label">ZIP Code *</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                       value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" 
                                       pattern="[0-9]{5}(-[0-9]{4})?" required>
                                <div class="invalid-feedback">Please enter a valid ZIP code.</div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-phone"></i> Emergency Contact</h6>
                                <hr>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_name" class="form-label">Emergency Contact Name *</label>
                                <input type="text" class="form-control" id="emergency_contact_name" 
                                       name="emergency_contact_name" required>
                                <div class="invalid-feedback">Please enter emergency contact name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone *</label>
                                <input type="tel" class="form-control" id="emergency_contact_phone" 
                                       name="emergency_contact_phone" required>
                                <div class="invalid-feedback">Please enter emergency contact phone.</div>
                            </div>
                        </div>

                        <!-- Application Details -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-info-circle"></i> Application Details</h6>
                                <hr>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose of Application *</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" 
                                          placeholder="Please describe the purpose of your application..." required></textarea>
                                <div class="invalid-feedback">Please describe the purpose of your application.</div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="additional_info" class="form-label">Additional Information</label>
                                <textarea class="form-control" id="additional_info" name="additional_info" rows="3" 
                                          placeholder="Any additional information or special requirements..."></textarea>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms_agreement" required>
                                    <label class="form-check-label" for="terms_agreement">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> 
                                        and certify that all information provided is accurate and complete. *
                                    </label>
                                    <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Submit Application
                                </button>
                                <a href="../dashboard.php" class="btn btn-secondary btn-lg ms-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Application Terms</h6>
                <p>By submitting this application, you agree to the following terms:</p>
                <ul>
                    <li>All information provided is accurate and complete to the best of your knowledge.</li>
                    <li>You understand that providing false information may result in application denial or legal consequences.</li>
                    <li>You consent to background checks and verification of provided information.</li>
                    <li>Processing fees are non-refundable once payment is made.</li>
                    <li>Application processing times may vary based on service type and current workload.</li>
                    <li>You will be notified of application status changes via email or phone.</li>
                </ul>
                
                <h6>Privacy Policy</h6>
                <p>Your personal information will be:</p>
                <ul>
                    <li>Used solely for processing your application and providing requested services.</li>
                    <li>Protected according to government privacy standards.</li>
                    <li>Not shared with third parties without your consent, except as required by law.</li>
                    <li>Retained according to government record retention policies.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('applicationForm');
    
    // SSN formatting
    const ssnInput = document.getElementById('ssn');
    ssnInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 6) {
            value = value.substring(0,3) + '-' + value.substring(3,5) + '-' + value.substring(5,9);
        } else if (value.length >= 4) {
            value = value.substring(0,3) + '-' + value.substring(3);
        }
        e.target.value = value;
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 7) {
                value = '(' + value.substring(0,3) + ') ' + value.substring(3,6) + '-' + value.substring(6,10);
            } else if (value.length >= 4) {
                value = '(' + value.substring(0,3) + ') ' + value.substring(3);
            } else if (value.length >= 1) {
                value = '(' + value;
            }
            e.target.value = value;
        });
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
    
    // Application type change
    const appTypeSelect = document.getElementById('application_type');
    appTypeSelect.addEventListener('change', function() {
        const purposeField = document.getElementById('purpose');
        const selectedType = this.options[this.selectedIndex].text;
        
        if (this.value && this.value !== 'other') {
            purposeField.placeholder = `Please describe why you need ${selectedType}...`;
        } else if (this.value === 'other') {
            purposeField.placeholder = 'Please describe the service you need and its purpose...';
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>