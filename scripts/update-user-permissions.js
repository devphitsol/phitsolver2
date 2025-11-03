/**
 * User Permissions Management Script
 * Updates user permissions and roles in the MongoDB database
 * 
 * Usage: node scripts/update-user-permissions.js
 */

const mongoose = require('mongoose');
require('dotenv').config();

// Import User model
const User = require('../models/User');

// Permission definitions
const PERMISSIONS = {
    // Product permissions
    'read_products': 'View product information',
    'write_products': 'Create, edit, and delete products',
    
    // Order permissions
    'read_orders': 'View order information',
    'write_orders': 'Create, edit, and manage orders',
    
    // Analytics permissions
    'read_analytics': 'View analytics and reports',
    
    // User management permissions
    'manage_users': 'Manage regular users (partners)',
    'manage_admins': 'Manage admin users',
    'manage_companies': 'Manage company profiles',
    
    // System permissions
    'view_reports': 'Access system reports',
    'system_settings': 'Modify system settings',
    'approve_registrations': 'Approve or reject user registrations',
    'manage_support': 'Manage support tickets',
    'audit_logs': 'View audit logs and system activity'
};

// Role definitions with their default permissions
const ROLE_PERMISSIONS = {
    'partner': [
        'read_products',
        'read_orders'
    ],
    'admin': [
        'read_products',
        'write_products',
        'read_orders',
        'write_orders',
        'read_analytics',
        'manage_users',
        'manage_companies',
        'view_reports',
        'approve_registrations',
        'manage_support'
    ],
    'super_admin': [
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
    ]
};

async function updateUserPermissions() {
    try {
        console.log('🚀 Starting user permissions update...');
        
        // Connect to MongoDB
        const mongoURI = process.env.MONGODB_URI || 'mongodb://localhost:27017/phitsol_partners';
        await mongoose.connect(mongoURI, {
            useNewUrlParser: true,
            useUnifiedTopology: true,
        });
        
        console.log('✅ Connected to MongoDB');
        
        // Get all users
        const users = await User.find({});
        console.log(`📊 Found ${users.length} users in database`);
        
        let updatedCount = 0;
        
        for (const user of users) {
            const role = user.role || 'partner';
            const expectedPermissions = ROLE_PERMISSIONS[role] || ROLE_PERMISSIONS['partner'];
            
            // Check if user needs permission update
            const currentPermissions = user.permissions || [];
            const needsUpdate = !arraysEqual(currentPermissions.sort(), expectedPermissions.sort());
            
            if (needsUpdate) {
                console.log(`🔄 Updating permissions for ${user.email} (${user.role})`);
                console.log(`   Current: [${currentPermissions.join(', ')}]`);
                console.log(`   Expected: [${expectedPermissions.join(', ')}]`);
                
                user.permissions = expectedPermissions;
                user.updatedAt = new Date();
                await user.save();
                
                updatedCount++;
                console.log(`   ✅ Updated successfully`);
            } else {
                console.log(`✅ ${user.email} (${user.role}) - permissions are up to date`);
            }
        }
        
        console.log(`\n📈 Summary:`);
        console.log(`   Total users: ${users.length}`);
        console.log(`   Updated users: ${updatedCount}`);
        console.log(`   No changes needed: ${users.length - updatedCount}`);
        
        // Display role statistics
        const roleStats = {};
        users.forEach(user => {
            roleStats[user.role] = (roleStats[user.role] || 0) + 1;
        });
        
        console.log(`\n👥 Role Distribution:`);
        Object.entries(roleStats).forEach(([role, count]) => {
            console.log(`   ${role}: ${count} users`);
        });
        
        console.log('\n🎉 User permissions update completed successfully!');
        
    } catch (error) {
        console.error('❌ Error updating user permissions:', error);
        process.exit(1);
    } finally {
        // Close MongoDB connection
        await mongoose.connection.close();
        console.log('🔌 Database connection closed');
    }
}

async function listPermissions() {
    console.log('📋 Available Permissions:');
    Object.entries(PERMISSIONS).forEach(([permission, description]) => {
        console.log(`   ${permission}: ${description}`);
    });
    
    console.log('\n👑 Role Permission Mappings:');
    Object.entries(ROLE_PERMISSIONS).forEach(([role, permissions]) => {
        console.log(`\n   ${role.toUpperCase()}:`);
        permissions.forEach(permission => {
            console.log(`     - ${permission}: ${PERMISSIONS[permission]}`);
        });
    });
}

