<?php
session_start();
include 'db_connection.php';

// Only Super Admin can access
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'super') {
    die("Access denied. Super Admin privileges required.");
}

$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name']);
    $contacts_per_page = (int)$_POST['contacts_per_page'];
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $allow_registration = isset($_POST['allow_registration']) ? 1 : 0;
    
    // Update settings in database
    $stmt = $conn->prepare("
        INSERT INTO system_settings (setting_name, setting_value) 
        VALUES 
        ('site_name', ?),
        ('contacts_per_page', ?),
        ('maintenance_mode', ?),
        ('allow_registration', ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->bind_param("siii", $site_name, $contacts_per_page, $maintenance_mode, $allow_registration);
    
    if ($stmt->execute()) {
        $success = "System settings updated successfully!";
    } else {
        $success = "Error updating settings: " . $stmt->error;
    }
    $stmt->close();
}

// Get current settings
$settings = [];
$result = $conn->query("SELECT setting_name, setting_value FROM system_settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings - Super Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.92);
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .settings-group {
            background: rgba(255, 255, 255, 0.8);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 5px solid #663300;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .settings-group h3 {
            color: #663300;
            margin-bottom: 20px;
            font-size: 1.3em;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .settings-group h3 i {
            color: #ffcc00;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #663300;
            box-shadow: 0 0 0 3px rgba(102, 51, 0, 0.1);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(248, 249, 250, 0.7);
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .checkbox-group label {
            font-weight: 600;
            color: #495057;
            margin: 0;
            cursor: pointer;
        }
        
        .checkbox-group input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
        }
        
        .checkbox-group small {
            display: block;
            color: #6c757d;
            font-size: 0.85em;
            margin-top: 5px;
            font-weight: normal;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn-backup, .btn-optimize {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-backup {
            background: #28a745;
            color: white;
        }
        
        .btn-backup:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-optimize {
            background: #17a2b8;
            color: white;
        }
        
        .btn-optimize:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn-save {
            padding: 15px 40px;
            background: #663300;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            background: #4d2600;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 51, 0, 0.2);
        }
        
        .btn-cancel {
            padding: 15px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn-cancel:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        .success-message {
            background: rgba(212, 237, 218, 0.9);
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid rgba(195, 230, 203, 0.8);
            text-align: center;
            font-weight: 600;
        }
        
        .info-box {
            background: rgba(209, 236, 241, 0.8);
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid rgba(190, 229, 235, 0.7);
            font-size: 0.9em;
        }
        
        .system-status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .status-item {
            background: rgba(255, 255, 255, 0.8);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .status-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #663300;
            margin-bottom: 5px;
        }
        
        .status-label {
            font-size: 0.9em;
            color: #6c757d;
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
            <h1 class="site-title">System Settings</h1>
            <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
        </div>
    </header>

    <div class="main-content">
        <div class="settings-container">
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- System Status Overview -->
            <div class="settings-group">
                <h3><i class="fas fa-chart-bar"></i> System Overview</h3>
                <div class="system-status">
                    <?php
                    // Get system statistics
                    $total_contacts = $conn->query("SELECT COUNT(*) as count FROM contact")->fetch_assoc()['count'];
                    $total_users = $conn->query("SELECT COUNT(*) as count FROM admins")->fetch_assoc()['count'];
                    $active_users = $conn->query("SELECT COUNT(*) as count FROM admins WHERE is_active = 1")->fetch_assoc()['count'];
                    ?>
                    <div class="status-item">
                        <div class="status-value"><?= $total_contacts ?></div>
                        <div class="status-label">Total Contacts</div>
                    </div>
                    <div class="status-item">
                        <div class="status-value"><?= $total_users ?></div>
                        <div class="status-label">Total Users</div>
                    </div>
                    <div class="status-item">
                        <div class="status-value"><?= $active_users ?></div>
                        <div class="status-label">Active Users</div>
                    </div>
                    <div class="status-item">
                        <div class="status-value"><?= phpversion() ?></div>
                        <div class="status-label">PHP Version</div>
                    </div>
                </div>
            </div>

            <form method="POST">
                <div class="settings-group">
                    <h3><i class="fas fa-cog"></i> General Settings</h3>
                    
                    <div class="form-group">
                        <label for="site_name">Site Name:</label>
                        <input type="text" id="site_name" name="site_name" 
                               value="<?= htmlspecialchars($settings['site_name'] ?? 'KNBS PhoneBook') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contacts_per_page">Contacts Per Page:</label>
                        <input type="number" id="contacts_per_page" name="contacts_per_page" 
                               value="<?= $settings['contacts_per_page'] ?? 10 ?>" 
                               min="5" max="100" required>
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i> Number of contacts displayed per page in the contacts list.
                        </div>
                    </div>
                </div>

                <div class="settings-group">
                    <h3><i class="fas fa-wrench"></i> System Status</h3>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                               <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                        <label for="maintenance_mode">
                            Maintenance Mode
                            <small>When enabled, only administrators can access the system. Regular users will see a maintenance message.</small>
                        </label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="allow_registration" name="allow_registration" 
                               <?= ($settings['allow_registration'] ?? 1) ? 'checked' : '' ?>>
                        <label for="allow_registration">
                            Allow User Registration
                            <small>Allow new users to register accounts. When disabled, only administrators can create new accounts.</small>
                        </label>
                    </div>
                </div>

                <div class="settings-group">
                    <h3><i class="fas fa-database"></i> Database Management</h3>
                    
                    <div class="form-group">
                        <label for="backup_frequency">Automatic Backups:</label>
                        <select id="backup_frequency" name="backup_frequency">
                            <option value="daily" <?= ($settings['backup_frequency'] ?? 'weekly') === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= ($settings['backup_frequency'] ?? 'weekly') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= ($settings['backup_frequency'] ?? 'weekly') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="never" <?= ($settings['backup_frequency'] ?? 'weekly') === 'never' ? 'selected' : '' ?>>Never</option>
                        </select>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" onclick="backupDatabase()" class="btn-backup">
                            <i class="fas fa-download"></i> Create Manual Backup
                        </button>
                        <button type="button" onclick="optimizeDatabase()" class="btn-optimize">
                            <i class="fas fa-broom"></i> Optimize Database
                        </button>
                    </div>
                    
                    <div class="info-box">
                        <i class="fas fa-lightbulb"></i> 
                        <strong>Backup:</strong> Creates a complete backup of your database. 
                        <strong>Optimize:</strong> Cleans up and optimizes database tables for better performance.
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    <a href="dashboard.php" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function backupDatabase() {
        if (confirm('This will create a backup of the entire database. Continue?')) {
            // Show loading state
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
            btn.disabled = true;
            
            // Simulate backup process (replace with actual backup script)
            setTimeout(() => {
                window.location.href = 'backup_database.php';
            }, 1000);
        }
    }
    
    function optimizeDatabase() {
        if (confirm('This will optimize database tables to improve performance. Continue?')) {
            // Show loading state
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Optimizing...';
            btn.disabled = true;
            
            // Simulate optimization process (replace with actual optimization script)
            setTimeout(() => {
                window.location.href = 'optimize_database.php';
            }, 1000);
        }
    }
    
    // Add some interactive features
    document.addEventListener('DOMContentLoaded', function() {
        // Add real-time character count for site name
        const siteNameInput = document.getElementById('site_name');
        if (siteNameInput) {
            siteNameInput.addEventListener('input', function() {
                const charCount = this.value.length;
                // You could display this count somewhere if needed
            });
        }
    });
    </script>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>