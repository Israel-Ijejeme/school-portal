<?php
class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'school_dashboard';

    private $conn;
    private $stmt;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->conn->connect_error) {
            die('Connection failed: ' . $this->conn->connect_error);
        }
    }

    // Prepare the query
    public function query($sql) {
        $this->stmt = $this->conn->prept are($sql);
        if (!$this->stmt) {
            die('Query preparation failed: ' . $this->conn->error);
        }
        return $this->stmt; // Return the prepared statement
    }

    // Bind parameters
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = 'i'; // Integer
                    break;
                case is_float($value):
                    $type = 'd'; // Double
                    break;
                case is_bool($value):
                    $type = 'i'; // Boolean as integer
                    break;
                default:
                    $type = 's'; // String
            }
        }
        $this->stmt->bind_param($type, $value);
    }

    // Execute the statement
    public function execute() {
        return $this->stmt->execute();
    }

    // Get a single result
    public function single() {
        $this->execute();
        $result = $this->stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get multiple results
    public function resultSet() {
        $this->execute();
        $result = $this->stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Close the statement
    public function close() {
        $this->stmt->close();
    }

    // Close the connection
    public function __destruct() {
        $this->conn->close();
    }
}