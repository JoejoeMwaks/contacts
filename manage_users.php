<?php
session_start();
include 'db_connection.php';

// Only Super Admin can access
// Only allow super admins or admins with manage permissions
if (!isset($_SESSION['admin_id']) || 
    ($_SESSION['role'] !== 'super' && empty($_SESSION['can_manage_admins']))) {
    die("Access denied. You do not have permission to manage users.");
}
$success = "";
$error = "";

// Handle individual password change
if (isset($_POST['change_password'])) {
    $user_id = (int)$_POST['user_id'];
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if ($new_password === $confirm_password) {
        if (strlen($new_password) >= 6) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password changed successfully for user ID: $user_id";
            } else {
                $error = "Failed to change password: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Password must be at least 6 characters long.";
        }
    } else {
        $error = "Passwords do not match.";
    }
}

// Handle email change
if (isset($_POST['change_email'])) {
    $user_id = (int)$_POST['user_id'];
    $new_email = trim($_POST['new_email']);
    
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $new_email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Email address is already in use by another user.";
        } else {
            $stmt = $conn->prepare("UPDATE admins SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $new_email, $user_id);
            
            if ($stmt->execute()) {
                $success = "Email updated successfully for user ID: $user_id";
            } else {
                $error = "Failed to update email: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = "Please enter a valid email address.";
    }
}

// Handle user activation/deactivation
if (isset($_POST['toggle_status'])) {
    $user_id = (int)$_POST['user_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $user_id);
    
    if ($stmt->execute()) {
        $action = $new_status ? "activated" : "deactivated";
        $success = "User $action successfully.";
    } else {
        $error = "Failed to update user status: " . $stmt->error;
    }
    $stmt->close();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = $_POST['username'];
    
    // Prevent super admin from deleting themselves
    if ($user_id == $_SESSION['admin_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = "User '$username' has been removed from the system.";
        } else {
            $error = "Failed to delete user: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Check if required columns exist
$required_columns = ['email', 'role', 'is_active', 'last_login', 'login_count', 'created_at'];
$missing_columns = [];

foreach ($required_columns as $column) {
    $check = $conn->query("SHOW COLUMNS FROM admins LIKE '$column'");
    if (!$check || $check->num_rows === 0) {
        $missing_columns[] = $column;
    }
    if ($check) $check->free();
}

// If columns are missing, show setup instructions
if (!empty($missing_columns)) {
    $setup_required = true;
} else {
    $setup_required = false;
    
    // Fetch all users with basic columns only
    $result = $conn->query("
        SELECT id, username, email, role, is_active, last_login, login_count, created_at 
        FROM admins 
        ORDER BY is_active DESC, last_login DESC
    ");

    // Check if query was successful
    if ($result === false) {
        die("Database error: " . $conn->error);
    }

    // Get user statistics
    $total_users = $result->num_rows;

    $active_result = $conn->query("SELECT COUNT(*) as count FROM admins WHERE is_active = 1");
    $active_users = $active_result ? $active_result->fetch_assoc()['count'] : 0;
    if ($active_result) $active_result->free();

    $super_result = $conn->query("SELECT COUNT(*) as count FROM admins WHERE role = 'super'");
    $super_admins = $super_result ? $super_result->fetch_assoc()['count'] : 0;
    if ($super_result) $super_result->free();

    $today_result = $conn->query("SELECT COUNT(*) as count FROM admins WHERE DATE(last_login) = CURDATE()");
    $today_logins = $today_result ? $today_result->fetch_assoc()['count'] : 0;
    if ($today_result) $today_result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Super Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .user-management {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .user-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .user-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 5px;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .role-super {
            background: #663300;
            color: white;
        }
        
        .role-admin {
            background: #6c757d;
            color: white;
        }
        
        .password-modal, .email-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
        }
        
        .modal-title {
            font-size: 1.5em;
            font-weight: bold;
            color: #663300;
            margin: 0;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #6c757d;
        }
        
        .close-modal:hover {
            color: #343a40;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #663300;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4d2600;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #663300;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #663300;
        }
        
        .last-login {
            font-size: 0.9em;
            color: #6c757d;
        }
        
        .user-details {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        
        .detail-label {
            font-weight: 600;
            width: 120px;
            color: #495057;
        }
        
        .detail-value {
            flex: 1;
            color: #6c757d;
        }
        
        .editable-email {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .email-text {
            flex: 1;
        }
        
        .edit-email-btn {
            background: none;
            border: none;
            color: #17a2b8;
            cursor: pointer;
            font-size: 14px;
        }
        
        .edit-email-btn:hover {
            color: #138496;
            text-decoration: underline;
        }
        
        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .setup-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
        }
        
        .sql-code {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    
    <nav class="top-menu">
        <div class="menu-left">
            <ul>
                <li><a href="index.php">Contacts</a></li>
                <li><a href="#">Locations</a></li>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Download</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </div>
        <div class="menu-right">
            <span style="color: white; margin-right: 15px;">Super Admin Panel</span>
            <a href="dashboard.php" class="login-btn">Dashboard</a>
            <a href="logout.php" class="login-btn">Logout</a>
        </div>
    </nav>

    <header class="header-bottom">
        <div class="header-flex">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1 class="site-title">User Management</h1>
            <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
        </div>
    </header>

    <div class="main-content">
        <div class="user-management">
            <?php if (!empty($success)): ?>
                <div class="success-message" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($setup_required): ?>
                <div class="setup-box">
                    <h3><i class="fas fa-tools"></i> Database Setup Required</h3>
                    <p>The following required columns are missing from your <strong>admins</strong> table:</p>
                    <ul>
                        <?php foreach ($missing_columns as $column): ?>
                            <li><code><?= $column ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <p>Please run the following SQL commands in your database (phpMyAdmin or MySQL):</p>
                    
                    <div class="sql-code">
-- Add missing columns to admins table<br>
ALTER TABLE admins <br>
ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '',<br>
ADD COLUMN role ENUM('super', 'admin') DEFAULT 'admin',<br>
ADD COLUMN is_active TINYINT(1) DEFAULT 1,<br>
ADD COLUMN last_login TIMESTAMP NULL,<br>
ADD COLUMN login_count INT DEFAULT 0,<br>
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;<br>
<br>
-- Update existing admin to have email<br>
UPDATE admins SET email = 'admin@knbs.gov.ke' WHERE email = '';<br>
<br>
-- Make sure at least one super admin exists<br>
UPDATE admins SET role = 'super' WHERE username = 'admin' LIMIT 1;
                    </div>
                    
                    <p>After running these commands, <a href="manage_users.php">refresh this page</a>.</p>
                </div>
            <?php else: ?>
                <!-- User Statistics -->
                <div class="user-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?= $total_users ?></div>
                        <div>Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $active_users ?></div>
                        <div>Active Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $super_admins ?></div>
                        <div>Super Admins</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $today_logins ?></div>
                        <div>Today's Logins</div>
                    </div>
                </div>

                <!-- Users List -->
                <h2>All System Users</h2>
                
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                    <div class="user-card">
                        <div class="user-header">
                            <div class="user-info">
                                <h3 style="margin: 0 0 10px 0;">
                                    <?= htmlspecialchars($user['username']) ?>
                                    <span class="user-status role-<?= $user['role'] ?>">
                                        <?= strtoupper($user['role']) ?> ADMIN
                                    </span>
                                    <span class="user-status <?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $user['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                                    </span>
                                </h3>
                            </div>
                            
                            <div class="user-actions">
                                <!-- Change Password Button -->
                                <button type="button" onclick="openPasswordModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                                
                                <!-- Change Email Button -->
                                <button type="button" onclick="openEmailModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['email']) ?>')" class="btn btn-info">
                                    <i class="fas fa-envelope"></i> Change Email
                                </button>
                                
                                <!-- Activate/Deactivate User -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $user['is_active'] ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-<?= $user['is_active'] ? 'warning' : 'success' ?>">
                                        <i class="fas fa-<?= $user['is_active'] ? 'pause' : 'play' ?>"></i>
                                        <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                
                                <!-- Delete User (cannot delete yourself) -->
                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete <?= htmlspecialchars($user['username']) ?>? This action cannot be undone.')">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- User Details -->
                        <div class="user-details">
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value">
                                    <div class="editable-email">
                                        <span class="email-text"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
                                        <button type="button" class="edit-email-btn" onclick="openEmailModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['email']) ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </div>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">User ID:</span>
                                <span class="detail-value"><?= $user['id'] ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Registered:</span>
                                <span class="detail-value"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Login Count:</span>
                                <span class="detail-value"><?= $user['login_count'] ?? 0 ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Last Login:</span>
                                <span class="detail-value"><?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-users" style="font-size: 3em; margin-bottom: 20px;"></i>
                        <h3>No users found</h3>
                        <p>There are no users in the system.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="password-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Change Password</h3>
                <button type="button" class="close-modal" onclick="closePasswordModal()">&times;</button>
            </div>
            <form method="POST" id="passwordForm">
                <input type="hidden" name="user_id" id="modalUserId">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" required minlength="6" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="6" placeholder="Confirm new password">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Cancel</button>
                    <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Change Modal -->
    <div id="emailModal" class="email-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Change Email Address</h3>
                <button type="button" class="close-modal" onclick="closeEmailModal()">&times;</button>
            </div>
            <form method="POST" id="emailForm">
                <input type="hidden" name="user_id" id="emailModalUserId">
                <div class="form-group">
                    <label for="new_email">New Email Address:</label>
                    <input type="email" name="new_email" id="new_email" required placeholder="Enter new email address">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEmailModal()">Cancel</button>
                    <button type="submit" name="change_email" class="btn btn-primary">Update Email</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Password Modal Functions
    function openPasswordModal(userId, username) {
        document.getElementById('modalUserId').value = userId;
        document.querySelector('#passwordModal .modal-title').textContent = 'Change Password for ' + username;
        document.getElementById('passwordModal').style.display = 'flex';
        document.getElementById('new_password').focus();
    }
    
    function closePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
        document.getElementById('passwordForm').reset();
    }
    
    // Email Modal Functions
    function openEmailModal(userId, username, currentEmail) {
        document.getElementById('emailModalUserId').value = userId;
        document.querySelector('#emailModal .modal-title').textContent = 'Change Email for ' + username;
        document.getElementById('new_email').value = currentEmail;
        document.getElementById('emailModal').style.display = 'flex';
        document.getElementById('new_email').focus();
        document.getElementById('new_email').select();
    }
    
    function closeEmailModal() {
        document.getElementById('emailModal').style.display = 'none';
        document.getElementById('emailForm').reset();
    }
    
    // Close modals when clicking outside
    document.getElementById('passwordModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePasswordModal();
        }
    });
    
    document.getElementById('emailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEmailModal();
        }
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePasswordModal();
            closeEmailModal();
        }
    });
    
    // Password confirmation validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            document.getElementById('confirm_password').focus();
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            document.getElementById('new_password').focus();
        }
    });
    
    // Email validation
    document.getElementById('emailForm').addEventListener('submit', function(e) {
        const email = document.getElementById('new_email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address!');
            document.getElementById('new_email').focus();
        }
    });
    </script>

</body>
</html>

<?php
// Close database connection
if (isset($result) && $result) $result->free();
$conn->close();
?>