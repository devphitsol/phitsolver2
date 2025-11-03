<?php

namespace App\Models;

use App\Config\Database;
use App\Utils\EmailService;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class SupportMessage
{
    private $collection;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->collection = $db->getCollection('support_messages');
    }

    /**
     * Get all support messages
     */
    public function getAll()
    {
        try {
            $cursor = $this->collection->find(
                [],
                ['sort' => ['created_at' => -1]]
            );
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching support messages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get support messages by user ID
     */
    public function getByUserId($userId)
    {
        try {
            $cursor = $this->collection->find(
                ['user_id' => $userId],
                ['sort' => ['created_at' => -1]]
            );
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching support messages by user ID: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get support message by ID
     */
    public function getById($id)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            $message = $this->collection->findOne(['_id' => $id]);
            return $message ? (array) $message : null;
        } catch (\Exception $e) {
            error_log("Error fetching support message by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new support message
     */
    public function create($data)
    {
        try {
            // Set default values
            $data['created_at'] = $this->getCurrentDateTime();
            $data['updated_at'] = $this->getCurrentDateTime();
            $data['status'] = 'pending';
            $data['admin_reply'] = null;
            $data['admin_reply_date'] = null;

            $result = $this->collection->insertOne($data);
            return $result->getInsertedId();
        } catch (\Exception $e) {
            error_log("Error creating support message: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update support message
     */
    public function update($id, $data)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            $data['updated_at'] = $this->getCurrentDateTime();
            
            $result = $this->collection->updateOne(
                ['_id' => $id],
                ['$set' => $data]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating support message: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add admin reply to support message
     */
    public function addAdminReply($id, $reply)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            // Get the original message to send email notification
            $originalMessage = $this->getById($id);
            if (!$originalMessage) {
                throw new \Exception('Support message not found');
            }
            
            $result = $this->collection->updateOne(
                ['_id' => $id],
                [
                    '$set' => [
                        'admin_reply' => $reply,
                        'admin_reply_date' => $this->getCurrentDateTime(),
                        'status' => 'replied',
                        'updated_at' => $this->getCurrentDateTime()
                    ]
                ]
            );
            
            if ($result->getModifiedCount() > 0) {
                // Send email notification to user
                $this->sendReplyNotification($originalMessage, $reply);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error adding admin reply: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send email notification to user when admin replies
     */
    private function sendReplyNotification($message, $adminReply)
    {
        try {
            // Only send email if user email is available
            if (!empty($message['user_email']) && !empty($message['user_name'])) {
                EmailService::sendSupportReplyNotification(
                    $message['user_email'],
                    $message['user_name'],
                    $message['subject'],
                    $adminReply,
                    $message['message']
                );
                
                error_log("Support reply notification sent to: " . $message['user_email']);
            }
        } catch (\Exception $e) {
            error_log("Error sending support reply notification: " . $e->getMessage());
            // Don't throw the exception as email failure shouldn't break the reply functionality
        }
    }

    /**
     * Delete support message
     */
    public function delete($id)
    {
        try {
            // Convert string ID to ObjectId if needed
            if (is_string($id)) {
                $id = new ObjectId($id);
            }
            
            $result = $this->collection->deleteOne(['_id' => $id]);
            return $result->getDeletedCount() > 0;
        } catch (\Exception $e) {
            error_log("Error deleting support message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get pending support messages count
     */
    public function getPendingCount()
    {
        try {
            return $this->collection->countDocuments(['status' => 'pending']);
        } catch (\Exception $e) {
            error_log("Error counting pending support messages: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get replied support messages count
     */
    public function getRepliedCount()
    {
        try {
            return $this->collection->countDocuments(['status' => 'replied']);
        } catch (\Exception $e) {
            error_log("Error counting replied support messages: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total support messages count
     */
    public function getCount()
    {
        try {
            return $this->collection->countDocuments([]);
        } catch (\Exception $e) {
            error_log("Error counting support messages: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get support messages by status
     */
    public function getByStatus($status)
    {
        try {
            $cursor = $this->collection->find(
                ['status' => $status],
                ['sort' => ['created_at' => -1]]
            );
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching support messages by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent support messages (last 7 days)
     */
    public function getRecent($days = 7)
    {
        try {
            $date = new \DateTime();
            $date->modify("-{$days} days");
            
            $cursor = $this->collection->find(
                ['created_at' => ['$gte' => $this->getCurrentDateTime($date)]],
                ['sort' => ['created_at' => -1]]
            );
            return $cursor->toArray();
        } catch (\Exception $e) {
            error_log("Error fetching recent support messages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate support message data
     */
    public function validate($data)
    {
        $errors = [];

        if (empty($data['subject'])) {
            $errors[] = 'Subject is required';
        }

        if (empty($data['message'])) {
            $errors[] = 'Message is required';
        }

        if (empty($data['purpose'])) {
            $errors[] = 'Purpose is required';
        }

        if (empty($data['user_id'])) {
            $errors[] = 'User ID is required';
        }

        // Validate subcategory for RENTAL and RENT TO OWN
        if (($data['purpose'] === 'RENTAL' || $data['purpose'] === 'RENT TO OWN') && empty($data['subcategory'])) {
            $errors[] = 'Equipment type is required for rental inquiries';
        }

        return $errors;
    }

    /**
     * Get current date time in appropriate format
     */
    private function getCurrentDateTime($date = null)
    {
        if ($date === null) {
            $date = new \DateTime();
        }
        
        $db = Database::getInstance();
        if ($db->isUsingFileStorage()) {
            return $date->format('Y-m-d H:i:s');
        } else {
            // For MongoDB, use UTCDateTime
            if (extension_loaded('mongodb')) {
                return new \MongoDB\BSON\UTCDateTime($date->getTimestamp() * 1000);
            } else {
                return $date->format('Y-m-d H:i:s');
            }
        }
    }
} 