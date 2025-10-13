<?php
include("db_connection.php");

// Start session for admin checks
session_start();

// =========// ==================== Get Contacts Per Page Setting ====================
$contacts_per_page = 10; // default value
$settings_result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_name = 'contacts_per_page'");
if ($settings_result && $settings_result->num_rows > 0) {
    $setting = $settings_result->fetch_assoc();
    $contacts_per_page = (int)$setting['setting_value'];
}
if ($settings_result) $settings_result->free();

// ==================== Pagination Settings ====================
$limit = $contacts_per_page; // Use the setting from database
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ==================== Search Filter ====================
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// ==================== Count Query ====================
$countQuery = "SELECT COUNT(*) as total FROM contact 
               WHERE floor LIKE CONCAT('%', ?, '%') 
                  OR department LIKE CONCAT('%', ?, '%') 
                  OR Caller_ID LIKE CONCAT('%', ?, '%') 
                  OR contact_name LIKE CONCAT('%', ?, '%')";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("ssss", $search, $search, $search, $search);
$countStmt->execute();
$countResult = $countStmt->get_result();
$total = $countResult->fetch_assoc()['total'];
$countStmt->close();

// ==================== Main Query ====================
$query = "SELECT id, floor, department, Caller_ID, contact_name 
          FROM contact 
          WHERE floor LIKE CONCAT('%', ?, '%') 
             OR department LIKE CONCAT('%', ?, '%') 
             OR Caller_ID LIKE CONCAT('%', ?, '%') 
             OR contact_name LIKE CONCAT('%', ?, '%') 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssii", $search, $search, $search, $search, $limit, $offset);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KNBS PhoneBook Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        /* Hamburger Menu Styles - ALWAYS VISIBLE */
        .hamburger-menu-container {
            display: block;
            margin: 15px 0;
        }
        
        .hamburger-btn {
            background: #ffc107;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .hamburger-btn:hover {
            background: #34495e;
            transform: translateY(-2px);
        }
        
        .hamburger-btn i {
            font-size: 20px;
        }
        
        .hamburger-content {
            display: none;
            position: absolute;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            margin-top: 10px;
            padding: 15px 0;
            border: 1px solid #e0e0e0;
            z-index: 1000;
            min-width: 250px;
        }
        
        .hamburger-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hamburger-content a {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #f5f5f5;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .hamburger-content a:hover {
            background: #f8f9fa;
            color: #2c3e50;
            padding-left: 25px;
        }
        
        .hamburger-content a:last-child {
            border-bottom: none;
        }
        
        .hamburger-content .btn-add {
            background: #28a745;
            color: white !important;
            margin: 10px;
            text-align: center;
            border-radius: 6px;
            border: none !important;
        }
        
        .hamburger-content .btn-add:hover {
            background: #218838 !important;
            color: white !important;
        }
        
        .hamburger-content .btn-admin {
            background: #b06443;
            color: white !important;
            margin: 10px;
            text-align: center;
            border-radius: 6px;
            border: none !important;
        }
        
        .hamburger-content .btn-admin:hover {
            background: #ffc107 !important;
            color: white !important;
        }
        
        /* Admin Functions - Original Desktop Version - HIDDEN */
        .admin-functions {
            display: none;
        }
        
        /* Overlay for menu */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .menu-overlay.active {
            display: block;
        }
        
        /* Ensure proper spacing */
        .container {
            position: relative;
        }
    </style>
