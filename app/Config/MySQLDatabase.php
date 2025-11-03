<?php

namespace App\Config;

use PDO;
use PDOException;

class MySQLDatabase
{
    private static $instance = null;
    private $connection;
    private $database;
    private $useFileStorage = false;
    private $dataDir;

    private function __construct()
    {
        $this->loadEnvironment();
        $this->dataDir = __DIR__ . '/../../data';
        
        // Check if MySQL extension is available
        if (extension_loaded('pdo_mysql')) {
            $this->connect();
        } else {
            $this->useFileStorage = true;
            $this->initializeFileStorage();
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironment()
    {
        // First try to load from config.env file
        $configEnvFile = __DIR__ . '/../../config.env';
        if (file_exists($configEnvFile)) {
            $this->loadConfigFile($configEnvFile);
        }
        
        // Then try to load from .env file
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            try {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->load();
            } catch (\Exception $e) {
                error_log("Error loading .env file: " . $e->getMessage());
            }
        }
        
        // Set defaults if no environment variables are found
        $_ENV['MYSQL_HOST'] = $_ENV['MYSQL_HOST'] ?? 'localhost';
        $_ENV['MYSQL_PORT'] = $_ENV['MYSQL_PORT'] ?? '3306';
        $_ENV['MYSQL_DATABASE'] = $_ENV['MYSQL_DATABASE'] ?? 'phitsol_partners';
        $_ENV['MYSQL_USERNAME'] = $_ENV['MYSQL_USERNAME'] ?? 'root';
        $_ENV['MYSQL_PASSWORD'] = $_ENV['MYSQL_PASSWORD'] ?? '';
        $_ENV['MYSQL_CHARSET'] = $_ENV['MYSQL_CHARSET'] ?? 'utf8mb4';
        // Optional production/cPanel settings
        $_ENV['MYSQL_PERSISTENT'] = $_ENV['MYSQL_PERSISTENT'] ?? 'false';
        $_ENV['MYSQL_CONNECT_TIMEOUT'] = $_ENV['MYSQL_CONNECT_TIMEOUT'] ?? '10';
        $_ENV['MYSQL_READ_TIMEOUT'] = $_ENV['MYSQL_READ_TIMEOUT'] ?? '10';
        $_ENV['MYSQL_SSL_ENABLED'] = $_ENV['MYSQL_SSL_ENABLED'] ?? 'false';
        $_ENV['MYSQL_SSL_CA'] = $_ENV['MYSQL_SSL_CA'] ?? '';
    }
    
    private function loadConfigFile($filePath)
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $_ENV[$key] = $value;
            }
        }
    }

    private function connect()
    {
        try {
            // Double-check if MySQL extension is available
            if (!extension_loaded('pdo_mysql')) {
                throw new \Exception("MySQL PDO extension not available");
            }
            
            $host = $_ENV['MYSQL_HOST'] ?? 'localhost';
            $port = $_ENV['MYSQL_PORT'] ?? '3306';
            $database = $_ENV['MYSQL_DATABASE'] ?? 'phitsol_partners';
            $username = $_ENV['MYSQL_USERNAME'] ?? 'root';
            $password = $_ENV['MYSQL_PASSWORD'] ?? '';
            $charset = $_ENV['MYSQL_CHARSET'] ?? 'utf8mb4';

            // Validate configuration
            if (empty($database)) {
                throw new \Exception("MySQL database name not configured. Please update your configuration in config.env");
            }

            // Create DSN
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
            
            // PDO options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",
            ];

            // Persistent connections (optional)
            $persistent = strtolower((string)($_ENV['MYSQL_PERSISTENT'] ?? 'false')) === 'true';
            if ($persistent) {
                $options[PDO::ATTR_PERSISTENT] = true;
            }

            // Timeouts
            $connectTimeout = (int)($_ENV['MYSQL_CONNECT_TIMEOUT'] ?? 10);
            if ($connectTimeout > 0) {
                $options[PDO::ATTR_TIMEOUT] = $connectTimeout;
            }

            // SSL (for managed hosts/cPanel)
            $sslEnabled = strtolower((string)($_ENV['MYSQL_SSL_ENABLED'] ?? 'false')) === 'true';
            $sslCa = trim((string)($_ENV['MYSQL_SSL_CA'] ?? ''));
            if ($sslEnabled) {
                // Only set CA if provided; some hosts require it
                if (!empty($sslCa) && file_exists($sslCa)) {
                    $options[\PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
                }
                // Disable server cert verification only if CA not provided (last resort)
                if (empty($sslCa)) {
                    // @phpstan-ignore-next-line (constant may not exist in older PHP builds)
                    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                    }
                }
            }

            $this->connection = new PDO($dsn, $username, $password, $options);
            $this->database = $database;

            // Test connection
            $this->connection->query("SELECT 1");
            
            error_log("MySQL connection established successfully to: " . $database);
        } catch (\Exception $e) {
            error_log("MySQL connection failed: " . $e->getMessage() . ". Falling back to file storage.");
            // Fallback to file storage if MySQL connection fails
            $this->useFileStorage = true;
            $this->initializeFileStorage();
        }
    }

    private function initializeFileStorage()
    {
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
        
        // Create collections directory
        $collectionsDir = $this->dataDir . '/collections';
        if (!is_dir($collectionsDir)) {
            mkdir($collectionsDir, 0755, true);
        }
    }

    public function getConnection()
    {
        if ($this->useFileStorage) {
            return null;
        }
        return $this->connection;
    }

    public function getDatabase()
    {
        if ($this->useFileStorage) {
            return new \App\Config\FileDatabase($this->dataDir);
        }
        return $this->database;
    }

    public function getCollection($collectionName)
    {
        if ($this->useFileStorage) {
            return new \App\Config\FileCollection($this->dataDir, $collectionName);
        }
        return new MySQLCollection($this->connection, $collectionName);
    }

    public function isUsingFileStorage()
    {
        return $this->useFileStorage;
    }

    public function close()
    {
        if ($this->connection) {
            $this->connection = null;
        }
    }

    /**
     * Execute raw SQL query
     */
    public function query($sql, $params = [])
    {
        if ($this->useFileStorage) {
            throw new \Exception("Cannot execute SQL queries in file storage mode");
        }
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("MySQL query error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        if ($this->useFileStorage) {
            return null;
        }
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        if ($this->useFileStorage) {
            return false;
        }
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        if ($this->useFileStorage) {
            return false;
        }
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        if ($this->useFileStorage) {
            return false;
        }
        return $this->connection->rollback();
    }
}

