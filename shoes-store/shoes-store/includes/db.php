<?php
/**
 * Database Connection Class
 * PDO-based with prepared statement support
 */

class Database {
    private $pdo;
    private $stmt;
    
    /**
     * Constructor - Establish database connection
     */
    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (ENVIRONMENT === 'development') {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    /**
     * Execute a query with prepared statements
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $this->stmt = $this->pdo->prepare($sql);
            $this->stmt->execute($params);
            return $this->stmt;
        } catch (PDOException $e) {
            if (ENVIRONMENT === 'development') {
                throw new Exception("Query failed: " . $e->getMessage());
            } else {
                throw new Exception("Database operation failed.");
            }
        }
    }
    
    /**
     * Fetch single row
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        $this->query($sql, $params);
        return $this->stmt->fetch();
    }
    
    /**
     * Fetch all rows
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $this->query($sql, $params);
        return $this->stmt->fetchAll();
    }
    
    /**
     * Get last inserted ID
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get row count from last query
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
}
