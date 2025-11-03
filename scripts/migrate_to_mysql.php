<?php
/**
 * MongoDB to MySQL Migration Script
 * This script migrates data from MongoDB collections to MySQL tables
 */

// Add custom PHP include path
ini_set("include_path", '/home/qiimy7odbu3s/php:' . ini_get("include_path"));

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Config\MySQLDatabase;

class MongoDBToMySQLMigration
{
    private $sourceDb;
    private $targetDb;
    private $migrationLog = [];
    
    public function __construct()
    {
        // Initialize source database (MongoDB)
        $this->sourceDb = Database::getInstance();
        
        // Initialize target database (MySQL)
        $this->targetDb = MySQLDatabase::getInstance();
    }
    
    /**
     * Run the complete migration
     */
    public function migrate()
    {
        echo "Starting MongoDB to MySQL migration...\n";
        echo "=====================================\n\n";
        
        try {
            // Check if source is using file storage (MongoDB fallback)
            if ($this->sourceDb->isUsingFileStorage()) {
                echo "⚠️  Source database is using file storage. Migrating from JSON files...\n\n";
                $this->migrateFromFileStorage();
            } else {
                echo "📊 Source database is MongoDB. Migrating from collections...\n\n";
                $this->migrateFromMongoDB();
            }
            
            echo "\n✅ Migration completed successfully!\n";
            $this->printMigrationSummary();
            
        } catch (Exception $e) {
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
        
        return true;
    }
    
    /**
     * Migrate from MongoDB collections
     */
    private function migrateFromMongoDB()
    {
        // Migrate users
        $this->migrateUsers();
        
        // Migrate blog posts
        $this->migrateBlogPosts();
        
        // Migrate sliders
        $this->migrateSliders();
        
        // Migrate support messages
        $this->migrateSupportMessages();
    }
    
    /**
     * Migrate from file storage (JSON files)
     */
    private function migrateFromFileStorage()
    {
        // Migrate users from JSON
        $this->migrateUsersFromFile();
        
        // Migrate blog posts from JSON
        $this->migrateBlogPostsFromFile();
        
        // Migrate sliders from JSON
        $this->migrateSlidersFromFile();
        
        // Migrate support messages from JSON
        $this->migrateSupportMessagesFromFile();
    }
    
    /**
     * Migrate users collection
     */
    private function migrateUsers()
    {
        echo "👥 Migrating users...\n";
        
        try {
            $usersCollection = $this->sourceDb->getCollection('users');
            $users = $usersCollection->find()->toArray();
            
            $targetCollection = $this->targetDb->getCollection('users');
            $migratedCount = 0;
            
            foreach ($users as $user) {
                $userData = $this->transformUserData($user);
                $result = $targetCollection->insertOne($userData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['users'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} users\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating users: " . $e->getMessage() . "\n";
            $this->migrationLog['users'] = 0;
        }
    }
    
    /**
     * Migrate users from JSON file
     */
    private function migrateUsersFromFile()
    {
        echo "👥 Migrating users from JSON file...\n";
        
        try {
            $usersFile = __DIR__ . '/../data/collections/users.json';
            if (!file_exists($usersFile)) {
                echo "⚠️  No users.json file found\n";
                $this->migrationLog['users'] = 0;
                return;
            }
            
            $usersData = json_decode(file_get_contents($usersFile), true);
            if (empty($usersData)) {
                echo "⚠️  No users data found in JSON file\n";
                $this->migrationLog['users'] = 0;
                return;
            }
            
            $targetCollection = $this->targetDb->getCollection('users');
            $migratedCount = 0;
            
            foreach ($usersData as $user) {
                $userData = $this->transformUserData($user);
                $result = $targetCollection->insertOne($userData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['users'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} users from JSON\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating users from JSON: " . $e->getMessage() . "\n";
            $this->migrationLog['users'] = 0;
        }
    }
    
    /**
     * Migrate blog posts collection
     */
    private function migrateBlogPosts()
    {
        echo "📝 Migrating blog posts...\n";
        
        try {
            $blogCollection = $this->sourceDb->getCollection('blog_posts');
            $posts = $blogCollection->find()->toArray();
            
            $targetCollection = $this->targetDb->getCollection('blog_posts');
            $migratedCount = 0;
            
            foreach ($posts as $post) {
                $postData = $this->transformBlogPostData($post);
                $result = $targetCollection->insertOne($postData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['blog_posts'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} blog posts\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating blog posts: " . $e->getMessage() . "\n";
            $this->migrationLog['blog_posts'] = 0;
        }
    }
    
    /**
     * Migrate blog posts from JSON file
     */
    private function migrateBlogPostsFromFile()
    {
        echo "📝 Migrating blog posts from JSON file...\n";
        
        try {
            $blogFile = __DIR__ . '/../data/collections/blogs.json';
            if (!file_exists($blogFile)) {
                echo "⚠️  No blogs.json file found\n";
                $this->migrationLog['blog_posts'] = 0;
                return;
            }
            
            $blogData = json_decode(file_get_contents($blogFile), true);
            if (empty($blogData)) {
                echo "⚠️  No blog data found in JSON file\n";
                $this->migrationLog['blog_posts'] = 0;
                return;
            }
            
            $targetCollection = $this->targetDb->getCollection('blog_posts');
            $migratedCount = 0;
            
            foreach ($blogData as $post) {
                $postData = $this->transformBlogPostData($post);
                $result = $targetCollection->insertOne($postData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['blog_posts'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} blog posts from JSON\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating blog posts from JSON: " . $e->getMessage() . "\n";
            $this->migrationLog['blog_posts'] = 0;
        }
    }
    
    /**
     * Migrate sliders collection
     */
    private function migrateSliders()
    {
        echo "🖼️  Migrating sliders...\n";
        
        try {
            $slidersCollection = $this->sourceDb->getCollection('sliders');
            $sliders = $slidersCollection->find()->toArray();
            
            $targetCollection = $this->targetDb->getCollection('sliders');
            $migratedCount = 0;
            
            foreach ($sliders as $slider) {
                $sliderData = $this->transformSliderData($slider);
                $result = $targetCollection->insertOne($sliderData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['sliders'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} sliders\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating sliders: " . $e->getMessage() . "\n";
            $this->migrationLog['sliders'] = 0;
        }
    }
    
    /**
     * Migrate sliders from JSON file
     */
    private function migrateSlidersFromFile()
    {
        echo "🖼️  Migrating sliders from JSON file...\n";
        
        try {
            $slidersFile = __DIR__ . '/../data/collections/sliders.json';
            if (!file_exists($slidersFile)) {
                echo "⚠️  No sliders.json file found\n";
                $this->migrationLog['sliders'] = 0;
                return;
            }
            
            $slidersData = json_decode(file_get_contents($slidersFile), true);
            if (empty($slidersData)) {
                echo "⚠️  No sliders data found in JSON file\n";
                $this->migrationLog['sliders'] = 0;
                return;
            }
            
            $targetCollection = $this->targetDb->getCollection('sliders');
            $migratedCount = 0;
            
            foreach ($slidersData as $slider) {
                $sliderData = $this->transformSliderData($slider);
                $result = $targetCollection->insertOne($sliderData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['sliders'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} sliders from JSON\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating sliders from JSON: " . $e->getMessage() . "\n";
            $this->migrationLog['sliders'] = 0;
        }
    }
    
    /**
     * Migrate support messages collection
     */
    private function migrateSupportMessages()
    {
        echo "💬 Migrating support messages...\n";
        
        try {
            $messagesCollection = $this->sourceDb->getCollection('support_messages');
            $messages = $messagesCollection->find()->toArray();
            
            $targetCollection = $this->targetDb->getCollection('support_messages');
            $migratedCount = 0;
            
            foreach ($messages as $message) {
                $messageData = $this->transformSupportMessageData($message);
                $result = $targetCollection->insertOne($messageData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['support_messages'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} support messages\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating support messages: " . $e->getMessage() . "\n";
            $this->migrationLog['support_messages'] = 0;
        }
    }
    
    /**
     * Migrate support messages from JSON file
     */
    private function migrateSupportMessagesFromFile()
    {
        echo "💬 Migrating support messages from JSON file...\n";
        
        try {
            $messagesFile = __DIR__ . '/../data/collections/support_messages.json';
            if (!file_exists($messagesFile)) {
                echo "⚠️  No support_messages.json file found\n";
                $this->migrationLog['support_messages'] = 0;
                return;
            }
            
            $messagesData = json_decode(file_get_contents($messagesFile), true);
            if (empty($messagesData)) {
                echo "⚠️  No support messages data found in JSON file\n";
                $this->migrationLog['support_messages'] = 0;
                return;
            }
            
            $targetCollection = $this->targetDb->getCollection('support_messages');
            $migratedCount = 0;
            
            foreach ($messagesData as $message) {
                $messageData = $this->transformSupportMessageData($message);
                $result = $targetCollection->insertOne($messageData);
                
                if ($result->getInsertedId()) {
                    $migratedCount++;
                }
            }
            
            $this->migrationLog['support_messages'] = $migratedCount;
            echo "✅ Migrated {$migratedCount} support messages from JSON\n";
            
        } catch (Exception $e) {
            echo "❌ Error migrating support messages from JSON: " . $e->getMessage() . "\n";
            $this->migrationLog['support_messages'] = 0;
        }
    }
    
    /**
     * Transform user data from MongoDB to MySQL format
     */
    private function transformUserData($user)
    {
        $userData = [
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'password' => $user['password'] ?? '',
            'name' => $user['name'] ?? '',
            'first_name' => $user['first_name'] ?? null,
            'last_name' => $user['last_name'] ?? null,
            'role' => $user['role'] ?? 'employee',
            'status' => $user['status'] ?? 'active',
            'phone' => $user['phone'] ?? null,
            'company' => $user['company'] ?? null,
            'position' => $user['position'] ?? null,
            'address' => $user['address'] ?? null,
            'city' => $user['city'] ?? null,
            'state' => $user['state'] ?? null,
            'country' => $user['country'] ?? null,
            'postal_code' => $user['postal_code'] ?? null,
            'website' => $user['website'] ?? null,
            'bio' => $user['bio'] ?? null,
            'avatar' => $user['avatar'] ?? null,
            'is_default_password' => $user['is_default_password'] ?? false,
            'password_change_required' => $user['password_change_required'] ?? false,
            'is_first_login' => $user['is_first_login'] ?? true,
            'login_count' => $user['login_count'] ?? 0
        ];
        
        // Handle dates
        if (isset($user['last_login'])) {
            $userData['last_login'] = $this->convertDate($user['last_login']);
        }
        
        if (isset($user['created_at'])) {
            $userData['created_at'] = $this->convertDate($user['created_at']);
        }
        
        if (isset($user['updated_at'])) {
            $userData['updated_at'] = $this->convertDate($user['updated_at']);
        }
        
        // Handle JSON fields
        if (isset($user['password_history'])) {
            $userData['password_history'] = json_encode($user['password_history']);
        }
        
        if (isset($user['document_status'])) {
            $userData['document_status'] = json_encode($user['document_status']);
        }
        
        if (isset($user['documents'])) {
            $userData['documents'] = json_encode($user['documents']);
        }
        
        return $userData;
    }
    
    /**
     * Transform blog post data from MongoDB to MySQL format
     */
    private function transformBlogPostData($post)
    {
        $postData = [
            'title' => $post['title'] ?? '',
            'slug' => $post['slug'] ?? '',
            'content' => $post['content'] ?? '',
            'excerpt' => $post['excerpt'] ?? null,
            'status' => $post['status'] ?? 'draft',
            'type' => $post['type'] ?? 'post',
            'category' => $post['category'] ?? null,
            'featured' => $post['featured'] ?? false,
            'views' => $post['views'] ?? 0,
            'author_id' => $post['author_id'] ?? null,
            'featured_image' => $post['featured_image'] ?? null,
            'video_url' => $post['video_url'] ?? null,
            'meta_title' => $post['meta_title'] ?? null,
            'meta_description' => $post['meta_description'] ?? null
        ];
        
        // Handle dates
        if (isset($post['created_at'])) {
            $postData['created_at'] = $this->convertDate($post['created_at']);
        }
        
        if (isset($post['updated_at'])) {
            $postData['updated_at'] = $this->convertDate($post['updated_at']);
        }
        
        if (isset($post['published_at'])) {
            $postData['published_at'] = $this->convertDate($post['published_at']);
        }
        
        // Handle JSON fields
        if (isset($post['tags'])) {
            $postData['tags'] = json_encode($post['tags']);
        }
        
        return $postData;
    }
    
    /**
     * Transform slider data from MongoDB to MySQL format
     */
    private function transformSliderData($slider)
    {
        $sliderData = [
            'title' => $slider['title'] ?? '',
            'description' => $slider['description'] ?? null,
            'image' => $slider['image'] ?? '',
            'link' => $slider['link'] ?? null,
            'button_text' => $slider['button_text'] ?? null,
            'order_index' => $slider['order_index'] ?? 0,
            'status' => $slider['status'] ?? 'active'
        ];
        
        // Handle dates
        if (isset($slider['created_at'])) {
            $sliderData['created_at'] = $this->convertDate($slider['created_at']);
        }
        
        if (isset($slider['updated_at'])) {
            $sliderData['updated_at'] = $this->convertDate($slider['updated_at']);
        }
        
        return $sliderData;
    }
    
    /**
     * Transform support message data from MongoDB to MySQL format
     */
    private function transformSupportMessageData($message)
    {
        $messageData = [
            'name' => $message['name'] ?? '',
            'email' => $message['email'] ?? '',
            'phone' => $message['phone'] ?? null,
            'company' => $message['company'] ?? null,
            'subject' => $message['subject'] ?? '',
            'message' => $message['message'] ?? '',
            'status' => $message['status'] ?? 'new',
            'priority' => $message['priority'] ?? 'medium',
            'assigned_to' => $message['assigned_to'] ?? null,
            'response' => $message['response'] ?? null
        ];
        
        // Handle dates
        if (isset($message['created_at'])) {
            $messageData['created_at'] = $this->convertDate($message['created_at']);
        }
        
        if (isset($message['updated_at'])) {
            $messageData['updated_at'] = $this->convertDate($message['updated_at']);
        }
        
        if (isset($message['resolved_at'])) {
            $messageData['resolved_at'] = $this->convertDate($message['resolved_at']);
        }
        
        return $messageData;
    }
    
    /**
     * Convert MongoDB date to MySQL format
     */
    private function convertDate($date)
    {
        if (is_object($date) && method_exists($date, 'toDateTime')) {
            // MongoDB UTCDateTime
            return $date->toDateTime()->format('Y-m-d H:i:s');
        } elseif (is_string($date)) {
            // String date
            return date('Y-m-d H:i:s', strtotime($date));
        } else {
            // Default to current time
            return date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Print migration summary
     */
    private function printMigrationSummary()
    {
        echo "\n📊 Migration Summary:\n";
        echo "====================\n";
        
        foreach ($this->migrationLog as $table => $count) {
            echo "• {$table}: {$count} records\n";
        }
        
        $total = array_sum($this->migrationLog);
        echo "\nTotal records migrated: {$total}\n";
    }
}

// Run migration if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $migration = new MongoDBToMySQLMigration();
    $migration->migrate();
}
?>