// MySQL Collection class to mimic MongoDB collection interface
class MySQLCollection
{
    private $connection;
    private $tableName;
    private $primaryKey = 'id';

    public function __construct($connection, $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * Find documents (equivalent to MongoDB find)
     */
    public function find($filter = [], $options = [])
    {
        try {
            $sql = "SELECT * FROM {$this->tableName}";
            $params = [];
            
            if (!empty($filter)) {
                $whereClause = $this->buildWhereClause($filter, $params);
                $sql .= " WHERE {$whereClause}";
            }
            
            // Add sorting
            if (isset($options['sort'])) {
                $orderBy = $this->buildOrderByClause($options['sort']);
                $sql .= " ORDER BY {$orderBy}";
            }
            
            // Add limit and offset
            if (isset($options['limit'])) {
                $sql .= " LIMIT " . (int)$options['limit'];
                if (isset($options['skip'])) {
                    $sql .= " OFFSET " . (int)$options['skip'];
                }
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            
            // Convert to MongoDB-like format
            return new MySQLCursor($results);
        } catch (PDOException $e) {
            error_log("MySQL find error: " . $e->getMessage());
            return new MySQLCursor([]);
        }
    }

    /**
     * Find one document (equivalent to MongoDB findOne)
     */
    public function findOne($filter = [])
    {
        try {
            $sql = "SELECT * FROM {$this->tableName}";
            $params = [];
            
            if (!empty($filter)) {
                $whereClause = $this->buildWhereClause($filter, $params);
                $sql .= " WHERE {$whereClause}";
            }
            
            $sql .= " LIMIT 1";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("MySQL findOne error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Insert one document (equivalent to MongoDB insertOne)
     */
    public function insertOne($document)
    {
        try {
            // Remove MongoDB-specific fields
            unset($document['_id']);
            
            $columns = array_keys($document);
            $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);
            
            $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($document);
            
            return new MySQLInsertResult($this->connection->lastInsertId());
        } catch (PDOException $e) {
            error_log("MySQL insertOne error: " . $e->getMessage());
            return new MySQLInsertResult(null);
        }
    }

    /**
     * Update one document (equivalent to MongoDB updateOne)
     */
    public function updateOne($filter, $update)
    {
        try {
            $setData = $update['$set'] ?? $update;
            unset($setData['id']); // Prevent updating primary key
            
            $setClause = [];
            $params = [];
            
            foreach ($setData as $key => $value) {
                $setClause[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            
            $whereClause = $this->buildWhereClause($filter, $params);
            
            $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClause) . " WHERE {$whereClause} LIMIT 1";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return new MySQLUpdateResult($stmt->rowCount());
        } catch (PDOException $e) {
            error_log("MySQL updateOne error: " . $e->getMessage());
            return new MySQLUpdateResult(0);
        }
    }

    /**
     * Delete one document (equivalent to MongoDB deleteOne)
     */
    public function deleteOne($filter)
    {
        try {
            $params = [];
            $whereClause = $this->buildWhereClause($filter, $params);
            
            $sql = "DELETE FROM {$this->tableName} WHERE {$whereClause} LIMIT 1";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return new MySQLDeleteResult($stmt->rowCount());
        } catch (PDOException $e) {
            error_log("MySQL deleteOne error: " . $e->getMessage());
            return new MySQLDeleteResult(0);
        }
    }

    /**
     * Count documents (equivalent to MongoDB countDocuments)
     */
    public function countDocuments($filter = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}";
            $params = [];
            
            if (!empty($filter)) {
                $whereClause = $this->buildWhereClause($filter, $params);
                $sql .= " WHERE {$whereClause}";
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return (int)$result['count'];
        } catch (PDOException $e) {
            error_log("MySQL countDocuments error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get distinct values (equivalent to MongoDB distinct)
     */
    public function distinct($field, $filter = [])
    {
        try {
            $sql = "SELECT DISTINCT {$field} FROM {$this->tableName}";
            $params = [];
            
            if (!empty($filter)) {
                $whereClause = $this->buildWhereClause($filter, $params);
                $sql .= " WHERE {$whereClause}";
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return array_filter($results); // Remove null/empty values
        } catch (PDOException $e) {
            error_log("MySQL distinct error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Build WHERE clause from MongoDB-style filter
     */
    private function buildWhereClause($filter, &$params)
    {
        $conditions = [];
        
        foreach ($filter as $key => $value) {
            if ($key === '_id') {
                // Handle MongoDB ObjectId conversion
                $key = 'id';
            }
            
            if (is_array($value)) {
                // Handle MongoDB operators
                if (isset($value['$regex'])) {
                    $conditions[] = "{$key} LIKE :{$key}";
                    $params[$key] = '%' . $value['$regex'] . '%';
                } elseif (isset($value['$in'])) {
                    $placeholders = [];
                    foreach ($value['$in'] as $i => $v) {
                        $placeholder = "{$key}_in_{$i}";
                        $placeholders[] = ":{$placeholder}";
                        $params[$placeholder] = $v;
                    }
                    $conditions[] = "{$key} IN (" . implode(', ', $placeholders) . ")";
                } elseif (isset($value['$ne'])) {
                    $conditions[] = "{$key} != :{$key}";
                    $params[$key] = $value['$ne'];
                } else {
                    // Handle $or conditions
                    if ($key === '$or') {
                        $orConditions = [];
                        foreach ($value as $orFilter) {
                            $orConditions[] = $this->buildWhereClause($orFilter, $params);
                        }
                        $conditions[] = "(" . implode(' OR ', $orConditions) . ")";
                    }
                }
            } else {
                $conditions[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }
        
        return implode(' AND ', $conditions);
    }

    /**
     * Build ORDER BY clause from MongoDB-style sort
     */
    private function buildOrderByClause($sort)
    {
        $orderBy = [];
        
        foreach ($sort as $field => $direction) {
            $direction = $direction === -1 ? 'DESC' : 'ASC';
            $orderBy[] = "{$field} {$direction}";
        }
        
        return implode(', ', $orderBy);
    }
}

// Helper classes for MySQL operations
class MySQLCursor
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }
}

class MySQLInsertResult
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getInsertedId()
    {
        return $this->id;
    }
}

class MySQLUpdateResult
{
    private $modifiedCount;

    public function __construct($modifiedCount)
    {
        $this->modifiedCount = $modifiedCount;
    }

    public function getModifiedCount()
    {
        return $this->modifiedCount;
    }
}

class MySQLDeleteResult
{
    private $deletedCount;

    public function __construct($deletedCount)
    {
        $this->deletedCount = $deletedCount;
    }

    public function getDeletedCount()
    {
        return $this->deletedCount;
    }
}

// Note: File-based classes are defined in Database.php to avoid conflicts
