<?php
session_start();
include 'db_connection.php';

// Require login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

$error = $success = "";

// Strong password check
function is_strong_password($password) {
    return (
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[\W]/', $password)
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check new passwords match
    if ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match.";
    }
    // Check password strength
    elseif (!is_strong_password($newPassword)) {
        $error = "Password must be at least 8 characters, include uppercase, lowercase, number, and symbol.";
    } else {
        $adminId = $_SESSION['admin_id'];

        // Get current hash
        $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $stmt->bind_result($dbPassword);
        if ($stmt->fetch()) {
            $stmt->close();

            // Verify old password
            if (!password_verify($oldPassword, $dbPassword)) {
                $error = "Old password is incorrect.";
            } else {
                // Hash new password
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update in DB
                $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $update->bind_param("si", $newHash, $adminId);
                if ($update->execute()) {
                    $success = "Password changed successfully. Please log in again.";
                    session_destroy(); // force re-login
                    header("Refresh: 2; url=login.php");
                } else {
                    $error = "Error updating password. Try again.";
                }
                $update->close();
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-bottom">
        <h1 class="site-title">Change Password</h1>
    </div>
</header>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<main>
    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" id="old_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit">Change Password</button>
        </form>
        <p><a href="dashboard.php">← Back to Dashboard</a></p>
    </div>
</main>

<footer>
    <p>&copy; 2025 Kenya National Bureau of Statistics | All Rights Reserved</p>
</footer>
</body>
</html>