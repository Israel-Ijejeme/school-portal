<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "school_dashboard";
    private $conn;
    
    // Constructor
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    // Get database connection
    public function getConnection() {
        return $this->conn;
    }
    
    // Execute a query and return the result
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    // Execute a prepared statement
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    // Get the last inserted ID
    public function getLastId() {
        return $this->conn->insert_id;
    }
    
    // Close the database connection
    public function close() {
        $this->conn->close();
    }
    
    // Escape string for safety
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
}
?> 