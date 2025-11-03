const express = require('express');
const rateLimit = require('express-rate-limit');
const { body, param } = require('express-validator');
const { authenticateToken, authorize } = require('../middleware/auth');
const {
  getAllUsers,
  getUserById,
  updateUser,
  deleteUser,
  approveUser,
  rejectUser,
  getPendingRegistrations,
  getSystemStats,
  updateSuperAdminSettings,
  forcePasswordChange
} = require('../controllers/adminController');

const router = express.Router();

// Rate limiting for admin routes (stricter than regular auth)
const adminLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 20, // 20 attempts per window
  message: {
    success: false,
    message: 'Too many admin requests, please try again later'
  },
  standardHeaders: true,
  legacyHeaders: false,
});

// All admin routes require authentication
router.use(authenticateToken);

// Validation rules
const userUpdateValidation = [
  body('firstName')
    .optional()
    .trim()
    .isLength({ min: 2, max: 50 })
    .withMessage('First name must be between 2 and 50 characters'),
  body('lastName')
    .optional()
    .trim()
    .isLength({ min: 2, max: 50 })
    .withMessage('Last name must be between 2 and 50 characters'),
  body('company')
    .optional()
    .trim()
    .isLength({ min: 2, max: 100 })
    .withMessage('Company name must be between 2 and 100 characters'),
  body('role')
    .optional()
    .isIn(['partner', 'admin', 'super_admin'])
    .withMessage('Invalid role'),
  body('status')
    .optional()
    .isIn(['active', 'inactive', 'pending', 'suspended'])
    .withMessage('Invalid status'),
  body('permissions')
    .optional()
    .isArray()
    .withMessage('Permissions must be an array'),
  body('notes')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Notes cannot exceed 500 characters')
];

const superAdminSettingsValidation = [
  body('allowedIPs')
    .optional()
    .isArray()
    .withMessage('Allowed IPs must be an array'),
  body('allowedIPs.*')
    .optional()
    .matches(/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/)
    .withMessage('Invalid IP address format'),
  body('notes')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Notes cannot exceed 500 characters')
];

const userIdValidation = [
  param('id')
    .isMongoId()
    .withMessage('Invalid user ID')
];

// Super Admin only routes
router.get('/stats', adminLimiter, authorize('super_admin'), getSystemStats);
router.get('/pending-registrations', adminLimiter, authorize('super_admin'), getPendingRegistrations);
router.put('/super-admin/settings', adminLimiter, authorize('super_admin'), superAdminSettingsValidation, updateSuperAdminSettings);

// Admin and Super Admin routes
router.get('/users', adminLimiter, authorize('admin', 'super_admin'), getAllUsers);
router.get('/users/:id', adminLimiter, authorize('admin', 'super_admin'), userIdValidation, getUserById);
router.put('/users/:id', adminLimiter, authorize('admin', 'super_admin'), userIdValidation, userUpdateValidation, updateUser);
router.delete('/users/:id', adminLimiter, authorize('super_admin'), userIdValidation, deleteUser);

// User approval routes (Admin and Super Admin)
router.post('/users/:id/approve', adminLimiter, authorize('admin', 'super_admin'), userIdValidation, approveUser);
router.post('/users/:id/reject', adminLimiter, authorize('admin', 'super_admin'), userIdValidation, rejectUser);

// Force password change (Super Admin only)
router.post('/users/:id/force-password-change', adminLimiter, authorize('super_admin'), userIdValidation, forcePasswordChange);

module.exports = router;
