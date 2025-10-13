<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Refresh user permissions from database
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT role, can_edit_contacts, can_delete_contacts, can_register_users FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Update session permissions
    $_SESSION['role'] = $user['role'];
    $_SESSION['can_edit_contacts'] = $user['can_edit_contacts'];
    $_SESSION['can_delete_contacts'] = $user['can_delete_contacts'];
    $_SESSION['can_register_users'] = $user['can_register_users'];
    
    // If super admin, grant all permissions
    if ($_SESSION['role'] === 'super') {
        $_SESSION['can_edit_contacts'] = 1;
        $_SESSION['can_delete_contacts'] = 1;
        $_SESSION['can_register_users'] = 1;
    }
}

$stmt->close();
$conn->close();

// Redirect back to previous page or dashboard
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
exit;
?>