<?php
/**
 * Default Admin Credentials Configuration
 * 
 * This file contains the default admin credentials for initial system setup.
 * These credentials should be changed immediately after first login.
 */

return [
    'default_admin' => [
        'username' => 'admin',
        'email' => 'admin@phitsol.com',
        'password' => 'Admin@2025#', // Recommended default password
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'role' => 'admin',
        'status' => 'active',
        'is_default_password' => true, // Flag to track if using default password
        'password_change_required' => true, // Force password change on first login
        'created_by' => 'system',
        'notes' => 'Default admin account - password must be changed on first login'
    ],
    
    'security_settings' => [
        'default_password_expiry_days' => 0, // 0 = must change immediately
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numbers' => true,
        'password_require_special_chars' => true,
        'password_history_count' => 5, // Remember last 5 passwords
        'max_login_attempts' => 5,
        'lockout_duration_minutes' => 30
    ],
    
    'notification_settings' => [
        'notify_on_default_password_change' => true,
        'notify_on_admin_login' => true,
        'log_all_password_changes' => true,
        'audit_trail_retention_days' => 365
    ]
];
