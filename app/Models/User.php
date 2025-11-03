<?php

namespace App\Models;

use App\Config\Database;

class User
{
    private $collection;
    private const MAX_USERS = 100;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->collection = $db->getCollection('users');
    }

    /**
     * Get all users
     */
    public function getAll()
    {
        try {
            $cursor = $this->collection->find(
                [],
                ['sort' => ['created_at' => -1]]
            );
            return $cursor->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get active users only
     */
    public function getActive()
    {
        try {
            $cursor = $this->collection->find(
                ['status' => 'active'],
                ['sort' => ['created_at' => -1]]
            );
            return $cursor->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user by ID
     */
    public function getById($id)
    {
        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            $user = $this->collection->findOne($filter);
            if ($user) {
                $user = (array) $user;
                // Ensure compatibility with Partners Portal by adding first_name/last_name if not present
                $user = $this->ensureNameFieldsCompatibility($user);
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            error_log("Error fetching user by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by email
     */
    public function getByEmail($email)
    {
        try {
            $user = $this->collection->findOne(['email' => $email]);
            if ($user) {
                $user = (array) $user;
                // Ensure compatibility with Partners Portal by adding first_name/last_name if not present
                $user = $this->ensureNameFieldsCompatibility($user);
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            error_log("Error fetching user by email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by username
     */
    public function getByUsername($username)
    {
        try {
            $user = $this->collection->findOne(['username' => $username]);
            if ($user) {
                $user = (array) $user;
                // Ensure compatibility with Partners Portal by adding first_name/last_name if not present
                $user = $this->ensureNameFieldsCompatibility($user);
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            error_log("Error fetching user by username: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new user
     */
    public function create($data)
    {
        try {
            // Check if we've reached the maximum number of users
            $totalUsers = $this->collection->countDocuments([]);
            if ($totalUsers >= self::MAX_USERS) {
                throw new \Exception("Maximum number of users (" . self::MAX_USERS . ") reached");
            }

            // Check if email already exists
            if ($this->getByEmail($data['email'])) {
                throw new \Exception("Email already exists");
            }

            // Check if username already exists
            if ($this->getByUsername($data['username'])) {
                throw new \Exception("Username already exists");
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Set default values
            $data['created_at'] = $this->getCurrentDateTime();
            $data['updated_at'] = $this->getCurrentDateTime();
            $data['status'] = $data['status'] ?? 'active';
            $data['role'] = $data['role'] ?? 'employee';
            $data['last_login'] = null;
            
            // Add security fields for admin users
            if (isset($data['role']) && in_array($data['role'], ['admin', 'super_admin'])) {
                $data['is_default_password'] = $data['is_default_password'] ?? false;
                $data['password_change_required'] = $data['password_change_required'] ?? false;
                $data['password_history'] = [];
                $data['login_count'] = 0;
                $data['is_first_login'] = true;
            }

            $result = $this->collection->insertOne($data);
            return $result->getInsertedId();
        } catch (\Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user
     */
    public function update($id, $data)
    {
        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            $data['updated_at'] = $this->getCurrentDateTime();
            
            // Hash password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']); // Don't update password if not provided
            }

            // Check if email already exists (excluding current user)
            if (isset($data['email'])) {
                $existingUser = $this->getByEmail($data['email']);
                $idField = is_numeric($id) ? 'id' : '_id';
                if ($existingUser && $existingUser[$idField] != $id) {
                    throw new \Exception("Email already exists");
                }
            }

            // Check if username already exists (excluding current user)
            if (isset($data['username'])) {
                $existingUser = $this->getByUsername($data['username']);
                $idField = is_numeric($id) ? 'id' : '_id';
                if ($existingUser && $existingUser[$idField] != $id) {
                    throw new \Exception("Username already exists");
                }
            }
            
            $result = $this->collection->updateOne(
                $filter,
                ['$set' => $data]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user profile (for partners portal)
     */
    public function updateProfile($id, $data)
    {
        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            $updateData = [
                'updated_at' => $this->getCurrentDateTime()
            ];

            // Add provided data to update
            foreach ($data as $key => $value) {
                if ($value !== null && $value !== '') {
                    $updateData[$key] = $value;
                }
            }
            
            $result = $this->collection->updateOne(
                $filter,
                ['$set' => $updateData]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            $result = $this->collection->deleteOne($filter);
            return $result->getDeletedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            $user = $this->getById($id);
            if (!$user) {
                return false;
            }

            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            
            $result = $this->collection->updateOne(
                $filter,
                [
                    '$set' => [
                        'status' => $newStatus,
                        'updated_at' => $this->getCurrentDateTime()
                    ]
                ]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error toggling user status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve business customer account
     */
    public function approveBusinessAccount($id)
    {
        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            $user = $this->getById($id);
            if (!$user || $user['role'] !== 'business' || $user['status'] !== 'pending') {
                return false;
            }
            
            $result = $this->collection->updateOne(
                $filter,
                [
                    '$set' => [
                        'status' => 'active',
                        'updated_at' => $this->getCurrentDateTime()
                    ]
                ]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error approving business account: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password)
    {
        try {
            // Try to find user by username or email
            $user = $this->getByUsername($username);
            if (!$user) {
                $user = $this->getByEmail($username);
            }

            if (!$user) {
                return null;
            }

            // Check if user is active
            if ($user['status'] !== 'active') {
                return null;
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Update last login
                $this->updateLastLogin($user['_id']);
                return $user;
            }

            return null;
        } catch (\Exception $e) {
            error_log("Error authenticating user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Change user password with security checks
     */
    public function changePassword($userId, $currentPassword, $newPassword, $isForcedChange = false)
    {
        try {
            // Handle both MongoDB ObjectId and MySQL integer ID
            $filter = is_numeric($userId) ? ['id' => (int)$userId] : ['_id' => $userId];
            
            // Get user by ID
            $user = $this->collection->findOne($filter);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // For forced changes (like first login), skip current password verification
            if (!$isForcedChange) {
                // Verify current password
                if (!password_verify($currentPassword, $user['password'])) {
                    return ['success' => false, 'message' => 'Current password is incorrect'];
                }
            }
            
            // Validate new password strength
            $passwordValidation = $this->validatePasswordStrength($newPassword, $user);
            if (!$passwordValidation['valid']) {
                return ['success' => false, 'message' => implode(', ', $passwordValidation['errors'])];
            }
            
            // Check password history to prevent reuse
            if (isset($user['password_history']) && !empty($user['password_history'])) {
                foreach ($user['password_history'] as $oldHash) {
                    if (password_verify($newPassword, $oldHash)) {
                        return ['success' => false, 'message' => 'Cannot reuse a previous password'];
                    }
                }
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Prepare update data
            $updateData = [
                'password' => $hashedPassword,
                'updated_at' => new UTCDateTime()
            ];
            
            // Add to password history (keep last 5 passwords)
            $passwordHistory = $user['password_history'] ?? [];
            array_unshift($passwordHistory, $user['password']); // Add current password to history
            $passwordHistory = array_slice($passwordHistory, 0, 5); // Keep only last 5
            $updateData['password_history'] = $passwordHistory;
            
            // Clear password change requirements
            if (isset($user['password_change_required']) && $user['password_change_required']) {
                $updateData['password_change_required'] = false;
            }
            
            if (isset($user['is_default_password']) && $user['is_default_password']) {
                $updateData['is_default_password'] = false;
            }
            
            // Update password in database
            $result = $this->collection->updateOne(
                $filter,
                ['$set' => $updateData]
            );
            
            if ($result->getModifiedCount() > 0) {
                // Log the password change
                $this->logPasswordChange($userId, $user['username'] ?? 'unknown', 
                    isset($user['is_default_password']) && $user['is_default_password'], $isForcedChange);
                
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update password'];
            }
            
        } catch (\Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password'];
        }
    }
    
    /**
     * Validate password strength for a user
     */
    private function validatePasswordStrength($password, $user)
    {
        $errors = [];
        $minLength = 8;
        $requireUppercase = true;
        $requireLowercase = true;
        $requireNumbers = true;
        $requireSpecialChars = true;
        
        // Check minimum length
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long";
        }
        
        // Check for uppercase letters
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        // Check for lowercase letters
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        // Check for numbers
        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        // Check for special characters
        if ($requireSpecialChars && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        // Check for common weak passwords
        $weakPasswords = [
            'password', '123456', 'admin', 'administrator', 'root',
            'user', 'guest', 'test', 'demo', 'sample', 'default'
        ];
        
        if (in_array(strtolower($password), $weakPasswords)) {
            $errors[] = "Password is too common and easily guessable";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Log password change event
     */
    private function logPasswordChange($userId, $username, $isDefaultPasswordChange, $isForcedChange)
    {
        try {
            $auditLogger = new \App\Utils\AuditLogger();
            $auditLogger->logPasswordChange($userId, $username, $isDefaultPasswordChange, $isForcedChange);
            
            // Send notification for password change
            $notificationSystem = new \App\Utils\NotificationSystem();
            $notificationSystem->notifyDefaultPasswordChange($userId, $username, $isDefaultPasswordChange);
            
        } catch (\Exception $e) {
            error_log("Error logging password change: " . $e->getMessage());
        }
    }

    /**
     * Update last login time and track login count
     */
    public function updateLastLogin($id)
    {
        try {
            // Use MySQL integer ID
            $filter = ['id' => (int)$id];
            
            // Get current user data
            $user = $this->collection->findOne($filter);
            if (!$user) {
                return false;
            }
            
            $updateData = [
                'last_login' => $this->getCurrentDateTime(),
                'updated_at' => $this->getCurrentDateTime()
            ];
            
            // Update login count and first login flag
            if (isset($user['login_count'])) {
                $updateData['login_count'] = ($user['login_count'] ?? 0) + 1;
            } else {
                $updateData['login_count'] = 1;
            }
            
            if (isset($user['is_first_login']) && $user['is_first_login']) {
                $updateData['is_first_login'] = false;
            }
            
            $this->collection->updateOne(
                $filter,
                ['$set' => $updateData]
            );
            
            // Log login activity
            $this->logLoginActivity($id, $user);
            
            return true;
        } catch (\Exception $e) {
            error_log("Error updating last login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user requires password change
     */
    public function requiresPasswordChange($userId)
    {
        try {
            // Handle both MongoDB ObjectId and MySQL integer ID
            $filter = is_numeric($userId) ? ['id' => (int)$userId] : ['_id' => $userId];
            
            $user = $this->collection->findOne($filter);
            if (!$user) {
                return false;
            }
            
            // Check if password change is required
            if (isset($user['password_change_required']) && $user['password_change_required']) {
                return true;
            }
            
            // Check if using default password
            if (isset($user['is_default_password']) && $user['is_default_password']) {
                return true;
            }
            
            // Check if this is first login
            if (isset($user['is_first_login']) && $user['is_first_login']) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error checking password change requirement: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user security status
     */
    public function getSecurityStatus($userId)
    {
        try {
            // Handle both MongoDB ObjectId and MySQL integer ID
            $filter = is_numeric($userId) ? ['id' => (int)$userId] : ['_id' => $userId];
            
            $user = $this->collection->findOne($filter);
            if (!$user) {
                return null;
            }
            
            return [
                'requires_password_change' => $this->requiresPasswordChange($userId),
                'is_default_password' => $user['is_default_password'] ?? false,
                'password_change_required' => $user['password_change_required'] ?? false,
                'is_first_login' => $user['is_first_login'] ?? false,
                'login_count' => $user['login_count'] ?? 0,
                'last_login' => $user['last_login'] ?? null
            ];
        } catch (\Exception $e) {
            error_log("Error getting security status: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Log login activity
     */
    private function logLoginActivity($userId, $user)
    {
        try {
            $auditLogger = new \App\Utils\AuditLogger();
            $notificationSystem = new \App\Utils\NotificationSystem();
            
            $isFirstLogin = $user['is_first_login'] ?? false;
            $isDefaultPassword = $user['is_default_password'] ?? false;
            $username = $user['username'] ?? 'unknown';
            
            $auditLogger->logAdminLogin($userId, $username, $isFirstLogin, $isDefaultPassword);
            
            // Send notifications
            if ($isFirstLogin) {
                $notificationSystem->notifyFirstLogin($userId, $username);
            }
            
            if ($isDefaultPassword) {
                $auditLogger->logDefaultPasswordWarning($userId, $username);
                $notificationSystem->notifyDefaultPasswordLogin($userId, $username);
            }
            
        } catch (\Exception $e) {
            error_log("Error logging login activity: " . $e->getMessage());
        }
    }

    /**
     * Log Partners Portal activities for audit trail
     */
    public function logPartnersPortalActivity($userId, $action, $description = '')
    {
        try {
            // Use MySQL integer ID
            $userId = (int)$userId;
            
            $logEntry = [
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'timestamp' => $this->getCurrentDateTime()
            ];
            
            // Store in a separate collection for audit logs
            $db = Database::getInstance();
            $auditCollection = $db->getCollection('partners_portal_audit');
            $auditCollection->insertOne($logEntry);
            
        } catch (\Exception $e) {
            error_log("Error logging Partners Portal activity: " . $e->getMessage());
        }
    }

    /**
     * Get user count
     */
    public function getCount()
    {
        try {
            return $this->collection->countDocuments([]);
        } catch (\Exception $e) {
            error_log("Error counting users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active user count
     */
    public function getActiveCount()
    {
        try {
            return $this->collection->countDocuments(['status' => 'active']);
        } catch (\Exception $e) {
            error_log("Error counting active users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get admin user count
     */
    public function getAdminCount()
    {
        try {
            return $this->collection->countDocuments(['role' => 'admin']);
        } catch (\Exception $e) {
            error_log("Error counting admin users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get business user count
     */
    public function getBusinessUserCount()
    {
        try {
            return $this->collection->countDocuments(['role' => 'business']);
        } catch (\Exception $e) {
            error_log("Error counting business users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all business users
     */
    public function getBusinessUsers()
    {
        try {
            $cursor = $this->collection->find(
                ['role' => 'business'],
                ['sort' => ['created_at' => -1]]
            );
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching business users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee count
     */
    public function getEmployeeCount()
    {
        try {
            return $this->collection->countDocuments(['role' => 'employee']);
        } catch (\Exception $e) {
            error_log("Error counting employees: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get pending user count
     */
    public function getPendingCount()
    {
        try {
            return $this->collection->countDocuments(['status' => 'pending']);
        } catch (\Exception $e) {
            error_log("Error counting pending users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get maximum users allowed
     */
    public function getMaxUsers()
    {
        return self::MAX_USERS;
    }

    /**
     * Validate user data
     */
    public function validate($data)
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }

        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        if (empty($data['name'])) {
            $errors[] = 'Full name is required';
        }

        return $errors;
    }

    /**
     * Get document status for a user
     */
    public function getDocumentStatus($userId)
    {
        $user = $this->getById($userId);
        return $user['document_status'] ?? [];
    }

    /**
     * Update document status for a user
     */
    public function updateDocumentStatus($userId, $docKey, $status, $note = '')
    {
        try {
            // Handle both MongoDB ObjectId and MySQL integer ID
            $filter = is_numeric($userId) ? ['id' => (int)$userId] : ['_id' => $userId];
            
            $update = [
                "document_status.$docKey.status" => $status,
                "document_status.$docKey.admin_note" => $note,
                'updated_at' => $this->getCurrentDateTime()
            ];
            $result = $this->collection->updateOne(
                $filter,
                ['$set' => $update]
            );
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating document status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get business users count
     */
    public function getBusinessCount()
    {
        try {
            return $this->collection->countDocuments(['role' => 'business']);
        } catch (\Exception $e) {
            error_log("Error counting business users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update user documents
     */
    public function updateUserDocuments($userId, $documents)
    {
        try {
            // Handle both MongoDB ObjectId and MySQL integer ID
            $filter = is_numeric($userId) ? ['id' => (int)$userId] : ['_id' => $userId];
            
            $result = $this->collection->updateOne(
                $filter,
                ['$set' => ['documents' => $documents]]
            );
            
            return $result->getModifiedCount() > 0;
            
        } catch (\Exception $e) {
            error_log('Error updating user documents: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get users with pagination, search, and filters
     */
    public function getUsersWithFilters($options = [])
    {
        try {
            $page = $options['page'] ?? 1;
            $limit = $options['limit'] ?? 10;
            $search = $options['search'] ?? '';
            $role = $options['role'] ?? '';
            $status = $options['status'] ?? '';
            $sortBy = $options['sortBy'] ?? 'created_at';
            $sortOrder = $options['sortOrder'] ?? -1;
            
            // Build filter criteria
            $filter = [];
            
            // Search filter
            if (!empty($search)) {
                $filter['$or'] = [
                    ['name' => ['$regex' => $search, '$options' => 'i']],
                    ['email' => ['$regex' => $search, '$options' => 'i']],
                    ['username' => ['$regex' => $search, '$options' => 'i']]
                ];
            }
            
            // Role filter
            if (!empty($role)) {
                $filter['role'] = $role;
            }
            
            // Status filter
            if (!empty($status)) {
                $filter['status'] = $status;
            }
            
            // Calculate skip value for pagination
            $skip = ($page - 1) * $limit;
            
            // Get total count for pagination
            $totalCount = $this->collection->countDocuments($filter);
            
            // Get users with filters and pagination
            $cursor = $this->collection->find(
                $filter,
                [
                    'sort' => [$sortBy => $sortOrder],
                    'skip' => $skip,
                    'limit' => $limit
                ]
            );
            
            $users = $cursor->toArray();
            
            return [
                'users' => $users,
                'totalCount' => $totalCount,
                'currentPage' => $page,
                'totalPages' => ceil($totalCount / $limit),
                'limit' => $limit,
                'hasNextPage' => $page < ceil($totalCount / $limit),
                'hasPrevPage' => $page > 1
            ];
        } catch (\Exception $e) {
            error_log("Error fetching users with filters: " . $e->getMessage());
            return [
                'users' => [],
                'totalCount' => 0,
                'currentPage' => 1,
                'totalPages' => 0,
                'limit' => $limit ?? 10,
                'hasNextPage' => false,
                'hasPrevPage' => false
            ];
        }
    }

    /**
     * Get users for API response
     */
    public function getUsersForAPI($options = [])
    {
        $result = $this->getUsersWithFilters($options);
        
        // Transform users for API response
        $transformedUsers = [];
        foreach ($result['users'] as $user) {
            // Use MySQL integer ID
            $id = (string)$user['id'];
            
            $transformedUsers[] = [
                'id' => $id,
                'name' => $user['name'] ?? 'N/A',
                'email' => $user['email'],
                'username' => $user['username'],
                'role' => $user['role'],
                'status' => $user['status'],
                'last_login' => $user['last_login'] ? 
                    (is_object($user['last_login']) ? $user['last_login']->toDateTime()->format('Y-m-d H:i:s') : $user['last_login']) : 
                    null,
                'created_at' => $user['created_at'] ? 
                    (is_object($user['created_at']) ? $user['created_at']->toDateTime()->format('Y-m-d H:i:s') : $user['created_at']) : 
                    null,
                'updated_at' => $user['updated_at'] ? 
                    (is_object($user['updated_at']) ? $user['updated_at']->toDateTime()->format('Y-m-d H:i:s') : $user['updated_at']) : 
                    null
            ];
        }
        
        return [
            'users' => $transformedUsers,
            'pagination' => [
                'totalCount' => $result['totalCount'],
                'currentPage' => $result['currentPage'],
                'totalPages' => $result['totalPages'],
                'limit' => $result['limit'],
                'hasNextPage' => $result['hasNextPage'],
                'hasPrevPage' => $result['hasPrevPage']
            ]
        ];
    }

    /**
     * Get available roles for filtering
     */
    public function getAvailableRoles()
    {
        try {
            $roles = $this->collection->distinct('role');
            return array_values(array_filter($roles));
        } catch (\Exception $e) {
            error_log("Error fetching available roles: " . $e->getMessage());
            return ['admin', 'employee', 'business'];
        }
    }

    /**
     * Get available statuses for filtering
     */
    public function getAvailableStatuses()
    {
        try {
            $statuses = $this->collection->distinct('status');
            return array_values(array_filter($statuses));
        } catch (\Exception $e) {
            error_log("Error fetching available statuses: " . $e->getMessage());
            return ['active', 'inactive', 'pending'];
        }
    }

    /**
     * Ensure name fields compatibility between admin system and Partners Portal
     */
    private function ensureNameFieldsCompatibility($user)
    {
        // If user has 'name' field but no 'first_name'/'last_name', split the name
        if (isset($user['name']) && !isset($user['first_name']) && !isset($user['last_name'])) {
            $nameParts = explode(' ', trim($user['name']), 2);
            $user['first_name'] = $nameParts[0] ?? '';
            $user['last_name'] = $nameParts[1] ?? '';
        }
        
        // If user has 'first_name'/'last_name' but no 'name', combine them
        if ((isset($user['first_name']) || isset($user['last_name'])) && !isset($user['name'])) {
            $user['name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        }
        
        return $user;
    }

    /**
     * Get current date time in appropriate format
     */
    private function getCurrentDateTime()
    {
        // Use MySQL datetime format
        return date('Y-m-d H:i:s');
    }
} 