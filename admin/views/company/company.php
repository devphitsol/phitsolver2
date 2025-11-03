<?php
// Company Profile Edit Page
$pageTitle = 'Update Company Profile';
$currentAction = 'company';

// Get user ID from URL parameter
$userId = $_GET['id'] ?? null;

if (!$userId) {
    $_SESSION['error'] = 'Company ID is required.';
    header('Location: index.php?action=company');
    exit;
}

// Get user data
$userController = new \App\Controllers\UserController();
$user = $userController->getUserById($userId);

if (!$user || $user['role'] !== 'business') {
    $_SESSION['error'] = 'Company not found or invalid company.';
    header('Location: index.php?action=company');
    exit;
}

// Sample company data (실제로는 데이터베이스에서 가져와야 함)
$companyData = [
                'company_name' => $user['company_name'] ?? $user['name'],
    'company_address' => $user['company_address'] ?? '123 Business Street, Makati City, Philippines',
    'date_of_incorporation' => $user['date_of_incorporation'] ?? '2020-01-15',
    'tin_number' => $user['tin_number'] ?? '123-456-789-000',
    'business_permit' => $user['business_permit'] ?? 'BP-2024-001234',
    'email_address' => $user['email'],
    'contact_number' => $user['contact_number'] ?? '+63 2 1234 5678',
    'website_url' => $user['website_url'] ?? 'https://www.phitsol.com'
];

$contactData = [
                'authorized_representative' => $user['name'],
    'position_title' => $user['position'] ?? 'Chief Executive Officer',
    'representative_contact' => $user['contact_number'] ?? '+63 917 123 4567',
    'representative_email' => $user['email'],
    'secondary_contact_name' => $user['secondary_contact_name'] ?? 'Jane Smith',
    'secondary_contact_position' => $user['secondary_contact_position'] ?? 'Chief Operating Officer',
    'secondary_contact_number' => $user['secondary_contact_number'] ?? '+63 917 987 6543',
    'secondary_contact_email' => $user['secondary_contact_email'] ?? 'jane.smith@phitsol.com'
];

$documents = [
    'company_profile' => $user['documents']['company_profile'] ?? false,
    'business_permit' => $user['documents']['business_permit'] ?? false,
    'bir_2303' => $user['documents']['bir_2303'] ?? false,
    'gis' => $user['documents']['gis'] ?? false,
    'audited_financial' => $user['documents']['audited_financial'] ?? false,
    'proof_of_payment' => $user['documents']['proof_of_payment'] ?? false,
    'valid_id' => $user['documents']['valid_id'] ?? false,
    'corporate_secretary' => $user['documents']['corporate_secretary'] ?? false,
    'credit_investigation' => $user['documents']['credit_investigation'] ?? false,
    'peza_certification' => $user['documents']['peza_certification'] ?? false
];
?>

<!-- Enhanced Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-building"></i>
            Company Profile Management
        </h1>
        <div class="stats-info">
            <span class="stat-item">Managing profile for: <strong><?php echo htmlspecialchars($companyData['company_name']); ?></strong></span>
        </div>
    </div>
    <div class="header-actions">
        <a href="index.php?action=company" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Companies
        </a>
    </div>
</div>

