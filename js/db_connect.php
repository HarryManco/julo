<?php
// Database configuration
$servername = "127.0.0.1:3306";       // Your database server
$username = "u345178461_julo";              // Your database username
$password = "QdXA^V2Dg[dc@6qF";                  // Your database password
$dbname = "u345178461_julocarwash";  // Your database name

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
