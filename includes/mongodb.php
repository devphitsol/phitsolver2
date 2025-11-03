<?php
/**
 * MongoDB Connection Class for PHITSOL Partners Portal
 * Provides a simple interface for MongoDB operations
 */

// Define MongoDB constants if not already defined
if (!defined('MONGODB_HOST')) {
    define('MONGODB_HOST', 'localhost');
}
if (!defined('MONGODB_PORT')) {
    define('MONGODB_PORT', '27017');
}
if (!defined('MONGODB_DATABASE')) {
    define('MONGODB_DATABASE', 'phitsol_dashboard');
}

class MongoDBConnection {
    private $manager;
    private $database;
    
    public function __construct($connectionString = null, $database = null) {
        $connectionString = $connectionString ?: "mongodb://" . MONGODB_HOST . ":" . MONGODB_PORT;
        $this->database = $database ?: MONGODB_DATABASE;
        
        try {
            $this->manager = new MongoDB\Driver\Manager($connectionString);
        } catch (Exception $e) {
            throw new Exception("Failed to connect to MongoDB: " . $e->getMessage());
        }
    }
    
    /**
     * Insert a document into a collection
     */
    public function insert($collection, $document) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert($document);
            $result = $this->manager->executeBulkWrite($this->database . '.' . $collection, $bulk);
            return $result->getInsertedCount();
        } catch (Exception $e) {
            throw new Exception("Failed to insert document: " . $e->getMessage());
        }
    }
    
    /**
     * Find documents in a collection
     */
    public function find($collection, $filter = [], $options = []) {
        try {
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->database . '.' . $collection, $query);
            return $cursor->toArray();
        } catch (Exception $e) {
            throw new Exception("Failed to find documents: " . $e->getMessage());
        }
    }
    
    /**
     * Find one document in a collection
     */
    public function findOne($collection, $filter = []) {
        try {
            $query = new MongoDB\Driver\Query($filter, ['limit' => 1]);
            $cursor = $this->manager->executeQuery($this->database . '.' . $collection, $query);
            $documents = $cursor->toArray();
            return count($documents) > 0 ? $documents[0] : null;
        } catch (Exception $e) {
            throw new Exception("Failed to find document: " . $e->getMessage());
        }
    }
    
    /**
     * Update documents in a collection
     */
    public function update($collection, $filter, $update, $options = []) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update, $options);
            $result = $this->manager->executeBulkWrite($this->database . '.' . $collection, $bulk);
            return $result->getModifiedCount();
        } catch (Exception $e) {
            throw new Exception("Failed to update documents: " . $e->getMessage());
        }
    }
    
    /**
     * Delete documents from a collection
     */
    public function delete($collection, $filter, $options = []) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->delete($filter, $options);
            $result = $this->manager->executeBulkWrite($this->database . '.' . $collection, $bulk);
            return $result->getDeletedCount();
        } catch (Exception $e) {
            throw new Exception("Failed to delete documents: " . $e->getMessage());
        }
    }
    
    /**
     * Count documents in a collection
     */
    public function count($collection, $filter = []) {
        try {
            $command = new MongoDB\Driver\Command([
                'count' => $collection,
                'query' => (object)$filter
            ]);
            $cursor = $this->manager->executeCommand($this->database, $command);
            $result = $cursor->toArray()[0];
            return $result->n;
        } catch (Exception $e) {
            throw new Exception("Failed to count documents: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a command
     */
    public function executeCommand($command) {
        try {
            $cmd = new MongoDB\Driver\Command($command);
            $cursor = $this->manager->executeCommand($this->database, $cmd);
            return $cursor->toArray();
        } catch (Exception $e) {
            throw new Exception("Failed to execute command: " . $e->getMessage());
        }
    }
    
    /**
     * Test the connection
     */
    public function ping() {
        try {
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $cursor = $this->manager->executeCommand('admin', $command);
            $response = $cursor->toArray()[0];
            return isset($response->ok) && $response->ok == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database name
     */
    public function getDatabase() {
        return $this->database;
    }
    
    /**
     * Get manager instance
     */
    public function getManager() {
        return $this->manager;
    }
}

// Global MongoDB connection instance
$mongodb = null;

/**
 * Get MongoDB connection instance with fallback
 */
function getMongoDB() {
    global $mongodb;
    if ($mongodb === null) {
        if (extension_loaded('mongodb')) {
            try {
                $mongodb = new MongoDBConnection();
            } catch (Exception $e) {
                // If MongoDB connection fails, create a fallback
                $mongodb = new MongoDBFallback();
            }
        } else {
            // If MongoDB extension is not loaded, create a fallback
            $mongodb = new MongoDBFallback();
        }
    }
    return $mongodb;
}

/**
 * Helper function to convert MongoDB document to array
 */
function mongoToArray($document) {
    // Handle MongoDB BSONDocument
    if ($document instanceof MongoDB\Model\BSONDocument) {
        $array = $document->toArray();
    }
    // Handle stdClass objects
    elseif (is_object($document)) {
        $array = (array)$document;
    }
    // Already an array
    else {
        $array = $document;
    }
    
    // Convert ObjectId to string
    if (isset($array['_id'])) {
        if (is_array($array['_id'])) {
            // If _id is an array, convert it to JSON string
            $array['_id'] = json_encode($array['_id']);
        } else {
            // If _id is not an array, convert to string
            $array['_id'] = (string) $array['_id'];
        }
    }

    // Convert dates to readable format
    if (isset($array['created_at']) && $array['created_at'] instanceof MongoDB\BSON\UTCDateTime) {
        $array['created_at'] = $array['created_at']->toDateTime()->format('Y-m-d H:i:s');
    }
    
    if (isset($array['updated_at']) && $array['updated_at'] instanceof MongoDB\BSON\UTCDateTime) {
        $array['updated_at'] = $array['updated_at']->toDateTime()->format('Y-m-d H:i:s');
    }
    
    if (isset($array['published_at']) && $array['published_at'] instanceof MongoDB\BSON\UTCDateTime) {
        $array['published_at'] = $array['published_at']->toDateTime()->format('Y-m-d H:i:s');
    }

    return $array;
}

/**
 * Helper function to convert array to MongoDB document
 */
function arrayToMongo($array) {
    return $array; // MongoDB driver handles this automatically
}

/**
 * MongoDB Fallback Class
 * Provides a fallback when the MongoDB extension is not available
 */
class MongoDBFallback {
    private $error = null;
    
    public function __construct() {
        $this->error = 'MongoDB PHP extension is not installed or not loaded in the web server.';
    }
    
    public function insert($collection, $document) {
        throw new Exception($this->error);
    }
    
    public function find($collection, $filter = [], $options = []) {
        throw new Exception($this->error);
    }
    
    public function findOne($collection, $filter = [], $options = []) {
        throw new Exception($this->error);
    }
    
    public function update($collection, $filter, $update, $options = ['multi' => false]) {
        throw new Exception($this->error);
    }
    
    public function delete($collection, $filter, $options = ['limit' => 1]) {
        throw new Exception($this->error);
    }
    
    public function count($collection, $filter = []) {
        throw new Exception($this->error);
    }
    
    public function executeCommand($command) {
        throw new Exception($this->error);
    }
    
    public function getManager() {
        throw new Exception($this->error);
    }
    
    public function getDatabaseName() {
        return MONGODB_DATABASE;
    }
}
?>
