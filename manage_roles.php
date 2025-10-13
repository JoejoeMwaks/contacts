<?php
session_start();
include 'db_connection.php';

// Only Super Admin can access
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'super') {
    die("Access denied. Super Admin privileges required.");
}

$success = "";
$error = "";

// Handle individual permission updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_single_permissions'])) {
        $user_id = (int)$_POST['user_id'];
        $can_edit = isset($_POST['can_edit']) ? 1 : 0;
        $can_delete = isset($_POST['can_delete']) ? 1 : 0;
        $can_register = isset($_POST['can_register']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE admins SET can_edit_contacts = ?, can_delete_contacts = ?, can_register_users = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("iiii", $can_edit, $can_delete, $can_register, $user_id);
            if ($stmt->execute()) {
                $success = "Permissions updated successfully for user ID: $user_id";
                
                // If the updated user is the current user, refresh their permissions
                if ($user_id == $_SESSION['admin_id']) {
                    header("Location: refresh_permissions.php");
                    exit;
                }
            } else {
                $error = "Failed to update permissions: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // Handle role change
    if (isset($_POST['change_role'])) {
        $user_id = (int)$_POST['user_id'];
        $new_role = $_POST['new_role'];
        
        // Prevent changing own role from super admin
        if ($user_id == $_SESSION['admin_id'] && $new_role !== 'super') {
            $error = "You cannot change your own role from Super Admin.";
        } else {
            $stmt = $conn->prepare("UPDATE admins SET role = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $new_role, $user_id);
                if ($stmt->execute()) {
                    $success = "User role updated successfully.";
                    
                    // If changing to super admin, grant all permissions automatically
                    if ($new_role === 'super') {
                        $perm_stmt = $conn->prepare("UPDATE admins SET can_edit_contacts = 1, can_delete_contacts = 1, can_register_users = 1 WHERE id = ?");
                        $perm_stmt->bind_param("i", $user_id);
                        $perm_stmt->execute();
                        $perm_stmt->close();
                        $success .= " All permissions granted (Super Admin role).";
                    }
                    
                    // If the updated user is the current user, refresh their permissions
                    if ($user_id == $_SESSION['admin_id']) {
                        header("Location: refresh_permissions.php");
                        exit;
                    }
                } else {
                    $error = "Failed to update role: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Fetch all users
$result = $conn->query("
    SELECT id, username, email, role, is_active, 
           can_edit_contacts, can_delete_contacts, can_register_users,
           last_login, login_count, created_at 
    FROM admins 
    ORDER BY role DESC, username ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Roles & Permissions - Super Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .roles-management {
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
        }
        
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 5px;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .role-super { background: #663300; color: white; }
        .role-admin { background: #6c757d; color: white; }
        
        .permissions-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
        }
        
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .permission-item:hover {
            border-color: #663300;
            background: #fff;
        }
        
        .permission-item input[type="checkbox"] {
            transform: scale(1.3);
            cursor: pointer;
        }
        
        .permission-item label {
            font-weight: 600;
            color: #495057;
            cursor: pointer;
            flex: 1;
        }
        
        .permission-desc {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 3px;
        }
        
        .role-selector {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .role-selector select {
            padding: 8px 15px;
            border: 2px solid #ced4da;
            border-radius: 6px;
            background: white;
            font-weight: 600;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #663300;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4d2600;
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
        
        .permission-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }
        
        .super-admin-note {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    
    <nav class="top-menu">
        <div class="menu-left">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Contacts</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_roles.php" class="active">Manage Roles</a></li>
                <li><a href="system_settings.php">System Settings</a></li>
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
            <h1 class="site-title">Manage Roles & Permissions</h1>
            <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
        </div>
    </header>

    <div class="main-content">
        <div class="roles-management">
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Refresh Permissions Button -->
            <div style="text-align: center; margin: 20px 0;">
                <a href="refresh_permissions.php" class="btn btn-warning">
                    <i class="fas fa-sync-alt"></i> Refresh My Permissions
                </a>
                <small style="display: block; margin-top: 5px; color: #666;">
                    Use this if your permissions don't reflect recent changes
                </small>
            </div>

            <h2>User Permissions Management</h2>
            <p style="color: #666; margin-bottom: 25px;">Select specific permissions for each user by checking/unchecking the boxes below.</p>

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
                            
                            <!-- Role Change Form -->
                            <div class="role-selector">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_role" onchange="this.form.submit()">
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Regular Admin</option>
                                        <option value="super" <?= $user['role'] === 'super' ? 'selected' : '' ?>>Super Admin</option>
                                    </select>
                                    <input type="hidden" name="change_role">
                                </form>
                                <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                    <span style="color: #dc3545; font-size: 0.9em;">(Your account)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Permissions Section -->
                    <div class="permissions-section">
                        <h4 style="margin-bottom: 15px; color: #495057;">
                            <i class="fas fa-key"></i> User Permissions
                        </h4>
                        
                        <?php if ($user['role'] === 'super'): ?>
                            <div class="super-admin-note">
                                <i class="fas fa-crown"></i> 
                                <strong>Super Admin Note:</strong> This user has all permissions automatically. 
                                To modify permissions, change their role to "Regular Admin" first.
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                
                                <div class="permissions-grid">
                                    <!-- Edit Contacts Permission -->
                                    <div class="permission-item">
                                        <input type="checkbox" 
                                               name="can_edit" 
                                               id="edit_<?= $user['id'] ?>" 
                                               value="1" 
                                               <?= $user['can_edit_contacts'] ? 'checked' : '' ?>>
                                        <label for="edit_<?= $user['id'] ?>">
                                            Edit Contacts
                                            <div class="permission-desc">Can modify contact information</div>
                                        </label>
                                    </div>
                                    
                                    <!-- Delete Contacts Permission -->
                                    <div class="permission-item">
                                        <input type="checkbox" 
                                               name="can_delete" 
                                               id="delete_<?= $user['id'] ?>" 
                                               value="1" 
                                               <?= $user['can_delete_contacts'] ? 'checked' : '' ?>>
                                        <label for="delete_<?= $user['id'] ?>">
                                            Delete Contacts
                                            <div class="permission-desc">Can remove contacts from system</div>
                                        </label>
                                    </div>
                                    
                                    <!-- Register Users Permission -->
                                    <div class="permission-item">
                                        <input type="checkbox" 
                                               name="can_register" 
                                               id="register_<?= $user['id'] ?>" 
                                               value="1" 
                                               <?= $user['can_register_users'] ? 'checked' : '' ?>>
                                        <label for="register_<?= $user['id'] ?>">
                                            Register Users
                                            <div class="permission-desc">Can create new admin accounts</div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="permission-actions">
                                    <button type="submit" name="update_single_permissions" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Permissions for <?= htmlspecialchars($user['username']) ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
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
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
                <a href="manage_users.php" class="btn btn-warning">Manage Users</a>
            </div>
        </div>
    </div>

    <script>
    // Auto-submit role change forms
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelects = document.querySelectorAll('select[name="new_role"]');
        roleSelects.forEach(select => {
            select.addEventListener('change', function() {
                if (confirm('Change user role? This will affect their permissions.')) {
                    this.form.submit();
                } else {
                    this.blur(); // Remove focus
                }
            });
        });
        
        // Add visual feedback for permission checkboxes
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.closest('.permission-item');
                if (this.checked) {
                    label.style.borderColor = '#28a745';
                    label.style.background = '#f8fff9';
                } else {
                    label.style.borderColor = '#e9ecef';
                    label.style.background = '#f8f9fa';
                }
            });
        });
    });
    </script>

</body>
</html>

<?php
// Close database connection
if (isset($result)) $result->free();
$conn->close();
?>