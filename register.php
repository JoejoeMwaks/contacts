<?php
session_start();
include 'db_connection.php';

// Check how many admins exist
$check = $conn->query("SELECT COUNT(*) AS total FROM admins");
$count = 0;
if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    $count = intval($row['total']);
}

// If admins exist, only super admin can register new ones
if ($count > 0) {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'super') {
        die("Access denied. Only Super Admin can register new admins.");
    }
}

$error = $success = "";

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

        // Determine role
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
<html>
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-menu">
        <nav>
            <a href="">Contacts</a>
            <a href="">Locations</a>
            <a href="">FAQs</a>
            <a href="">Download</a>
            <a href="">Feedback</a>
        </nav>
    </div>

    <header class="header-bottom">
        <div class="header-flex">
        <img src="images/logo.png" alt="Logo" class="logo">
        <h1 class="site-title">Register Admin</h1>
        <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
    </div>
</header>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<main>
    <div class="login-container">
        <form method="post">
            <div class="form-group">
                <label class="labels" for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label class="labels" for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                <small>Password must be at least 8 characters, include uppercase, lowercase, number, and symbol.</small>
            </div>

            <?php if ($count > 0): ?>
                <div class="form-group">
                    <label class="labels" for="role">Role:</label>
                    <select name="role" id="role">
                        <option value="admin">Admin</option>
                        <option value="super">Super Admin</option>
                    </select>
                </div>
            <?php else: ?>
                <p>First admin will be registered as <strong>Super Admin</strong>.</p>
            <?php endif; ?>

            <button type="submit">Register</button>
        </form>

        <div class="form-bottom">
            <p>Back to: <a href="login.php">Login</a></p>
            <?php if ($count > 0): ?>
                <p><a href="dashboard.php">← Back to Dashboard</a></p>
            <?php endif; ?>
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