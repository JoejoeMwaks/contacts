<?php
$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "";
$dbname = getenv('DB_NAME') ?: "contacts";
$port = getenv('DB_PORT') ?: "3306";

$conn = mysqli_init();

if (strpos($host, 'aivencloud.com') !== false) {
    // Aiven requires SSL
    $ca_cert = '/etc/ssl/certs/ca-certificates.crt';
    if (!file_exists($ca_cert)) {
        $ca_cert = NULL;
    }
    mysqli_ssl_set($conn, NULL, NULL, $ca_cert, NULL, NULL);
    $conn->real_connect($host, $user, $pass, $dbname, (int)$port, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
} else {
    $conn->real_connect($host, $user, $pass, $dbname, (int)$port);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

