/**
 * Super Admin Account Creation Script
 * Creates a Super Admin user in the MongoDB database
 * 
 * Usage: node scripts/create-super-admin.js
 */

const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');
require('dotenv').config();

// Import User model
const User = require('../models/User');

// Super Admin configuration
const SUPER_ADMIN_CONFIG = {
    email: 'superadmin@phitsol.com',
    password: 'SuperAdmin@123', // Default password - MUST be changed on first login
    firstName: 'Super',
    lastName: 'Admin',
    company: 'PHITSOL INC.',
    role: 'super_admin',
    status: 'active',
    permissions: [
        'read_products',
        'write_products',
        'read_orders',
        'write_orders',
        'read_analytics',
        'manage_users',
        'manage_admins',
        'manage_companies',
        'view_reports',
        'system_settings',
        'approve_registrations',
        'manage_support',
        'audit_logs'
    ],
    profile: {
        phone: '+1-555-0123',
        address: {
            street: '123 Business Ave',
            city: 'Corporate City',
            state: 'CA',
            zipCode: '90210',
            country: 'USA'
        },
        preferences: {
            notifications: {
                email: true,
                sms: false
            },
            language: 'en',
            timezone: 'UTC'
        }
    },
    isFirstLogin: true, // Force password change on first login
    lastPasswordChange: new Date(),
    createdBy: 'system'
};

async function createSuperAdmin() {
    try {
        console.log('🚀 Starting Super Admin creation process...');
        
        // Connect to MongoDB
        const mongoURI = process.env.MONGODB_URI || 'mongodb://localhost:27017/phitsol_partners';
        await mongoose.connect(mongoURI, {
            useNewUrlParser: true,
            useUnifiedTopology: true,
        });
        
        console.log('✅ Connected to MongoDB');
        
        // Check if Super Admin already exists
        const existingSuperAdmin = await User.findOne({ 
            $or: [
                { email: SUPER_ADMIN_CONFIG.email },
                { role: 'super_admin' }
            ]
        });
        
        if (existingSuperAdmin) {
            console.log('⚠️  Super Admin already exists!');
            console.log(`   Email: ${existingSuperAdmin.email}`);
            console.log(`   Role: ${existingSuperAdmin.role}`);
            console.log(`   Status: ${existingSuperAdmin.status}`);
            
            const readline = require('readline');
            const rl = readline.createInterface({
                input: process.stdin,
                output: process.stdout
            });
            
            const answer = await new Promise((resolve) => {
                rl.question('Do you want to update the existing Super Admin? (y/N): ', resolve);
            });
            
            rl.close();
            
            if (answer.toLowerCase() !== 'y' && answer.toLowerCase() !== 'yes') {
                console.log('❌ Super Admin creation cancelled.');
                process.exit(0);
            }
            
            // Update existing Super Admin
            const hashedPassword = await bcrypt.hash(SUPER_ADMIN_CONFIG.password, 12);
            
            existingSuperAdmin.password = hashedPassword;
            existingSuperAdmin.firstName = SUPER_ADMIN_CONFIG.firstName;
            existingSuperAdmin.lastName = SUPER_ADMIN_CONFIG.lastName;
            existingSuperAdmin.company = SUPER_ADMIN_CONFIG.company;
            existingSuperAdmin.status = SUPER_ADMIN_CONFIG.status;
            existingSuperAdmin.permissions = SUPER_ADMIN_CONFIG.permissions;
            existingSuperAdmin.profile = SUPER_ADMIN_CONFIG.profile;
            existingSuperAdmin.isFirstLogin = true;
            existingSuperAdmin.lastPasswordChange = new Date();
            existingSuperAdmin.updatedAt = new Date();
            
            await existingSuperAdmin.save();
            
            console.log('✅ Super Admin updated successfully!');
            console.log(`   Email: ${existingSuperAdmin.email}`);
            console.log(`   Role: ${existingSuperAdmin.role}`);
            console.log(`   Status: ${existingSuperAdmin.status}`);
            console.log(`   Permissions: ${existingSuperAdmin.permissions.length} permissions granted`);
            
        } else {
            // Create new Super Admin
            console.log('📝 Creating new Super Admin account...');
            
            // Hash the password
            const hashedPassword = await bcrypt.hash(SUPER_ADMIN_CONFIG.password, 12);
            console.log('🔐 Password hashed successfully');
            
            // Create user data
            const superAdminData = {
                ...SUPER_ADMIN_CONFIG,
                password: hashedPassword
            };
            
            // Create the Super Admin user
            const superAdmin = new User(superAdminData);
            await superAdmin.save();
            
            console.log('✅ Super Admin created successfully!');
            console.log('📋 Account Details:');
            console.log(`   Email: ${superAdmin.email}`);
            console.log(`   Name: ${superAdmin.firstName} ${superAdmin.lastName}`);
            console.log(`   Company: ${superAdmin.company}`);
            console.log(`   Role: ${superAdmin.role}`);
            console.log(`   Status: ${superAdmin.status}`);
            console.log(`   Permissions: ${superAdmin.permissions.length} permissions granted`);
            console.log(`   Created: ${superAdmin.createdAt}`);
        }
        
        // Display security information
        console.log('\n🔒 Security Information:');
        console.log('   Default Password: SuperAdmin@123');
        console.log('   ⚠️  MUST be changed on first login!');
        console.log('   🔐 Password is hashed with bcrypt (12 rounds)');
        console.log('   🛡️  Account has full system permissions');
        
        // Display access information
        console.log('\n🌐 Access Information:');
        console.log('   Login URL: http://localhost/phitsol/login_handler.php');
        console.log('   Dashboard URL: http://localhost/phitsol/Dashboard_partners.php');
        console.log('   API Endpoint: http://localhost:3000/api/auth/login');
        
        // Display permissions
        console.log('\n👑 Super Admin Permissions:');
        SUPER_ADMIN_CONFIG.permissions.forEach((permission, index) => {
            console.log(`   ${index + 1}. ${permission}`);
        });
        
        console.log('\n🎉 Super Admin setup completed successfully!');
        
    } catch (error) {
        console.error('❌ Error creating Super Admin:', error);
        process.exit(1);
    } finally {
        // Close MongoDB connection
        await mongoose.connection.close();
        console.log('🔌 Database connection closed');
    }
}

// Run the script
if (require.main === module) {
    createSuperAdmin();
}

module.exports = { createSuperAdmin, SUPER_ADMIN_CONFIG };