<!-- Enhanced Content Body -->
<div class="content-body">
    <div class="company-profile-container">
        
        <!-- Company Profile Section -->
        <div class="profile-section">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-building"></i>
                        <h3>Company Information</h3>
                    </div>
                    <div class="section-actions">
                        <button type="button" class="btn btn-edit" id="updateCompanyBtn">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </button>
                    </div>
                </div>
                
                <div class="section-content">
                    <form id="companyProfileForm" class="enhanced-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="company_name" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Company Name <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="<?php echo htmlspecialchars($companyData['company_name']); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="date_of_incorporation" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Date of Incorporation <span class="required">*</span>
                                </label>
                                <input type="date" class="form-control" id="date_of_incorporation" name="date_of_incorporation" 
                                       value="<?php echo $companyData['date_of_incorporation']; ?>" readonly>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="company_address" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Company Address <span class="required">*</span>
                                </label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="3" readonly><?php echo htmlspecialchars($companyData['company_address']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="tin_number" class="form-label">
                                    <i class="fas fa-id-card"></i>
                                    TIN / Tax ID Number <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="tin_number" name="tin_number" 
                                       value="<?php echo htmlspecialchars($companyData['tin_number']); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="business_permit" class="form-label">
                                    <i class="fas fa-file-contract"></i>
                                    Business Permit Number <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="business_permit" name="business_permit" 
                                       value="<?php echo htmlspecialchars($companyData['business_permit']); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="email_address" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address <span class="required">*</span>
                                </label>
                                <input type="email" class="form-control" id="email_address" name="email_address" 
                                       value="<?php echo htmlspecialchars($companyData['email_address']); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_number" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Contact Number <span class="required">*</span>
                                </label>
                                <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                       value="<?php echo htmlspecialchars($companyData['contact_number']); ?>" readonly>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="website_url" class="form-label">
                                    <i class="fas fa-globe"></i>
                                    Website URL
                                </label>
                                <input type="url" class="form-control" id="website_url" name="website_url" 
                                       value="<?php echo htmlspecialchars($companyData['website_url']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-actions" id="companyFormActions" style="display: none;">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <button type="button" class="btn btn-cancel" id="cancelCompanyBtn">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Contact Persons Section -->
        <div class="profile-section">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-users"></i>
                        <h3>Contact Information</h3>
                    </div>
                    <div class="section-actions">
                        <button type="button" class="btn btn-edit" id="updateContactBtn">
                            <i class="fas fa-edit"></i>
                            Edit Contacts
                        </button>
                    </div>
                </div>
                
                <div class="section-content">
                    <form id="contactPersonsForm" class="enhanced-form">
                        <!-- Primary Contact -->
                        <div class="contact-group">
                            <div class="contact-header">
                                <i class="fas fa-user-tie"></i>
                                <h4>Primary Contact Person</h4>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="authorized_representative" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Authorized Representative <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="authorized_representative" name="authorized_representative" 
                                           value="<?php echo htmlspecialchars($contactData['authorized_representative']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="position_title" class="form-label">
                                        <i class="fas fa-briefcase"></i>
                                        Position/Title <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="position_title" name="position_title" 
                                           value="<?php echo htmlspecialchars($contactData['position_title']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="representative_contact" class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Contact Number <span class="required">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="representative_contact" name="representative_contact" 
                                           value="<?php echo htmlspecialchars($contactData['representative_contact']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="representative_email" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address <span class="required">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="representative_email" name="representative_email" 
                                           value="<?php echo htmlspecialchars($contactData['representative_email']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Secondary Contact -->
                        <div class="contact-group">
                            <div class="contact-header">
                                <i class="fas fa-user"></i>
                                <h4>Secondary Contact Person</h4>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="secondary_contact_name" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Contact Person Name
                                    </label>
                                    <input type="text" class="form-control" id="secondary_contact_name" name="secondary_contact_name" 
                                           value="<?php echo htmlspecialchars($contactData['secondary_contact_name']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="secondary_contact_position" class="form-label">
                                        <i class="fas fa-briefcase"></i>
                                        Position/Title
                                    </label>
                                    <input type="text" class="form-control" id="secondary_contact_position" name="secondary_contact_position" 
                                           value="<?php echo htmlspecialchars($contactData['secondary_contact_position']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="secondary_contact_number" class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Contact Number
                                    </label>
                                    <input type="tel" class="form-control" id="secondary_contact_number" name="secondary_contact_number" 
                                           value="<?php echo htmlspecialchars($contactData['secondary_contact_number']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="secondary_contact_email" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" class="form-control" id="secondary_contact_email" name="secondary_contact_email" 
                                           value="<?php echo htmlspecialchars($contactData['secondary_contact_email']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions" id="contactFormActions" style="display: none;">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <button type="button" class="btn btn-cancel" id="cancelContactBtn">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Documents Section -->
        <div class="profile-section">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-file-alt"></i>
                        <h3>Required Documents</h3>
                    </div>
                    <div class="section-actions">
                        <button type="button" class="btn btn-edit" id="updateDocumentsBtn">
                            <i class="fas fa-edit"></i>
                            Edit Documents
                        </button>
                    </div>
                </div>
                
                <div class="section-content">
                    <form id="documentsForm" class="enhanced-form">
                        <div class="documents-grid">
                            <div class="documents-column">
                                <div class="documents-header">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <h4>Required Documents</h4>
                                </div>
                                
                                <div class="document-list">
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="company_profile" name="company_profile" 
                                               <?php echo $documents['company_profile'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="company_profile">
                                            <i class="fas fa-file-text"></i>
                                            Company Profile <span class="required">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="business_permit" name="business_permit" 
                                               <?php echo $documents['business_permit'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="business_permit">
                                            <i class="fas fa-file-contract"></i>
                                            Mayor or Business Permit <span class="required">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="bir_2303" name="bir_2303" 
                                               <?php echo $documents['bir_2303'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="bir_2303">
                                            <i class="fas fa-file-invoice"></i>
                                            BIR 2303 <span class="required">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="gis" name="gis" 
                                               <?php echo $documents['gis'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="gis">
                                            <i class="fas fa-file-alt"></i>
                                            GIS <span class="required">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="audited_financial" name="audited_financial" 
                                               <?php echo $documents['audited_financial'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="audited_financial">
                                            <i class="fas fa-chart-line"></i>
                                            Audited Financial Statement <span class="required">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="proof_of_payment" name="proof_of_payment" 
                                               <?php echo $documents['proof_of_payment'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="proof_of_payment">
                                            <i class="fas fa-receipt"></i>
                                            Proof of Payment (3 Months Office Address) <span class="required">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="valid_id" name="valid_id" 
                                               <?php echo $documents['valid_id'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="valid_id">
                                            <i class="fas fa-id-card"></i>
                                            Valid ID of Authorized Person <span class="required">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="corporate_secretary" name="corporate_secretary" 
                                               <?php echo $documents['corporate_secretary'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="corporate_secretary">
                                            <i class="fas fa-stamp"></i>
                                            Corporate Secretary Certificate <span class="required">*</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="documents-column">
                                <div class="documents-header">
                                    <i class="fas fa-info-circle"></i>
                                    <h4>Optional Documents</h4>
                                </div>
                                
                                <div class="document-list">
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="credit_investigation" name="credit_investigation" 
                                               <?php echo $documents['credit_investigation'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="credit_investigation">
                                            <i class="fas fa-search"></i>
                                            Credit Investigation Form
                                        </label>
                                    </div>
                                    
                                    <div class="document-item">
                                        <input class="form-check-input" type="checkbox" id="peza_certification" name="peza_certification" 
                                               <?php echo $documents['peza_certification'] ? 'checked' : ''; ?> disabled>
                                        <label class="form-check-label" for="peza_certification">
                                            <i class="fas fa-certificate"></i>
                                            PEZA Certification (if Zero Rated Tax)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions" id="documentsFormActions" style="display: none;">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <button type="button" class="btn btn-cancel" id="cancelDocumentsBtn">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ✅ Successfully updated
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store original values for restoration
    let originalCompanyData = {};
    let originalContactData = {};
    let originalDocumentData = {};
    
    // Company Profile Form
    const updateCompanyBtn = document.getElementById('updateCompanyBtn');
    const companyFormActions = document.getElementById('companyFormActions');
    const cancelCompanyBtn = document.getElementById('cancelCompanyBtn');
    const companyForm = document.getElementById('companyProfileForm');
    const companyInputs = companyForm.querySelectorAll('input, textarea');
    
    // Store original values
    companyInputs.forEach(input => {
        originalCompanyData[input.name] = input.value;
    });
    
    updateCompanyBtn.addEventListener('click', function() {
        companyInputs.forEach(input => {
            input.readOnly = false;
            input.classList.add('form-control-active');
        });
        companyFormActions.style.display = 'flex';
        updateCompanyBtn.style.display = 'none';
    });
    
    cancelCompanyBtn.addEventListener('click', function() {
        // Restore original values
        companyInputs.forEach(input => {
            input.value = originalCompanyData[input.name] || '';
            input.readOnly = true;
            input.classList.remove('form-control-active');
        });
        companyFormActions.style.display = 'none';
        updateCompanyBtn.style.display = 'block';
    });
    
    companyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        
        
        // Collect form data
        const formData = new FormData();
        formData.append('action', 'update_company_profile');
        formData.append('user_id', '<?php echo $userId; ?>');
        
        companyInputs.forEach(input => {
            formData.append(input.name, input.value);
            
        });
        
        
        
        // Send data to server
        fetch('index.php?action=company&method=update_company_profile', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            
            try {
                const data = JSON.parse(text);
                
                
                if (data.success) {
                    showSuccessToast('Company profile updated successfully');
                    
                    // Update original values
                    companyInputs.forEach(input => {
                        originalCompanyData[input.name] = input.value;
                    });
                    
                    // Reset form to read-only
                    companyInputs.forEach(input => {
                        input.readOnly = true;
                        input.classList.remove('form-control-active');
                    });
                    companyFormActions.style.display = 'none';
                    updateCompanyBtn.style.display = 'block';
                } else {
                    alert('Error: ' + (data.message || 'Failed to save company profile'));
                }
            } catch (error) {
                
                alert('Error: Invalid response from server');
            }
        })
        .catch(error => {
            
            alert('Error: Failed to save company profile - ' + error.message);
        });
    });
    
    // Contact Persons Form
    const updateContactBtn = document.getElementById('updateContactBtn');
    const contactFormActions = document.getElementById('contactFormActions');
    const cancelContactBtn = document.getElementById('cancelContactBtn');
    const contactForm = document.getElementById('contactPersonsForm');
    const contactInputs = contactForm.querySelectorAll('input');
    
    // Store original values
    contactInputs.forEach(input => {
        originalContactData[input.name] = input.value;
    });
    
    updateContactBtn.addEventListener('click', function() {
        contactInputs.forEach(input => {
            input.readOnly = false;
            input.classList.add('form-control-active');
        });
        contactFormActions.style.display = 'flex';
        updateContactBtn.style.display = 'none';
    });
    
    cancelContactBtn.addEventListener('click', function() {
        // Restore original values
        contactInputs.forEach(input => {
            input.value = originalContactData[input.name] || '';
            input.readOnly = true;
            input.classList.remove('form-control-active');
        });
        contactFormActions.style.display = 'none';
        updateContactBtn.style.display = 'block';
    });
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        
        
        // Collect form data
        const formData = new FormData();
        formData.append('action', 'update_contact_persons');
        formData.append('user_id', '<?php echo $userId; ?>');
        
        contactInputs.forEach(input => {
            formData.append(input.name, input.value);
            
        });
        
        
        
        // Send data to server
        fetch('index.php?action=company&method=update_contact_persons', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            
            try {
                const data = JSON.parse(text);
                
                
                if (data.success) {
                    showSuccessToast('Contact information updated successfully');
                    
                    // Update original values
                    contactInputs.forEach(input => {
                        originalContactData[input.name] = input.value;
                    });
                    
                    // Reset form to read-only
                    contactInputs.forEach(input => {
                        input.readOnly = true;
                        input.classList.remove('form-control-active');
                    });
                    contactFormActions.style.display = 'none';
                    updateContactBtn.style.display = 'block';
                } else {
                    alert('Error: ' + (data.message || 'Failed to save contact information'));
                }
            } catch (error) {
                
                alert('Error: Invalid response from server');
            }
        })
        .catch(error => {
            
            alert('Error: Failed to save contact information - ' + error.message);
        });
    });
    
    // Documents Form
    const updateDocumentsBtn = document.getElementById('updateDocumentsBtn');
    const documentsFormActions = document.getElementById('documentsFormActions');
    const cancelDocumentsBtn = document.getElementById('cancelDocumentsBtn');
    const documentsForm = document.getElementById('documentsForm');
    const documentCheckboxes = documentsForm.querySelectorAll('input[type="checkbox"]');
    
    // Store original values
    documentCheckboxes.forEach(checkbox => {
        originalDocumentData[checkbox.name] = checkbox.checked;
    });
    
    updateDocumentsBtn.addEventListener('click', function() {
        documentCheckboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
        documentsFormActions.style.display = 'flex';
        updateDocumentsBtn.style.display = 'none';
    });
    
    cancelDocumentsBtn.addEventListener('click', function() {
        // Restore original values
        documentCheckboxes.forEach(checkbox => {
            checkbox.checked = originalDocumentData[checkbox.name] || false;
            checkbox.disabled = true;
        });
        documentsFormActions.style.display = 'none';
        updateDocumentsBtn.style.display = 'block';
    });
    
    documentsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect form data
        const formData = new FormData();
        formData.append('action', 'update_documents');
        formData.append('user_id', '<?php echo $userId; ?>');
        
        // Add all checkbox values
        documentCheckboxes.forEach(checkbox => {
            formData.append(checkbox.name, checkbox.checked ? '1' : '0');
        });
        
        // Send data to server
        fetch('index.php?action=company&method=update_documents', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessToast('Documents updated successfully');
                
                // Update original values
                documentCheckboxes.forEach(checkbox => {
                    originalDocumentData[checkbox.name] = checkbox.checked;
                });
                
                // Reset form to read-only
                documentCheckboxes.forEach(checkbox => {
                    checkbox.disabled = true;
                });
                documentsFormActions.style.display = 'none';
                updateDocumentsBtn.style.display = 'block';
            } else {
                alert('Error: ' + (data.message || 'Failed to save documents'));
            }
        })
        .catch(error => {
            
            alert('Error: Failed to save documents');
        });
    });
    
    // Success Toast Function
    function showSuccessToast(message = '✅ Successfully updated') {
        const toastElement = document.getElementById('successToast');
        if (toastElement) {
            const toastBody = toastElement.querySelector('.toast-body');
            if (toastBody) {
                toastBody.textContent = message;
            }
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    }
});
</script>

