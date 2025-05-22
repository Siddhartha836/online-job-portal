<?php
// Database credentials
$servername = "localhost";
$username = "root"; // Make sure this is your correct DB username
$password = ""; // Make sure this is your correct DB password
$dbname = "onlinejob"; // Make sure this matches the database name you created

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to utf8mb4
$conn->set_charset("utf8mb4");
?>