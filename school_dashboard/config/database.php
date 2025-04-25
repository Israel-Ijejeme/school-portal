<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "school_dashboard";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> 