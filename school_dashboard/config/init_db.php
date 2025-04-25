<?php
// Database initialization file
require_once 'database.php';

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
    exit;
}

// Close initial connection
$conn->close();

// Connect to the newly created database
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read the SQL file
$sql_file = file_get_contents('setup.sql');

// Split SQL file into individual statements
$statements = explode(';', $sql_file);

// Execute each statement
$error = false;
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if ($conn->query($statement) !== TRUE) {
            echo "Error executing statement: " . $conn->error . "<br>";
            echo "Statement: " . $statement . "<br>";
            $error = true;
        }
    }
}

if (!$error) {
    echo "Database setup completed successfully!<br>";
}

// Close connection
$conn->close();
?> 