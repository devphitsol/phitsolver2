<?php

namespace App\Utils;

use App\Config\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * Audit Logger Utility Class
 * 
 * Handles logging of security-related events including password changes,
 * admin logins, and other sensitive operations.
 */
class AuditLogger
{
    private $collection;
    
    public function __construct()
    {
        $db = Database::getInstance();
        $this->collection = $db->getCollection('audit_logs');
    }
    
    /**
     * Log a security event
     * 
     * @param string $eventType Type of event (login, password_change, etc.)
     * @param string $userId User ID who performed the action
     * @param string $description Event description
     * @param array $metadata Additional metadata
     * @return bool Success status
     */
    public function logEvent($eventType, $userId, $description, $metadata = [])
    {
        try {
            $logEntry = [
                'event_type' => $eventType,
                'user_id' => $userId,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'timestamp' => new UTCDateTime(),
                'metadata' => $metadata,
                'session_id' => session_id() ?: 'unknown'
            ];
            
            $result = $this->collection->insertOne($logEntry);
            return $result->getInsertedId() !== null;
            
        } catch (\Exception $e) {
            error_log("Audit logging error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log admin login event
     * 
     * @param string $userId User ID
     * @param string $username Username
     * @param bool $isFirstLogin Whether this is the first login
     * @param bool $isDefaultPassword Whether using default password
     * @return bool Success status
     */
    public function logAdminLogin($userId, $username, $isFirstLogin = false, $isDefaultPassword = false)
    {
        $description = "Admin login: {$username}";
        if ($isFirstLogin) {
            $description .= " (First login)";
        }
        if ($isDefaultPassword) {
            $description .= " (Using default password - CHANGE REQUIRED)";
        }
        
        return $this->logEvent('admin_login', $userId, $description, [
            'username' => $username,
            'is_first_login' => $isFirstLogin,
            'is_default_password' => $isDefaultPassword
        ]);
    }
    
    /**
     * Log password change event
     * 
     * @param string $userId User ID
     * @param string $username Username
     * @param bool $isDefaultPasswordChange Whether changing from default password
     * @param bool $isForcedChange Whether this was a forced password change
     * @return bool Success status
     */
    public function logPasswordChange($userId, $username, $isDefaultPasswordChange = false, $isForcedChange = false)
    {
        $description = "Password changed for user: {$username}";
        if ($isDefaultPasswordChange) {
            $description .= " (Default password changed)";
        }
        if ($isForcedChange) {
            $description .= " (Forced change)";
        }
        
        return $this->logEvent('password_change', $userId, $description, [
            'username' => $username,
            'is_default_password_change' => $isDefaultPasswordChange,
            'is_forced_change' => $isForcedChange
        ]);
    }
    
    /**
     * Log default password usage warning
     * 
     * @param string $userId User ID
     * @param string $username Username
     * @return bool Success status
     */
    public function logDefaultPasswordWarning($userId, $username)
    {
        return $this->logEvent('default_password_warning', $userId, 
            "SECURITY WARNING: User {$username} is still using default password", [
                'username' => $username,
                'severity' => 'high'
            ]);
    }
    
    /**
     * Log failed login attempt
     * 
     * @param string $username Username attempted
     * @param string $reason Failure reason
     * @return bool Success status
     */
    public function logFailedLogin($username, $reason = 'Invalid credentials')
    {
        return $this->logEvent('failed_login', null, 
            "Failed login attempt for: {$username} - {$reason}", [
                'username' => $username,
                'reason' => $reason
            ]);
    }
    
    /**
     * Get audit logs with filtering
     * 
     * @param array $filters Filter criteria
     * @param int $limit Number of records to return
     * @param int $skip Number of records to skip
     * @return array Audit logs
     */
    public function getAuditLogs($filters = [], $limit = 100, $skip = 0)
    {
        try {
            $query = [];
            
            if (isset($filters['event_type'])) {
                $query['event_type'] = $filters['event_type'];
            }
            
            if (isset($filters['user_id'])) {
                $query['user_id'] = $filters['user_id'];
            }
            
            if (isset($filters['date_from']) || isset($filters['date_to'])) {
                $query['timestamp'] = [];
                if (isset($filters['date_from'])) {
                    $query['timestamp']['$gte'] = new UTCDateTime(strtotime($filters['date_from']) * 1000);
                }
                if (isset($filters['date_to'])) {
                    $query['timestamp']['$lte'] = new UTCDateTime(strtotime($filters['date_to']) * 1000);
                }
            }
            
            $cursor = $this->collection->find(
                $query,
                [
                    'sort' => ['timestamp' => -1],
                    'limit' => $limit,
                    'skip' => $skip
                ]
            );
            
            return $cursor->toArray();
            
        } catch (\Exception $e) {
            error_log("Error retrieving audit logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean up old audit logs
     * 
     * @param int $retentionDays Number of days to retain logs
     * @return int Number of deleted records
     */
    public function cleanupOldLogs($retentionDays = 365)
    {
        try {
            $cutoffDate = new UTCDateTime((time() - ($retentionDays * 24 * 60 * 60)) * 1000);
            
            $result = $this->collection->deleteMany([
                'timestamp' => ['$lt' => $cutoffDate]
            ]);
            
            return $result->getDeletedCount();
            
        } catch (\Exception $e) {
            error_log("Error cleaning up audit logs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get security statistics
     * 
     * @param int $days Number of days to analyze
     * @return array Security statistics
     */
    public function getSecurityStats($days = 30)
    {
        try {
            $startDate = new UTCDateTime((time() - ($days * 24 * 60 * 60)) * 1000);
            
            $pipeline = [
                ['$match' => ['timestamp' => ['$gte' => $startDate]]],
                ['$group' => [
                    '_id' => '$event_type',
                    'count' => ['$sum' => 1]
                ]]
            ];
            
            $cursor = $this->collection->aggregate($pipeline);
            $stats = [];
            
            foreach ($cursor as $doc) {
                $stats[$doc['_id']] = $doc['count'];
            }
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Error getting security stats: " . $e->getMessage());
            return [];
        }
    }
}
