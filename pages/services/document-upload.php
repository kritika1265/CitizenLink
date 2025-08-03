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

// Create upload directories if they don't exist
$upload_dir = UPLOAD_PATH . 'documents/';
$user_upload_dir = $upload_dir . $user_id . '/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!file_exists($user_upload_dir)) {
    mkdir($user_upload_dir, 0755, true);
}

// Get database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_document'])) {
    $document_type = $_POST['document_type'];
    $document_description = trim($_POST['document_description']);
    $application_id = !empty($_POST['application_id']) ? $_POST['application_id'] : null;
    
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
        $file = $_FILES['document_file'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file
        $allowed_extensions = ALLOWED_FILE_TYPES;
        $max_size = MAX_FILE_SIZE;
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
        } elseif ($file_size > $max_size) {
            $error = "File size too large. Maximum size: " . formatBytes($max_size);
        } else {
            // Generate unique filename
            $unique_filename = uniqid() . '_' . time() . '.' . $file_ext;
            $file_path = $user_upload_dir . $unique_filename;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                try {
                    // Insert document record
                    $stmt = $pdo->prepare("
                        INSERT INTO user_documents (
                            user_id, application_id, document_name, document_type, 
                            file_name, file_path, file_size, description, status, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                    ");
                    
                    $stmt->execute([
                        $user_id, $application_id, $file_name, $document_type,
                        $unique_filename, $file_path, $file_size, $document_description
                    ]);
                    
                    // Log activity
                    logActivity($user_id, 'document_uploaded', "Document uploaded: {$file_name}");
                    
                    $message = "Document uploaded successfully! It will be reviewed within 1-2 business days.";
                    
                } catch (PDOException $e) {
                    unlink($file_path); // Remove uploaded file on database error
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to upload file. Please try again.";
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }
}

// Handle document deletion
if (isset($_POST['delete_document'])) {
    $document_id = $_POST['document_id'];
    
    try {
        // Get document info
        $stmt = $pdo->prepare("SELECT * FROM user_documents WHERE id = ? AND user_id = ?");
        $stmt->execute([$document_id, $user_id]);
        $document = $stmt->fetch();
        
        if ($document) {
            // Delete file
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            // Delete database record
            $stmt = $pdo->prepare("DELETE FROM user_documents WHERE id = ? AND user_id = ?");
            $stmt->execute([$document_id, $user_id]);
            
            logActivity($user_id, 'document_deleted', "Document deleted: {$document['document_name']}");
            $message = "Document deleted successfully.";
        }
    } catch (PDOException $e) {
        $error = "Error deleting document: " . $e->getMessage();
    }
}

// Get user's applications for dropdown
$applications = [];
try {
    $stmt = $pdo->prepare("SELECT id, application_number, application_type FROM applications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Continue without applications dropdown
}

// Get user's uploaded documents
$documents = [];
try {
    $stmt = $pdo->prepare("
        SELECT d.*, a.application_number 
        FROM user_documents d 
        LEFT JOIN applications a ON d.application_id = a.id 
        WHERE d.user_id = ? 
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading documents: " . $e->getMessage();
}

$page_title = "Document Upload";
include '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-upload"></i> Document Upload Center</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="../profile.php">Profile</a></li>
                    <li class="breadcrumb-item active">Document Upload</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Success!</strong> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Upload Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cloud-upload-alt"></i> Upload New Document</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="upload_document" value="1">
                        
                        <!-- Document Type -->
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type *</label>
                            <select class="form-select" id="document_type" name="document_type" required>
                                <option value="">Select document type...</option>
                                <option value="identification">Government Issued ID</option>
                                <option value="proof_of_address">Proof of Address</option>
                                <option value="birth_certificate">Birth Certificate</option>
                                <option value="marriage_certificate">Marriage Certificate</option>
                                <option value="passport">Passport</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="social_security">Social Security Card</option>
                                <option value="utility_bill">Utility Bill</option>
                                <option value="bank_statement">Bank Statement</option>
                                <option value="tax_document">Tax Document</option>
                                <option value="employment_verification">Employment Verification</option>
                                <option value="medical_record">Medical Record</option>
                                <option value="legal_document">Legal Document</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Application Association -->
                        <?php if (!empty($applications)): ?>
                        <div class="mb-3">
                            <label for="application_id" class="form-label">Associated Application (Optional)</label>
                            <select class="form-select" id="application_id" name="application_id">
                                <option value="">Not associated with any application</option>
                                <?php foreach ($applications as $app): ?>
                                    <option value="<?php echo $app['id']; ?>">
                                        <?php echo htmlspecialchars($app['application_number'] . ' - ' . ucfirst(str_replace('_', ' ', $app['application_type']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Document Description -->
                        <div class="mb-3">
                            <label for="document_description" class="form-label">Document Description</label>
                            <textarea class="form-control" id="document_description" name="document_description" 
                                      rows="3" placeholder="Brief description of the document (optional)"></textarea>
                        </div>

                        <!-- File Upload Area -->
                        <div class="mb-3">
                            <label for="document_file" class="form-label">Select Document *</label>
                            <div class="upload-area" id="uploadArea">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                                    <h5>Drag and drop your file here</h5>
                                    <p class="text-muted">or click to browse</p>
                                    <input type="file" class="form-control" id="document_file" name="document_file" 
                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required style="display: none;">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('document_file').click();">
                                        Browse Files
                                    </button>
                                </div>
                                <div class="upload-info mt-3">
                                    <small class="text-muted">
                                        <strong>Allowed formats:</strong> <?php echo implode(', ', ALLOWED_FILE_TYPES); ?><br>
                                        <strong>Maximum size:</strong> <?php echo formatBytes(MAX_FILE_SIZE); ?>
                                    </small>
                                </div>
                            </div>
                            <div id="fileInfo" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-file"></i> <span id="fileName"></span>
                                    <span class="badge bg-secondary ms-2" id="fileSize"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary" id="uploadBtn">
                                <i class="fas fa-upload"></i> Upload Document
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Upload Guidelines -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle"></i> Upload Guidelines</h6>
                </div>
                <div class="card-body">
                    <h6>Document Requirements:</h6>
                    <ul class="small">
                        <li>Documents must be clear and legible</li>
                        <li>All text must be visible and readable</li>
                        <li>Photos should be well-lit with no shadows</li>
                        <li>Scanned documents preferred over photos</li>
                        <li>Original documents or certified copies only</li>
                    </ul>
                    
                    <h6>File Specifications:</h6>
                    <ul class="small">
                        <li><strong>Formats:</strong> PDF, JPG, PNG, DOC, DOCX</li>
                        <li><strong>Max Size:</strong> <?php echo formatBytes(MAX_FILE_SIZE); ?></li>
                        <li><strong>Resolution:</strong> Minimum 300 DPI</li>
                        <li><strong>Color:</strong> Color or high-quality grayscale</li>
                    </ul>

                    <h6>Processing Time:</h6>
                    <ul class="small">
                        <li>Review within 1-2 business days</li>
                        <li>Email notification of status changes</li>
                        <li>Rejected documents require re-upload</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-chart-bar"></i> Your Document Stats</h6>
                </div>
                <div class="card-body">
                    <?php
                    $stats = [
                        'total' => count($documents),
                        'pending' => count(array_filter($documents, fn($d) => $d['status'] === 'pending')),
                        'approved' => count(array_filter($documents, fn($d) => $d['status'] === 'approved')),
                        'rejected' => count(array_filter($documents, fn($d) => $d['status'] === 'rejected'))
                    ];
                    ?>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary"><?php echo $stats['total']; ?></h4>
                            <small>Total</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning"><?php echo $stats['pending']; ?></h4>
                            <small>Pending</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success"><?php echo $stats['approved']; ?></h4>
                            <small>Approved</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger"><?php echo $stats['rejected']; ?></h4>
                            <small>Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Uploaded Documents List -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-folder-open"></i> My Documents (<?php echo count($documents); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($documents)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5>No documents uploaded yet</h5>
                            <p class="text-muted">Upload your first document using the form above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Application</th>
                                        <th>Upload Date</th>
                                        <th>Status</th>
                                        <th>Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-<?php echo getFileIcon($doc['file_name']); ?> me-2"></i>
                                                <?php echo htmlspecialchars($doc['document_name']); ?>
                                                <?php if ($doc['description']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($doc['description']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['document_type']))); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($doc['application_number']): ?>
                                                    <a href="service-status.php?app=<?php echo $doc['application_number']; ?>">
                                                        <?php echo htmlspecialchars($doc['application_number']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($doc['created_at'])); ?></td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success', 
                                                    'rejected' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$doc['status']] ?? 'secondary'; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($doc['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatBytes($doc['file_size']); ?></td>
                                            <td>
                                                <a href="download-document.php?id=<?php echo $doc['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <?php if ($doc['status'] === 'pending'): ?>
                                                    <form method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                        <input type="hidden" name="delete_document" value="1">
                                                        <input type="hidden" name="document_id" value="<?php echo $doc['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.upload-area.dragover {
    border-color: #007bff;
    background-color: #e3f2fd;
    transform: scale(1.02);
}

.upload-content h5 {
    color: #6c757d;
    margin-bottom: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('document_file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            showFileInfo(files[0]);
        }
    });
    
    // Click to browse
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            showFileInfo(this.files[0]);
        }
    });
    
    function showFileInfo(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatBytes(file.size);
        fileInfo.style.display = 'block';
        
        // Validate file
        const allowedTypes = <?php echo json_encode(ALLOWED_FILE_TYPES); ?>;
        const maxSize = <?php echo MAX_FILE_SIZE; ?>;
        const fileExt = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(fileExt)) {
            alert('Invalid file type. Allowed types: ' + allowedTypes.join(', '));
            resetForm();
            return;
        }
        
        if (file.size > maxSize) {
            alert('File size too large. Maximum size: ' + formatBytes(maxSize));
            resetForm();
            return;
        }
    }
    
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Make formatBytes available globally
    window.formatBytes = formatBytes;
});

function resetForm() {
    document.getElementById('uploadForm').reset();
    document.getElementById('fileInfo').style.display = 'none';
}
</script>

<?php include '../../includes/footer.php'; ?>