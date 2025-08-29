<?php
// Database configuration
$host = "localhost";
$user = "root"; // Default XAMPP username
$pass = " ";     // Default XAMPP password is empty
// $pass = "teller";     // Default XAMPP password is empty
$dbname = "patronix_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8");
?>