async function addPermissionToUser(email, permission) {
    try {
        console.log(`🔧 Adding permission '${permission}' to user '${email}'...`);
        
        // Connect to MongoDB
        const mongoURI = process.env.MONGODB_URI || 'mongodb://localhost:27017/phitsol_partners';
        await mongoose.connect(mongoURI, {
            useNewUrlParser: true,
            useUnifiedTopology: true,
        });
        
        const user = await User.findOne({ email: email.toLowerCase() });
        
        if (!user) {
            console.log(`❌ User '${email}' not found`);
            return;
        }
        
        if (!user.permissions.includes(permission)) {
            user.permissions.push(permission);
            user.updatedAt = new Date();
            await user.save();
            
            console.log(`✅ Permission '${permission}' added to user '${email}'`);
            console.log(`   Current permissions: [${user.permissions.join(', ')}]`);
        } else {
            console.log(`⚠️  User '${email}' already has permission '${permission}'`);
        }
        
    } catch (error) {
        console.error('❌ Error adding permission:', error);
    } finally {
        await mongoose.connection.close();
    }
}

async function removePermissionFromUser(email, permission) {
    try {
        console.log(`🗑️  Removing permission '${permission}' from user '${email}'...`);
        
        // Connect to MongoDB
        const mongoURI = process.env.MONGODB_URI || 'mongodb://localhost:27017/phitsol_partners';
        await mongoose.connect(mongoURI, {
            useNewUrlParser: true,
            useUnifiedTopology: true,
        });
        
        const user = await User.findOne({ email: email.toLowerCase() });
        
        if (!user) {
            console.log(`❌ User '${email}' not found`);
            return;
        }
        
        const permissionIndex = user.permissions.indexOf(permission);
        if (permissionIndex > -1) {
            user.permissions.splice(permissionIndex, 1);
            user.updatedAt = new Date();
            await user.save();
            
            console.log(`✅ Permission '${permission}' removed from user '${email}'`);
            console.log(`   Current permissions: [${user.permissions.join(', ')}]`);
        } else {
            console.log(`⚠️  User '${email}' does not have permission '${permission}'`);
        }
        
    } catch (error) {
        console.error('❌ Error removing permission:', error);
    } finally {
        await mongoose.connection.close();
    }
}

// Helper function to compare arrays
function arraysEqual(a, b) {
    if (a.length !== b.length) return false;
    for (let i = 0; i < a.length; i++) {
        if (a[i] !== b[i]) return false;
    }
    return true;
}

// Command line interface
async function main() {
    const args = process.argv.slice(2);
    const command = args[0];
    
    switch (command) {
        case 'list':
            await listPermissions();
            break;
        case 'update':
            await updateUserPermissions();
            break;
        case 'add':
            if (args.length < 3) {
                console.log('Usage: node scripts/update-user-permissions.js add <email> <permission>');
                process.exit(1);
            }
            await addPermissionToUser(args[1], args[2]);
            break;
        case 'remove':
            if (args.length < 3) {
                console.log('Usage: node scripts/update-user-permissions.js remove <email> <permission>');
                process.exit(1);
            }
            await removePermissionFromUser(args[1], args[2]);
            break;
        default:
            console.log('📋 User Permissions Management Script');
            console.log('\nUsage:');
            console.log('  node scripts/update-user-permissions.js list          - List all available permissions');
            console.log('  node scripts/update-user-permissions.js update        - Update all user permissions');
            console.log('  node scripts/update-user-permissions.js add <email> <permission>    - Add permission to user');
            console.log('  node scripts/update-user-permissions.js remove <email> <permission> - Remove permission from user');
            console.log('\nExamples:');
            console.log('  node scripts/update-user-permissions.js add admin@phitsol.com manage_users');
            console.log('  node scripts/update-user-permissions.js remove user@company.com write_products');
            break;
    }
}

// Run the script
if (require.main === module) {
    main();
}

module.exports = {
    updateUserPermissions,
    listPermissions,
    addPermissionToUser,
    removePermissionFromUser,
    PERMISSIONS,
    ROLE_PERMISSIONS
};
