<?php
// Database configuration
$servername = "localhost";       // Your database server
$username = "root";              // Your database username
$password = "";                  // Your database password
$dbname = "carwash";  // Your database name

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
