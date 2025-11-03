#!/usr/bin/env node

/**
 * PHITSOL Index.html Synchronization Script
 * This script ensures that public/index.html stays in sync with root index.html
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

function syncFiles() {
    console.log('🔄 Syncing index.html to public/index.html...');
    
    if (!fs.existsSync(rootFile)) {
        console.error('❌ Error: index.html not found in root directory!');
        process.exit(1);
    }
    
    // Ensure public directory exists
    const publicDir = path.dirname(publicFile);
    if (!fs.existsSync(publicDir)) {
        fs.mkdirSync(publicDir, { recursive: true });
    }
    
    // Copy file
    fs.copyFileSync(rootFile, publicFile);
    console.log('✅ Synchronization complete!');
    
    // Verify files are identical
    const rootHash = getFileHash(rootFile);
    const publicHash = getFileHash(publicFile);
    
    if (rootHash === publicHash) {
        console.log(`✅ Files are identical (Hash: ${rootHash.substring(0, 8)}...)`);
    } else {
        console.log('❌ Warning: Files are different after sync!');
    }
}

function watchFile() {
    console.log('👀 Watching index.html for changes...');
    console.log('Press Ctrl+C to stop watching');
    
    fs.watchFile(rootFile, { interval: 1000 }, (curr, prev) => {
        if (curr.mtime !== prev.mtime) {
            console.log(`\n📝 index.html changed at ${new Date().toLocaleString()}`);
            syncFiles();
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

if (args.includes('--watch') || args.includes('-w')) {
    watchFile();
} else {
    syncFiles();
}

console.log('\n📋 Usage:');
console.log('  node sync-index.js          # Sync once');
console.log('  node sync-index.js --watch   # Watch for changes and auto-sync');
console.log('  node sync-index.js -w        # Watch for changes and auto-sync');
