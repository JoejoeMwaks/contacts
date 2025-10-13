<?php
// -----------------------------------------
// Secure Session Configuration
// -----------------------------------------
ini_set('session.gc_maxlifetime', 1800);       // 30 min timeout
ini_set('session.use_strict_mode', 1);         // Prevent uninitialized IDs
ini_set('session.use_only_cookies', 1);        // Only cookies, no URL session IDs
ini_set('session.cookie_httponly', 1);         // No JavaScript access
ini_set('session.cookie_secure', 1);           // Only send cookies over HTTPS (must use HTTPS!)

session_start();

// -----------------------------------------
// Security Headers
// -----------------------------------------
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// -----------------------------------------
// Redirect if already logged in
// -----------------------------------------
if (isset($_SESSION['admin_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header("Location: login.php?msg=expired");
        exit;
    } else {
        $_SESSION['last_activity'] = time();
        header("Location: dashboard.php");
        exit;
    }
}

include 'db_connection.php';

$error = '';
$message = '';

// -----------------------------------------
// System messages
// -----------------------------------------
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'logout': $message = "You have been successfully logged out."; break;
        case 'expired': $message = "Your session has expired. Please log in again."; break;
        case 'registered': $message = "Registration successful. You can now log in."; break;
    }
}

// -----------------------------------------
// Handle Login Request
// -----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security validation failed. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $ip = $_SERVER['REMOTE_ADDR'];

        // Basic validation
        if (empty($username) || empty($password)) {
            $error = "Invalid username or password.";
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $error = "Invalid username or password.";
        } else {
            try {
                // -----------------------------------------
                // Rate Limiting (Database-based)
                // -----------------------------------------
                $timeframe = 15 * 60; // 15 minutes
                $max_attempts = 5;

                // Clean up old attempts
                $conn->query("DELETE FROM login_attempts WHERE attempt_time < (NOW() - INTERVAL 15 MINUTE)");

                // Count attempts from this IP
                $stmt = $conn->prepare("SELECT COUNT(*) AS attempts FROM login_attempts WHERE ip_address = ?");
                if (!$stmt) {
                    die("Database error: " . $conn->error);
                }
                $stmt->bind_param("s", $ip);
                $stmt->execute();
                $stmt->bind_result($attempts);
                $stmt->fetch();
                $stmt->close();

                if ($attempts >= $max_attempts) {
                    $error = "Too many failed login attempts. Please try again after 15 minutes.";
                } else {
                    // -----------------------------------------
                    // Fetch user
                    // -----------------------------------------
                    $stmt = $conn->prepare("SELECT id, username, password, role, can_edit_contacts, can_delete_contacts, can_register_users, can_manage_admins, is_active FROM admins WHERE username = ?");
                    if (!$stmt) {
                        die("Database error: " . $conn->error);
                    }
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();

                        if (!$user['is_active']) {
                            $error = "Your account has been deactivated. Contact administrator.";
                        } elseif (password_verify($password, $user['password'])) {
                            // ✅ Successful login - Set all session variables properly
                            $_SESSION['admin_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['is_active'] = $user['is_active'];

                            // Set permission variables (ensure they exist)
                            $_SESSION['can_edit_contacts'] = $user['can_edit_contacts'] ?? 0;
                            $_SESSION['can_delete_contacts'] = $user['can_delete_contacts'] ?? 0;
                            $_SESSION['can_register_users'] = $user['can_register_users'] ?? 0;
                            $_SESSION['can_manage_admins'] = $user['can_manage_admins'] ?? 0;

                            // If user is super admin, grant all permissions automatically
                            if ($_SESSION['role'] === 'super') {
                                $_SESSION['can_edit_contacts'] = 1;
                                $_SESSION['can_delete_contacts'] = 1;
                                $_SESSION['can_register_users'] = 1;
                                $_SESSION['can_manage_admins'] = 1;
                            }

                            $_SESSION['last_activity'] = time();
                            session_regenerate_id(true);

                            // Update last login
                            $update_stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                            if (!$update_stmt) {
                                die("Database error: " . $conn->error);
                            }
                            $update_stmt->bind_param("i", $user['id']);
                            $update_stmt->execute();
                            $update_stmt->close();

                            // Clear failed attempts
                            $del_stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
                            if (!$del_stmt) {
                                die("Database error: " . $conn->error);
                            }
                            $del_stmt->bind_param("s", $ip);
                            $del_stmt->execute();
                            $del_stmt->close();

                            header("Location: dashboard.php");
                            exit;
                        } else {
                            // Wrong password
                            $error = "Invalid username or password.";
                        }
                    } else {
                        // No such user
                        $error = "Invalid username or password.";
                    }

                    // Record failed attempt if login failed
                    if (!empty($error)) {
                        $insert_stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())");
                        if (!$insert_stmt) {
                            die("Database error: " . $conn->error);
                        }
                        $insert_stmt->bind_param("s", $ip);
                        $insert_stmt->execute();
                        $insert_stmt->close();
                    }
                }
            } catch (Exception $e) {
                $error = "A system error occurred. Please try again later.";
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

// -----------------------------------------
// Generate new CSRF token per page load
// -----------------------------------------
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - KNBS PhoneBook</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
</head>
<body>

    <div class="top-menu">
        <nav>
             <ul>
           <li> <a href="index.php">Contacts</a></li>
             <li><a href="">Locations</a></li>
            <li> <a href="">FAQs</a></li>
            <li> <a href="">Download</a></li>
             <li><a href="">Feedback</a></li>
</ul>
        </nav>
    </div>
    <header class="header-bottom">
        <div class="header-flex">
        <img src="images/logo.png" alt="Logo" class="logo">
        <h1 class="site-title"> Login </h1>
        <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
    </div>
</header>

<main>
    <div class="main-content">
    <div class="login-container">
        <div class="form_box">
            <h2 class="form-title">Login</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                           name="username"
                           id="username"
                           required
                           autocomplete="username"
                           placeholder="Enter your username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           autocomplete="current-password"
                           placeholder="Enter your password">
                </div>

                <button type="submit">Login</button>
            </form>

            <div class="links">
                <p>No admin account? <a href="register.php">Register here</a></p>
                <p><a href="index.php">← Back to Home</a></p>
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