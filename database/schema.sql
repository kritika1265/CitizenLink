-- CitizenLink Database
CREATE DATABASE IF NOT EXISTS citizenlink_db;
USE citizenlink_db;

-- Citizens Table (Extended User Table)
CREATE TABLE citizens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    citizen_id VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    date_of_birth DATE,
    national_id VARCHAR(20) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin Users Table
CREATE TABLE admin_users (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'officer') DEFAULT 'officer',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services Table
CREATE TABLE services (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(100) NOT NULL,
    service_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    required_documents TEXT,
    processing_time VARCHAR(50),
    fees DECIMAL(10,2) DEFAULT 0.00,
    form_fields JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Applications Table
CREATE TABLE applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    application_number VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected', 'completed') DEFAULT 'draft',
    submitted_data JSON,
    admin_notes TEXT,
    rejection_reason TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT NULL,
    submission_date TIMESTAMP NULL,
    completion_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES admin_users(admin_id) ON DELETE SET NULL
);

-- Legacy Applications Table (extended form)
CREATE TABLE legacy_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    application_number VARCHAR(20) UNIQUE,
    application_type VARCHAR(50),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    date_of_birth DATE,
    ssn VARCHAR(11),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(2),
    zip_code VARCHAR(10),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    purpose TEXT,
    additional_info TEXT,
    status ENUM('pending', 'processing', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Application Status History Table
CREATE TABLE application_status_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    old_status VARCHAR(20),
    new_status VARCHAR(20) NOT NULL,
    changed_by INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL
);

-- Documents Table
CREATE TABLE documents (
    document_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE
);

-- User Documents Table
CREATE TABLE user_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    application_id INT NULL,
    document_name VARCHAR(255),
    document_type VARCHAR(100),
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    file_size BIGINT,
    mime_type VARCHAR(100),
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verification_notes TEXT,
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL
);

-- Notifications Table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    application_id INT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE SET NULL
);

-- System Settings Table
CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default Services
INSERT INTO services (service_name, service_code, description, category, required_documents, processing_time, fees, form_fields) VALUES
('National ID Renewal', 'NID_RENEWAL', 'Renew your national identification card', 'Identity Services', 'Current ID, Photo, Proof of Address', '5-7 business days', 25.00, '{"fields": [{"name": "current_id", "type": "text", "required": true, "label": "Current ID Number"}, {"name": "reason", "type": "select", "required": true, "label": "Reason for Renewal", "options": ["Expired", "Lost", "Damaged"]}]}'),
('Passport Application', 'PASSPORT_APP', 'Apply for a new passport', 'Identity Services', 'Birth Certificate, Photo, Proof of Citizenship', '15-20 business days', 120.00, '{"fields": [{"name": "passport_type", "type": "select", "required": true, "label": "Passport Type", "options": ["Regular", "Official", "Diplomatic"]}, {"name": "pages", "type": "select", "required": true, "label": "Number of Pages", "options": ["32", "64"]}]}'),
('Business License', 'BUS_LICENSE', 'Apply for business operating license', 'Permits & Licenses', 'Business Plan, Tax Registration, Proof of Address', '10-15 business days', 75.00, '{"fields": [{"name": "business_name", "type": "text", "required": true, "label": "Business Name"}, {"name": "business_type", "type": "select", "required": true, "label": "Business Type", "options": ["Retail", "Service", "Manufacturing", "Technology"]}]}'),
('Tax Certificate', 'TAX_CERT', 'Request tax compliance certificate', 'Tax Services', 'Tax Returns, Payment Receipts', '3-5 business days', 15.00, '{"fields": [{"name": "tax_year", "type": "select", "required": true, "label": "Tax Year", "options": ["2024", "2023", "2022"]}, {"name": "certificate_type", "type": "select", "required": true, "label": "Certificate Type", "options": ["Individual", "Business"]}]}'),
('Driving License Renewal', 'DL_RENEWAL', 'Renew your driving license', 'Transportation', 'Current License, Medical Certificate, Photo', '7-10 business days', 50.00, '{"fields": [{"name": "license_class", "type": "select", "required": true, "label": "License Class", "options": ["A", "B", "C", "D"]}, {"name": "medical_cert", "type": "file", "required": true, "label": "Medical Certificate"}]}');

-- Default Admin
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@citizenlink.gov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

-- Default Settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'CitizenLink', 'Website name'),
('site_email', 'info@citizenlink.gov', 'Contact email'),
('max_file_size', '5242880', 'Maximum file upload size in bytes (5MB)'),
('allowed_file_types', 'pdf,jpg,jpeg,png,doc,docx', 'Allowed file extensions'),
('maintenance_mode', '0', 'Maintenance mode (0=off, 1=on)');

-- Indexes
CREATE INDEX idx_applications_user_id ON applications(user_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_service_id ON applications(service_id);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_documents_application_id ON documents(application_id);
CREATE INDEX idx_services_category ON services(category);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_national_id ON users(national_id);
CREATE INDEX idx_user_documents_user_id ON user_documents(user_id);
CREATE INDEX idx_user_documents_application_id ON user_documents(application_id);
CREATE INDEX idx_user_documents_status ON user_documents(status);
CREATE INDEX idx_user_documents_type ON user_documents(document_type);
