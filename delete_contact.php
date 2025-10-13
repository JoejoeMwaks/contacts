<?php
session_start();
include 'db_connection.php';

// Ensure only logged-in admins can access
if (!isset($_SESSION['admin_id']) || empty($_SESSION['is_active'])) {
    header("Location: login.php");
    exit;
}

// Check if admin has delete permissions OR is super admin
if ($_SESSION['role'] !== 'super' && empty($_SESSION['can_delete_contacts'])) {
    die("Access denied. You do not have permission to delete contacts.");
}


// Validate contact ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid contact ID.");
}

$id = intval($_GET['id']);
$admin_id = $_SESSION['admin_id'];

// Fetch admin role and delete permission
$stmt = $conn->prepare("SELECT role, can_delete_results FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($role, $can_delete_results);
$stmt->fetch();
$stmt->close();

// Restrict delete permission
if ($role !== 'super' && !$can_delete_results) {
    die("Access denied. You do not have permission to delete contacts.");
}

// Perform delete
$stmt = $conn->prepare("DELETE FROM contact WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Redirect back to dashboard with success message
    header("Location: dashboard.php?msg=contact_deleted");
    exit;
} else {
    die("Failed to delete contact. Please try again.");
}
