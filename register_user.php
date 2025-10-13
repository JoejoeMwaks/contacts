<?php
session_start();
include 'db_connection.php';

// Check if user is logged in AND has register permission OR is super admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user has register permission OR is super admin
$has_register_permission = ($_SESSION['can_register_users'] ?? 0) || ($_SESSION['role'] === 'super');

if (!$has_register_permission) {
    die("Access denied. You do not have permission to register new users.");
}

$error = $success = "";

// Check how many admins exist - FIXED: This was missing
$check = $conn->query("SELECT COUNT(*) AS total FROM admins");
$count = 0;
if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    $count = intval($row['total']);
}

// Password validation function
function is_strong_password($password) {
    return (
        strlen($password) >= 8 &&                 // at least 8 chars
        preg_match('/[A-Z]/', $password) &&       // at least one uppercase
        preg_match('/[a-z]/', $password) &&       // at least one lowercase
        preg_match('/[0-9]/', $password) &&       // at least one digit
        preg_match('/[\W]/', $password)           // at least one special char
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $passwordRaw = $_POST['password'];

    // Username validation
    if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
        $error = "Username must be 3–50 characters and only contain letters, numbers, dots, underscores, or hyphens.";
    }
    // Password validation
    elseif (!is_strong_password($passwordRaw)) {
        $error = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
    } else {
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        // Determine role - FIXED: Now $count is defined
        if ($count === 0) {
            $role = 'super'; // first admin is Super Admin
        } else {
            $role = isset($_POST['role']) && in_array($_POST['role'], ['admin', 'super'])
                ? $_POST['role'] : 'admin';
        }

        // Double-check max admins
        $check2 = $conn->query("SELECT COUNT(*) AS total FROM admins");
        $totalAdmins = 0;
        if ($check2 && $check2->num_rows > 0) {
            $row2 = $check2->fetch_assoc();
            $totalAdmins = intval($row2['total']);
        }

        if ($totalAdmins >= 4) {
            $error = "Registration closed. Maximum 4 admins allowed.";
        } else {
            // Insert with prepared statement
            $stmt = $conn->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $username, $password, $role);
                if ($stmt->execute()) {
                    $success = "Admin registered successfully. They can now log in.";
                } else {
                    $error = "Registration failed. Username may already exist.";
                }
                $stmt->close();
            } else {
                $error = "Database error. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin - KNBS PhoneBook</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>

    <div class="top-menu">
        <nav>
            <ul>
                <li><a href="index.php">Contacts</a></li>
                <li><a href="">Locations</a></li>
                <li><a href="">FAQs</a></li>
                <li><a href="">Download</a></li>
                <li><a href="">Feedback</a></li>
            </ul>
        </nav>
    </div>

    <header class="header-bottom">
        <div class="header-flex">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1 class="site-title">Register Admin</h1>
            <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
        </div>
    </header>

    <main>
        <div class="main-content">
            <div class="login-container">
                <div class="form_box">
                    <h2 class="form-title">Register Admin</h2>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="post" autocomplete="on">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text"
                                   name="username"
                                   id="username"
                                   required
                                   autocomplete="username"
                                   placeholder="Enter username"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="Enter password">
                            <small style="display: block; margin-top: 5px; font-size: 12px; color: #666;">
                                Must be at least 8 characters with uppercase, lowercase, number, and symbol.
                            </small>
                        </div>

                        <?php if ($count > 0): ?>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select name="role" id="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="super">Super Admin</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <p style="background: #f0f8ff; padding: 10px; border-radius: 5px; border-left: 4px solid #007bff;">
                                    <strong>First Admin:</strong> Will be registered as <strong>Super Admin</strong>
                                </p>
                            </div>
                        <?php endif; ?>

                        <button type="submit">Register</button>
                    </form>

                    <div class="links">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                        <?php if ($count > 0): ?>
                            <p><a href="dashboard.php">← Back to Dashboard</a></p>
                        <?php else: ?>
                            <p><a href="index.php">← Back to Home</a></p>
                        <?php endif; ?>
                    </div>
                </div>
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
            <p id="copyright">Copyright 2025 © Kenya National Bureau of Statistics | All Rights Reserved</p>
        </div>
    </footer>
</body>
</html>