#!/usr/bin/env node

/**
 * PHITSOL Smart Index.html Synchronization Script
 * This script ensures that public/index.html stays in sync with root index.html
 * while maintaining correct relative paths for each location
 */

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const rootFile = 'index.html';
const publicFile = 'public/index.html';

function getFileHash(filePath) {
    const content = fs.readFileSync(filePath);
    return crypto.createHash('md5').update(content).digest('hex');
}

function convertPathsForPublic(content) {
    // Convert root-relative paths to public-relative paths
    let converted = content;
    
    // CSS paths: css/ -> ../css/
    converted = converted.replace(/href="css\//g, 'href="../css/');
    converted = converted.replace(/href="revolution\/css\//g, 'href="../revolution/css/');
    
    // Image paths: images/ -> ../images/
    converted = converted.replace(/src="images\//g, 'src="../images/');
    converted = converted.replace(/href="images\//g, 'href="../images/');
    converted = converted.replace(/data-at2x="images\//g, 'data-at2x="../images/');
    converted = converted.replace(/url\(images\//g, 'url(../images/');
    
    // JavaScript paths: js/ -> ../js/
    converted = converted.replace(/src="js\//g, 'src="../js/');
    converted = converted.replace(/src="revolution\/js\//g, 'src="../revolution/js/');
    
    return converted;
}

function convertPathsForRoot(content) {
    // Convert public-relative paths to root-relative paths
    let converted = content;
    
    // CSS paths: ../css/ -> css/
    converted = converted.replace(/href="\.\.\/css\//g, 'href="css/');
    converted = converted.replace(/href="\.\.\/revolution\/css\//g, 'href="revolution/css/');
    
    // Image paths: ../images/ -> images/
    converted = converted.replace(/src="\.\.\/images\//g, 'src="images/');
    converted = converted.replace(/href="\.\.\/images\//g, 'href="images/');
    converted = converted.replace(/data-at2x="\.\.\/images\//g, 'data-at2x="images/');
    converted = converted.replace(/url\(\.\.\/images\//g, 'url(images/');
    
    // JavaScript paths: ../js/ -> js/
    converted = converted.replace(/src="\.\.\/js\//g, 'src="js/');
    converted = converted.replace(/src="\.\.\/revolution\/js\//g, 'src="revolution/js/');
    
    return converted;
}

function syncToPublic() {
    console.log('🔄 Syncing root index.html to public/index.html with path conversion...');
    
    if (!fs.existsSync(rootFile)) {
        console.error('❌ Error: index.html not found in root directory!');
        process.exit(1);
    }
    
    // Read root file
    const rootContent = fs.readFileSync(rootFile, 'utf8');
    
    // Convert paths for public directory
    const publicContent = convertPathsForPublic(rootContent);
    
    // Ensure public directory exists
    const publicDir = path.dirname(publicFile);
    if (!fs.existsSync(publicDir)) {
        fs.mkdirSync(publicDir, { recursive: true });
    }
    
    // Write to public file
    fs.writeFileSync(publicFile, publicContent);
    console.log('✅ Synchronization complete with path conversion!');
    
    // Verify files exist and are different (as expected)
    const rootHash = getFileHash(rootFile);
    const publicHash = getFileHash(publicFile);
    
    if (rootHash !== publicHash) {
        console.log('✅ Files are correctly different (paths converted for each location)');
        console.log(`   Root hash: ${rootHash.substring(0, 8)}...`);
        console.log(`   Public hash: ${publicHash.substring(0, 8)}...`);
    } else {
        console.log('⚠️  Warning: Files are identical - path conversion may not have worked');
    }
}

function syncToRoot() {
    console.log('🔄 Syncing public/index.html to root index.html with path conversion...');
    
    if (!fs.existsSync(publicFile)) {
        console.error('❌ Error: public/index.html not found!');
        process.exit(1);
    }
    
    // Read public file
    const publicContent = fs.readFileSync(publicFile, 'utf8');
    
    // Convert paths for root directory
    const rootContent = convertPathsForRoot(publicContent);
    
    // Write to root file
    fs.writeFileSync(rootFile, rootContent);
    console.log('✅ Synchronization complete with path conversion!');
    
    // Verify files exist and are different (as expected)
    const rootHash = getFileHash(rootFile);
    const publicHash = getFileHash(publicFile);
    
    if (rootHash !== publicHash) {
        console.log('✅ Files are correctly different (paths converted for each location)');
        console.log(`   Root hash: ${rootHash.substring(0, 8)}...`);
        console.log(`   Public hash: ${publicHash.substring(0, 8)}...`);
    } else {
        console.log('⚠️  Warning: Files are identical - path conversion may not have worked');
    }
}

function watchFile() {
    console.log('👀 Watching root index.html for changes...');
    console.log('Press Ctrl+C to stop watching');
    
    fs.watchFile(rootFile, { interval: 1000 }, (curr, prev) => {
        if (curr.mtime !== prev.mtime) {
            console.log(`\n📝 index.html changed at ${new Date().toLocaleString()}`);
            syncToPublic();
        }
    });
    
    // Keep the process running
    process.on('SIGINT', () => {
        console.log('\n👋 Stopping file watcher...');
        fs.unwatchFile(rootFile);
        process.exit(0);
    });
}

// Main execution
const args = process.argv.slice(2);

if (args.includes('--to-public') || args.includes('-p')) {
    syncToPublic();
} else if (args.includes('--to-root') || args.includes('-r')) {
    syncToRoot();
} else if (args.includes('--watch') || args.includes('-w')) {
    watchFile();
} else {
    // Default: sync root to public
    syncToPublic();
}

console.log('\n📋 Usage:');
console.log('  node smart-sync-index.js              # Sync root to public (default)');
console.log('  node smart-sync-index.js --to-public  # Sync root to public');
console.log('  node smart-sync-index.js --to-root    # Sync public to root');
console.log('  node smart-sync-index.js --watch      # Watch for changes and auto-sync');
console.log('  node smart-sync-index.js -p           # Short form: sync root to public');
console.log('  node smart-sync-index.js -r           # Short form: sync public to root');
console.log('  node smart-sync-index.js -w           # Short form: watch mode');
