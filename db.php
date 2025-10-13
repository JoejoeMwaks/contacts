<?php
$host = "localhost";
$user = "root";  // default for XAMPP
$pass = "";      // default is empty unless you set a password
$dbname = "contacts"; // must match the name you created in phpMyAdmin

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
