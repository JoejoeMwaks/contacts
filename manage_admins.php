<?php
session_start();
include 'db_connection';

// Only active super admins can access
if (
    !isset($_SESSION['admin_id'], $_SESSION['role']) ||
    $_SESSION['role'] !== 'super'
) {
    die("Access denied.");
}

$success = "";

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $permissions = $_POST['permissions'] ?? [];

    // Fetch all non-super admins
    $allAdmins = $conn->query("SELECT id FROM admins WHERE role != 'super'");

    while ($admin = $allAdmins->fetch_assoc()) {
        $admin_id = (int)$admin['id'];
        $can_edit = isset($permissions[$admin_id]['edit']) ? 1 : 0;
        $can_delete = isset($permissions[$admin_id]['delete']) ? 1 : 0;
        $is_active = isset($permissions[$admin_id]['active']) ? 1 : 0;

        $stmt = $conn->prepare("
            UPDATE admins 
            SET can_edit_results = ?, can_delete_results = ?, is_active = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("iiii", $can_edit, $can_delete, $is_active, $admin_id);
        $stmt->execute();
        $stmt->close();
    }

    $success = "Permissions and status updated successfully.";
}

// Fetch all admins except super admins
$result = $conn->query("
    SELECT id, username, can_edit_results, can_delete_results, is_active 
    FROM admins 
    WHERE role != 'super'
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-top">
        <nav>
            <a href="#">Contacts</a>
            <a href="#">Locations</a>
            <a href="#">FAQs</a>
            <a href="#">Download</a>
            <a href="#">Feedback</a>
        </nav>
    </div>
    <div class="header-bottom">
        <img src="images/logo.png" alt="Logo" class="logo">
        <h1 class="site-title">Manage Admin Permissions</h1>
        <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
    </div>
</header>

<main>
    <?php if (!empty($success)): ?>
        <p class="success" style="color: green; text-align: center;">
            <?= htmlspecialchars($success) ?>
        </p>
    <?php endif; ?>

    <div class="admins-container">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <table>
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Can Edit</th>
                    <th>Can Delete</th>
                    <th>Active</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td>
                            <input type="checkbox" 
                                name="permissions[<?= $row['id'] ?>][edit]"
                                value="1" <?= $row['can_edit_results'] ? "checked" : "" ?>>
                        </td>
                        <td>
                            <input type="checkbox" 
                                name="permissions[<?= $row['id'] ?>][delete]"
                                value="1" <?= $row['can_delete_results'] ? "checked" : "" ?>>
                        </td>
                        <td>
                            <input type="checkbox" 
                                name="permissions[<?= $row['id'] ?>][active]"
                                value="1" <?= $row['is_active'] ? "checked" : "" ?>>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <div class="button-container">
                <button type="submit">Update Permissions</button>
            </div>
        </form>

        <div class="form-button">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</main>

<footer>
    <div class="footer-top">
        <img src="images/facebook.ico" class="icons">
        <img src="images/instagram.ico" class="icons">
        <img src="images/twitter.ico" class="icons">
        <img src="images/linkedin.ico" class="icons">
        <img src="images/youtube.ico" class="icons">
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 Kenya National Bureau of Statistics | All Rights Reserved</p>
    </div>
</footer>
</body>
</html>
