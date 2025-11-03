const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');

const userSchema = new mongoose.Schema({
  email: {
    type: String,
    required: [true, 'Email is required'],
    unique: true,
    lowercase: true,
    trim: true,
    match: [/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/, 'Please enter a valid email']
  },
  password: {
    type: String,
    required: [true, 'Password is required'],
    minlength: [6, 'Password must be at least 6 characters long'],
    select: false // Don't include password in queries by default
  },
  firstName: {
    type: String,
    required: [true, 'First name is required'],
    trim: true,
    maxlength: [50, 'First name cannot exceed 50 characters']
  },
  lastName: {
    type: String,
    required: [true, 'Last name is required'],
    trim: true,
    maxlength: [50, 'Last name cannot exceed 50 characters']
  },
  company: {
    type: String,
    required: [true, 'Company name is required'],
    trim: true,
    maxlength: [100, 'Company name cannot exceed 100 characters']
  },
  role: {
    type: String,
    enum: ['partner', 'admin', 'super_admin'],
    default: 'partner'
  },
  status: {
    type: String,
    enum: ['active', 'inactive', 'pending', 'suspended'],
    default: 'pending'
  },
  lastLogin: {
    type: Date,
    default: null
  },
  loginAttempts: {
    type: Number,
    default: 0
  },
  lockUntil: {
    type: Date,
    default: null
  },
  profile: {
    phone: {
      type: String,
      trim: true,
      match: [/^[\+]?[1-9][\d]{0,15}$/, 'Please enter a valid phone number']
    },
    address: {
      street: String,
      city: String,
      state: String,
      zipCode: String,
      country: String
    },
    preferences: {
      notifications: {
        email: { type: Boolean, default: true },
        sms: { type: Boolean, default: false }
      },
      language: { type: String, default: 'en' },
      timezone: { type: String, default: 'UTC' }
    }
  },
  permissions: [{
    type: String,
    enum: [
      'read_products', 'write_products', 'read_orders', 'write_orders', 'read_analytics', 
      'manage_users', 'manage_admins', 'manage_companies', 'view_reports', 'system_settings',
      'approve_registrations', 'manage_support', 'audit_logs'
    ]
  }],
  createdAt: {
    type: Date,
    default: Date.now
  },
  updatedAt: {
    type: Date,
    default: Date.now
  },
  // Super Admin specific fields
  isFirstLogin: {
    type: Boolean,
    default: false
  },
  lastPasswordChange: {
    type: Date,
    default: Date.now
  },
  passwordChangeRequired: {
    type: Boolean,
    default: false
  },
  twoFactorEnabled: {
    type: Boolean,
    default: false
  },
  twoFactorSecret: {
    type: String,
    select: false
  },
  allowedIPs: [{
    type: String,
    validate: {
      validator: function(v) {
        return /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(v);
      },
      message: 'Invalid IP address format'
    }
  }],
  loginAttempts: {
    type: Number,
    default: 0
  },
  lockUntil: {
    type: Date,
    default: null
  },
  createdBy: {
    type: String,
    default: 'system'
  },
  notes: {
    type: String,
    maxlength: [500, 'Notes cannot exceed 500 characters']
  }
}, {
  timestamps: true,
  toJSON: { virtuals: true },
  toObject: { virtuals: true }
});

// Virtual for full name
userSchema.virtual('fullName').get(function() {
  return `${this.firstName} ${this.lastName}`;
});

// Virtual for account lock status
userSchema.virtual('isLocked').get(function() {
  return !!(this.lockUntil && this.lockUntil > Date.now());
});

// Index for better performance
userSchema.index({ email: 1 });
userSchema.index({ status: 1 });
userSchema.index({ role: 1 });

// Pre-save middleware to hash password
userSchema.pre('save', async function(next) {
  // Only hash the password if it has been modified (or is new)
  if (!this.isModified('password')) return next();

  try {
    // Hash password with cost of 12
    const salt = await bcrypt.genSalt(12);
    this.password = await bcrypt.hash(this.password, salt);
    next();
  } catch (error) {
    next(error);
  }
});

