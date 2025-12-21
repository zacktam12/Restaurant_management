<?php
/**
 * Database Connection Class
 * Handles MySQL database connections and operations
 */

class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "restaurant_management";
    private $connection;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute a SELECT query
     */
    public function select($query, $params = [], $param_types = "") {
        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        return $data;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    public function execute($query, $params = [], $param_types = "") {
        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        
        $result = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $insert_id = $stmt->insert_id;
        $stmt->close();
        
        return [
            'success' => $result,
            'affected_rows' => $affected_rows,
            'insert_id' => $insert_id
        ];
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->connection->autocommit(FALSE);
    }

    /**
     * Commit transaction
     */
    public function commit() {
        $this->connection->commit();
        $this->connection->autocommit(TRUE);
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->connection->rollback();
        $this->connection->autocommit(TRUE);
    }

    /**
     * Close database connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Escape string for safe queries
     */
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
}
?>