<style>
/* Enhanced Company Profile Management Styles */

/* Main Container */
.company-profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Profile Sections */
.profile-section {
    margin-bottom: 2rem;
}

.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: all 0.3s ease;
}

.section-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

/* Section Headers */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: 1px solid #e9ecef;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title i {
    font-size: 1.5rem;
    opacity: 0.9;
}

.section-title h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.section-actions {
    display: flex;
    gap: 0.5rem;
}

/* Section Content */
.section-content {
    padding: 2rem;
}

/* Enhanced Forms */
.enhanced-form {
    width: 100%;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.form-label i {
    color: #667eea;
    font-size: 0.875rem;
    width: 16px;
    text-align: center;
}

.required {
    color: #dc3545;
    font-weight: 700;
}

/* Enhanced Form Controls */
.form-control {
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    color: #495057;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background-color: white;
}

.form-control-active {
    background-color: white !important;
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    color: #495057 !important;
}

/* Readonly Input Styling */
input[readonly], textarea[readonly] {
    background-color: #f8f9fa !important;
    border-color: #e9ecef !important;
    color: #6c757d !important;
    cursor: not-allowed;
}

/* Contact Groups */
.contact-group {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.contact-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.contact-header i {
    font-size: 1.25rem;
    color: #667eea;
}

.contact-header h4 {
    margin: 0;
    color: #495057;
    font-weight: 600;
    font-size: 1.1rem;
}

/* Documents Section */
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.documents-column {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
}

.documents-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.documents-header i {
    font-size: 1.25rem;
    color: #667eea;
}

.documents-header h4 {
    margin: 0;
    color: #495057;
    font-weight: 600;
    font-size: 1.1rem;
}

.document-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.document-item:hover {
    background: #f8f9fa;
    border-color: #667eea;
}

.form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    margin: 0;
    border: 2px solid #dee2e6;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.form-check-input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.form-check-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    margin: 0;
}

.form-check-label i {
    color: #667eea;
    font-size: 0.875rem;
    width: 16px;
    text-align: center;
}

/* Enhanced Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-edit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.btn-edit:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4c93 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-save {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-save:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.btn-cancel {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
}

.btn-cancel:hover {
    background: linear-gradient(135deg, #5a6268 0%, #343a40 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
}

.btn-outline-secondary {
    background: transparent;
    color: #6c757d;
    border: 2px solid #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
    margin-top: 2rem;
}

/* Toast Styles */
.toast {
    z-index: 1055;
}

/* Responsive Design */
@media (max-width: 768px) {
    .company-profile-container {
        padding: 0 0.5rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.5rem;
    }
    
    .section-content {
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .documents-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .contact-group {
        padding: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .section-title h3 {
        font-size: 1.1rem;
    }
    
    .contact-header h4,
    .documents-header h4 {
        font-size: 1rem;
    }
    
    .form-label {
        font-size: 0.85rem;
    }
    
    .form-control {
        padding: 0.625rem 0.875rem;
        font-size: 0.9rem;
    }
}
</style> 