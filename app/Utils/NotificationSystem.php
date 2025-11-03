<?php

namespace App\Utils;

use App\Config\Database;

/**
 * Notification System for Security Events
 * 
 * Handles notifications for password changes, admin logins,
 * and other security-related events.
 */
class NotificationSystem
{
    private $collection;
    
    public function __construct()
    {
        $db = Database::getInstance();
        $this->collection = $db->getCollection('notifications');
    }
    
    /**
     * Send notification for default password change
     * 
     * @param string $userId User ID who changed password
     * @param string $username Username
     * @param bool $wasDefaultPassword Whether it was a default password
     * @return bool Success status
     */
    public function notifyDefaultPasswordChange($userId, $username, $wasDefaultPassword = false)
    {
        try {
            $message = "Admin user '{$username}' has changed their password";
            if ($wasDefaultPassword) {
                $message .= " (from default password)";
            }
            
            $notification = [
                'type' => 'password_change',
                'title' => 'Password Changed',
                'message' => $message,
                'user_id' => $userId,
                'username' => $username,
                'priority' => 'high',
                'is_read' => false,
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
                'metadata' => [
                    'was_default_password' => $wasDefaultPassword,
                    'change_type' => $wasDefaultPassword ? 'default_to_custom' : 'custom_change'
                ]
            ];
            
            $result = $this->collection->insertOne($notification);
            return $result->getInsertedId() !== null;
            
        } catch (\Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification for admin login with default password
     * 
     * @param string $userId User ID
     * @param string $username Username
     * @return bool Success status
     */
    public function notifyDefaultPasswordLogin($userId, $username)
    {
        try {
            $notification = [
                'type' => 'security_warning',
                'title' => 'Security Warning: Default Password in Use',
                'message' => "Admin user '{$username}' is still using the default password. This is a security risk.",
                'user_id' => $userId,
                'username' => $username,
                'priority' => 'critical',
                'is_read' => false,
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
                'metadata' => [
                    'warning_type' => 'default_password_usage',
                    'requires_immediate_attention' => true
                ]
            ];
            
            $result = $this->collection->insertOne($notification);
            return $result->getInsertedId() !== null;
            
        } catch (\Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification for first admin login
     * 
     * @param string $userId User ID
     * @param string $username Username
     * @return bool Success status
     */
    public function notifyFirstLogin($userId, $username)
    {
        try {
            $notification = [
                'type' => 'first_login',
                'title' => 'First Admin Login',
                'message' => "Admin user '{$username}' has logged in for the first time.",
                'user_id' => $userId,
                'username' => $username,
                'priority' => 'medium',
                'is_read' => false,
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
                'metadata' => [
                    'event_type' => 'first_login',
                    'is_milestone' => true
                ]
            ];
            
            $result = $this->collection->insertOne($notification);
            return $result->getInsertedId() !== null;
            
        } catch (\Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification for failed login attempts
     * 
     * @param string $username Username attempted
     * @param int $attemptCount Number of failed attempts
     * @return bool Success status
     */
    public function notifyFailedLoginAttempts($username, $attemptCount)
    {
        try {
            $notification = [
                'type' => 'security_alert',
                'title' => 'Multiple Failed Login Attempts',
                'message' => "Multiple failed login attempts detected for username '{$username}' ({$attemptCount} attempts).",
                'user_id' => null,
                'username' => $username,
                'priority' => 'high',
                'is_read' => false,
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
                'metadata' => [
                    'attempt_count' => $attemptCount,
                    'potential_attack' => $attemptCount >= 5
                ]
            ];
            
            $result = $this->collection->insertOne($notification);
            return $result->getInsertedId() !== null;
            
        } catch (\Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notifications for admin dashboard
     * 
     * @param int $limit Number of notifications to return
     * @param bool $unreadOnly Only return unread notifications
     * @return array Notifications
     */
    public function getNotifications($limit = 10, $unreadOnly = false)
    {
        try {
            $filter = [];
            if ($unreadOnly) {
                $filter['is_read'] = false;
            }
            
            $cursor = $this->collection->find(
                $filter,
                [
                    'sort' => ['created_at' => -1],
                    'limit' => $limit
                ]
            );
            
            return $cursor->toArray();
            
        } catch (\Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark notification as read
     * 
     * @param string $notificationId Notification ID
     * @return bool Success status
     */
    public function markAsRead($notificationId)
    {
        try {
            $result = $this->collection->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId($notificationId)],
                ['$set' => ['is_read' => true, 'read_at' => new \MongoDB\BSON\UTCDateTime()]]
            );
            
            return $result->getModifiedCount() > 0;
            
        } catch (\Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read
     * 
     * @return bool Success status
     */
    public function markAllAsRead()
    {
        try {
            $result = $this->collection->updateMany(
                ['is_read' => false],
                ['$set' => ['is_read' => true, 'read_at' => new \MongoDB\BSON\UTCDateTime()]]
            );
            
            return $result->getModifiedCount() > 0;
            
        } catch (\Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification count
     * 
     * @param bool $unreadOnly Only count unread notifications
     * @return int Notification count
     */
    public function getNotificationCount($unreadOnly = false)
    {
        try {
            $filter = [];
            if ($unreadOnly) {
                $filter['is_read'] = false;
            }
            
            return $this->collection->countDocuments($filter);
            
        } catch (\Exception $e) {
            error_log("Error getting notification count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Clean up old notifications
     * 
     * @param int $retentionDays Number of days to retain notifications
     * @return int Number of deleted notifications
     */
    public function cleanupOldNotifications($retentionDays = 30)
    {
        try {
            $cutoffDate = new \MongoDB\BSON\UTCDateTime((time() - ($retentionDays * 24 * 60 * 60)) * 1000);
            
            $result = $this->collection->deleteMany([
                'created_at' => ['$lt' => $cutoffDate],
                'is_read' => true // Only delete read notifications
            ]);
            
            return $result->getDeletedCount();
            
        } catch (\Exception $e) {
            error_log("Error cleaning up notifications: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Send email notification (if email system is configured)
     * 
     * @param string $to Email address
     * @param string $subject Email subject
     * @param string $message Email message
     * @return bool Success status
     */
    public function sendEmailNotification($to, $subject, $message)
    {
        try {
            // This would integrate with your email system
            // For now, just log the notification
            error_log("Email notification would be sent to {$to}: {$subject}");
            return true;
            
        } catch (\Exception $e) {
            error_log("Error sending email notification: " . $e->getMessage());
            return false;
        }
    }
}
