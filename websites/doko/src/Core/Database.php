<?php
namespace Core;

/**
 * Database Connection Class
 * Handles all database connections and operations
 */
class Database {
    private $connection;
    private $config;
    
    public function __construct() {
        // Load database configuration
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->connect();
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['name']};charset=utf8mb4";
            $this->connection = new \PDO($dsn, $this->config['user'], $this->config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a query and return results
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Fetch all results from a query
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Fetch single result from a query
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Insert data and return last insert ID
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update data in table
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        return $this->query($sql, $params);
    }
    
    /**
     * Delete data from table
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $whereParams);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Get row count from last query
     */
    public function rowCount() {
        return $this->connection->rowCount();
    }
}
?>
