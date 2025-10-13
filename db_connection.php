<?php
$servername = "localhost";   // XAMPP default
$username   = "root";        // XAMPP default (no password)
$password   = "";            // leave empty unless you set a root password
$dbname     = "contacts";    // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