// Pre-save middleware to update updatedAt
userSchema.pre('save', function(next) {
  this.updatedAt = Date.now();
  next();
});

// Instance method to check password
userSchema.methods.comparePassword = async function(candidatePassword) {
  try {
    return await bcrypt.compare(candidatePassword, this.password);
  } catch (error) {
    throw new Error('Password comparison failed');
  }
};

// Instance method to increment login attempts
userSchema.methods.incLoginAttempts = function() {
  // If we have a previous lock that has expired, restart at 1
  if (this.lockUntil && this.lockUntil < Date.now()) {
    return this.updateOne({
      $unset: { lockUntil: 1 },
      $set: { loginAttempts: 1 }
    });
  }
  
  const updates = { $inc: { loginAttempts: 1 } };
  
  // Lock account after 5 failed attempts for 2 hours
  if (this.loginAttempts + 1 >= 5 && !this.isLocked) {
    updates.$set = { lockUntil: Date.now() + 2 * 60 * 60 * 1000 }; // 2 hours
  }
  
  return this.updateOne(updates);
};

// Instance method to reset login attempts
userSchema.methods.resetLoginAttempts = function() {
  return this.updateOne({
    $unset: { loginAttempts: 1, lockUntil: 1 },
    $set: { lastLogin: new Date() }
  });
};

// Instance method to check if user is super admin
userSchema.methods.isSuperAdmin = function() {
  return this.role === 'super_admin';
};

// Instance method to check if user is admin or super admin
userSchema.methods.isAdmin = function() {
  return ['admin', 'super_admin'].includes(this.role);
};

// Instance method to check if password change is required
userSchema.methods.requiresPasswordChange = function() {
  return this.passwordChangeRequired || this.isFirstLogin;
};

// Instance method to check if IP is allowed (for super admin)
userSchema.methods.isIPAllowed = function(ip) {
  if (this.role !== 'super_admin' || !this.allowedIPs || this.allowedIPs.length === 0) {
    return true; // No IP restrictions for non-super-admin users
  }
  return this.allowedIPs.includes(ip);
};

// Instance method to add allowed IP
userSchema.methods.addAllowedIP = function(ip) {
  if (this.role === 'super_admin' && !this.allowedIPs.includes(ip)) {
    this.allowedIPs.push(ip);
    return this.save();
  }
  return Promise.resolve(this);
};

// Instance method to remove allowed IP
userSchema.methods.removeAllowedIP = function(ip) {
  if (this.role === 'super_admin') {
    this.allowedIPs = this.allowedIPs.filter(allowedIP => allowedIP !== ip);
    return this.save();
  }
  return Promise.resolve(this);
};

// Instance method to enable 2FA
userSchema.methods.enable2FA = function(secret) {
  this.twoFactorEnabled = true;
  this.twoFactorSecret = secret;
  return this.save();
};

// Instance method to disable 2FA
userSchema.methods.disable2FA = function() {
  this.twoFactorEnabled = false;
  this.twoFactorSecret = undefined;
  return this.save();
};

// Static method to find user by email (including password for login)
userSchema.statics.findByEmailForLogin = function(email) {
  return this.findOne({ email: email.toLowerCase() }).select('+password');
};

// Static method to create a new partner user
userSchema.statics.createPartner = async function(userData) {
  const user = new this(userData);
  await user.save();
  return user;
};

// Static method to create a new super admin user
userSchema.statics.createSuperAdmin = async function(userData) {
  const user = new this(userData);
  await user.save();
  return user;
};

// Static method to get super admin users
userSchema.statics.getSuperAdmins = function() {
  return this.find({ role: 'super_admin' });
};

// Static method to check if super admin exists
userSchema.statics.superAdminExists = async function() {
  const count = await this.countDocuments({ role: 'super_admin' });
  return count > 0;
};

module.exports = mongoose.model('User', userSchema);