</head>
<body>

    <!-- ==================== TOP MENU ==================== -->
    <nav class="top-menu">
        <div class="menu-left">
            <ul>
                <li><a href="#">Contacts</a></li>
                <li><a href="locations.php">Locations</a></li>
                <li><a href="faqs.php">FAQs</a></li>
                <li><a href="download.php">Download</a></li>
                <li><a href="index.php">Home</a></li>
            </ul>
        </div>
        <div class="menu-right">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <span style="color: white; margin-right: 15px;">
                    Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    <?php if ($_SESSION['role'] === 'super'): ?>
                        <strong>(Super Admin)</strong>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
            <a href="logout.php" class="login-btn">Logout</a>
            <img src="images/telephone.jpg" alt="Phone Icon">
        </div>
    </nav>

    <!-- ==================== HEADER ==================== -->
    <header>
        <div class="header-content">
            <img src="images/logo.png" alt="KNBS Logo" class="logo">
            <h1>KNBS PhoneBook Dashboard</h1>
        </div>
    </header>

    <!-- ==================== MAIN CONTENT ==================== -->
    <div class="container">

        <!-- Show total contacts -->
        <p class="total-contacts">📌 Total Contacts: <strong><?php echo $total; ?></strong></p>

        <!-- Search box -->
        <div class="search-box">
            <form method="get" action="">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
                <input type="submit" value="Search">
            </form>
        </div>

        <!-- Hamburger Menu - ALWAYS VISIBLE -->
        <div class="hamburger-menu-container">
            <button class="hamburger-btn" id="hamburgerBtn">
                <i class="fas fa-bars"></i> Quick Actions
            </button>
            <div class="hamburger-content" id="hamburgerContent">
                <?php
                // Add new contact - Available to all logged-in admins
                if (isset($_SESSION['admin_id'])) {
                    echo '<a href="add_contact.php" class="btn-add">+ Add New Contact</a>';
                    
                    // Change Password - Available to all logged-in admins
                    echo '<a href="change_password.php" class="btn-admin">Change Password</a>';
                    
                    // Check if user is Super Admin OR has register users permission
                    $is_super_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'super');
                    $can_register = ($_SESSION['can_register_users'] ?? 0);
                    
                    if ($is_super_admin || $can_register) {
                        // Register User - Available to super admin OR admins with register permission
                        echo '<a href="register_user.php" class="btn-admin">Register User</a>';
                    }
                    
                    // Check if user is Super Admin - ONLY Super Admin sees these
                    if ($is_super_admin) {
                        // Super Admin only functions
                        echo '<a href="manage_users.php" class="btn-admin">Manage Users</a>';
                        echo '<a href="manage_roles.php" class="btn-admin">Manage Roles</a>';
                        echo '<a href="system_settings.php" class="btn-admin">System Settings</a>';
                    }
                } else {
                    // If not logged in as admin, show login button
                    echo '<a href="login.php" class="btn-admin">Admin Login</a>';
                }
                ?>
            </div>
        </div>

        <!-- Desktop Admin Functions Section - HIDDEN -->
        <div class="admin-functions">
            <!-- Add new contact - Available to all logged-in admins -->
            <?php if (isset($_SESSION['admin_id'])): ?>
                <a href="add_contact.php" class="btn-add">+ Add New Contact</a>
                
                <!-- Change Password - Available to all logged-in admins -->
                <a href="change_password.php" class="btn-admin">Change Password</a>
                
                <?php
                // Check if user is Super Admin OR has register users permission
                $is_super_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'super');
                $can_register = ($_SESSION['can_register_users'] ?? 0);
                
                if ($is_super_admin || $can_register) {
                    // Register User - Available to super admin OR admins with register permission
                    echo '<a href="register_user.php" class="btn-admin">Register User</a>';
                }
                
                // Check if user is Super Admin - ONLY Super Admin sees these
                if ($is_super_admin) {
                    // Super Admin only functions
                    echo '<a href="manage_users.php" class="btn-admin">Manage Users</a>';
                    echo '<a href="manage_roles.php" class="btn-admin">Manage Roles</a>';
                    echo '<a href="system_settings.php" class="btn-admin">System Settings</a>';
                }
                ?>
            <?php else: ?>
                <!-- If not logged in as admin, show login button -->
                <a href="login.php" class="btn-admin">Admin Login</a>
            <?php endif; ?>
        </div>

        <!-- Contacts Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Floor</th>
                <th>Department</th>
                <th>Caller ID</th>
                <th>Contact Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['floor']; ?></td>
                    <td><?php echo $row['department']; ?></td>
                    <td><?php echo $row['Caller_ID']; ?></td>
                    <td><?php echo $row['contact_name']; ?></td>
                    <td>
                        <?php if (isset($_SESSION['admin_id'])): ?>
                            <!-- Edit Contact - Check if user has edit permission OR is super admin -->
                            <?php if ($_SESSION['role'] === 'super' || ($_SESSION['can_edit_contacts'] ?? 0)): ?>
                                <a href="edit_contact.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a> | 
                            <?php endif; ?>
                            
                            <!-- Delete Contact - Check if user has delete permission OR is super admin -->
                            <?php if ($_SESSION['role'] === 'super' || ($_SESSION['can_delete_contacts'] ?? 0)): ?>
                                <a href="delete_contact.php?id=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this contact?');" 
                                   class="btn-delete">Delete</a>
                            <?php endif; ?>
                            
                            <!-- If user has no permissions for edit/delete -->
                            <?php if ($_SESSION['role'] !== 'super' && empty($_SESSION['can_edit_contacts']) && empty($_SESSION['can_delete_contacts'])): ?>
                                <span style="color: #999;">No permissions</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #999;">Login to edit</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            $totalPages = ceil($total / $limit);
            if ($totalPages > 1) {
                // Previous button
                if ($page > 1) {
                    echo "<a href='?page=" . ($page - 1) . "&search=" . urlencode($search) . "' class='pagination-prev'><i class='fas fa-chevron-left'></i> Prev</a>";
                }
                
                // First page
                if ($page > 3) {
                    echo "<a href='?page=1&search=" . urlencode($search) . "'>1</a>";
                    if ($page > 4) {
                        echo "<span class='pagination-ellipsis'>...</span>";
                    }
                }
                
                // Page numbers around current page
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    $active = ($i == $page) ? "class='active'" : "";
                    echo "<a href='?page=$i&search=" . urlencode($search) . "' $active>$i</a>";
                }
                
                // Last page
                if ($page < $totalPages - 2) {
                    if ($page < $totalPages - 3) {
                        echo "<span class='pagination-ellipsis'>...</span>";
                    }
                    echo "<a href='?page=$totalPages&search=" . urlencode($search) . "'>$totalPages</a>";
                }
                
                // Next button
                if ($page < $totalPages) {
                    echo "<a href='?page=" . ($page + 1) . "&search=" . urlencode($search) . "' class='pagination-next'>Next <i class='fas fa-chevron-right'></i></a>";
                }
            }
            ?>
        </div>

        <!-- Showing results -->
        <p class="showing">
            Showing <?php echo $offset + 1; ?>–
            <?php echo min($offset + $limit, $total); ?> of
            <?php echo $total; ?> results
        </p>
    </div>

    <!-- Overlay for menu -->
    <div class="menu-overlay" id="menuOverlay"></div>

    <script>
        // Hamburger menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const hamburgerContent = document.getElementById('hamburgerContent');
            const menuOverlay = document.getElementById('menuOverlay');
            
            if (hamburgerBtn) {
                hamburgerBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    hamburgerContent.classList.toggle('active');
                    menuOverlay.classList.toggle('active');
                });
            }
            
            // Close menu when clicking outside
            document.addEventListener('click', function() {
                hamburgerContent.classList.remove('active');
                menuOverlay.classList.remove('active');
            });
            
            // Prevent menu from closing when clicking inside it
            if (hamburgerContent) {
                hamburgerContent.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Close menu when overlay is clicked
            if (menuOverlay) {
                menuOverlay.addEventListener('click', function() {
                    hamburgerContent.classList.remove('active');
                    menuOverlay.classList.remove('active');
                });
            }
        });
    </script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
<?php include("footer.php"); ?>