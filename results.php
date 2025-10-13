<?php
include 'db_connection.php';
session_start();

// ==================== Get Contacts Per Page Setting ====================
$contacts_per_page = 10; // default value
$settings_result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_name = 'contacts_per_page'");
if ($settings_result && $settings_result->num_rows > 0) {
    $setting = $settings_result->fetch_assoc();
    $contacts_per_page = (int)$setting['setting_value'];
}
if ($settings_result) $settings_result->free();

// ==================== Pagination Settings ====================
$query = strtolower(trim($_GET['query'] ?? ''));
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $contacts_per_page;

$results = false;
$results_count = 0;
$total_pages = 0;

if ($query !== '') {
    // Count total results
    $count_stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM contact 
        WHERE floor LIKE CONCAT('%', ?, '%') 
           OR department LIKE CONCAT('%', ?, '%') 
           OR contact_name LIKE CONCAT('%', ?, '%') 
           OR Caller_ID LIKE CONCAT('%', ?, '%')
    ");
    
    if ($count_stmt) {
        $count_stmt->bind_param("ssss", $query, $query, $query, $query);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_results = $count_result->fetch_assoc()['total'];
        $results_count = $total_results;
        $total_pages = ceil($total_results / $contacts_per_page);
        $count_stmt->close();
    }

    // Get paginated results
    $stmt = $conn->prepare("
        SELECT floor, department, Caller_ID, contact_name
        FROM contact 
        WHERE floor LIKE CONCAT('%', ?, '%') 
           OR department LIKE CONCAT('%', ?, '%') 
           OR contact_name LIKE CONCAT('%', ?, '%') 
           OR Caller_ID LIKE CONCAT('%', ?, '%')
        LIMIT ? OFFSET ?
    ");

    if ($stmt) {
        $stmt->bind_param("ssssii", $query, $query, $query, $query, $contacts_per_page, $offset);
        $stmt->execute();
        $results = $stmt->get_result();
    } else {
        die("Database query preparation failed.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Results - KNBS PhoneBook</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .results-container {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .results-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(240, 240, 240, 0.8);
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 12px;
        }
        
        .results-header h2 {
            color: #663300;
            margin-bottom: 10px;
        }
        
        .table-container {
            overflow-x: auto;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .table-container table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table-container th {
            background: rgba(102, 51, 0, 0.9);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .table-container td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(224, 224, 224, 0.7);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .table-container tr:hover {
            background: rgba(248, 249, 250, 0.9);
        }
        
        .no-results {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            margin: 20px 0;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(224, 224, 224, 0.7);
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 12px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 15px;
            border: 1px solid rgba(221, 221, 221, 0.8);
            border-radius: 4px;
            text-decoration: none;
            color: #663300;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .pagination a:hover {
            background: rgba(102, 51, 0, 0.9);
            color: white;
            border-color: rgba(102, 51, 0, 0.9);
        }
        
        .pagination a.active {
            background: rgba(102, 51, 0, 0.9);
            color: white;
            border-color: rgba(102, 51, 0, 0.9);
        }
        
        .btn-primary {
            display: inline-block;
            padding: 12px 25px;
            background: rgba(102, 51, 0, 0.9);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        
        .btn-primary:hover {
            background: rgba(77, 38, 0, 0.9);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(108, 117, 125, 0.9);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(84, 91, 98, 0.9);
            transform: translateY(-2px);
        }
        
        .btn-admin {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(255, 193, 7, 0.9);
            color: #212529;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            background: rgba(224, 168, 0, 0.9);
            transform: translateY(-2px);
        }
        
        .settings-info {
            background: rgba(209, 236, 241, 0.8);
            color: #0c5460;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 10px 0;
            border-left: 3px solid rgba(23, 162, 184, 0.8);
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <!-- ======= Top Menu ======= -->
    <nav class="top-menu">
        <div class="menu-left">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Contacts</a></li>
                <li><a href="#">Locations</a></li>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Download</a></li>
            </ul>
        </div>
        <div class="menu-right">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <span style="color: white; margin-right: 15px;">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <a href="dashboard.php" class="login-btn">Dashboard</a>
                <a href="logout.php" class="login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Admin Login</a>
            <?php endif; ?>
            <img src="images/telephone.jpg" alt="Phone Icon">
        </div>
    </nav>

    <!-- ======= Header Section ======= -->
    <header class="header-bottom">
        <div class="header-flex">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1 class="site-title">Search Results</h1>
            <img src="images/telephone.jpg" alt="Telephone" class="header-image">
        </div>
    </header>

    <!-- ======= Search Results ======= -->
    <div class="main-content">
        <div class="results-container">
            <?php if ($query === ''): ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3em; color: #6c757d; margin-bottom: 20px;"></i>
                    <h2>No Search Query Entered</h2>
                    <p>Please enter a search term to find contacts.</p>
                    <a href="index.php" class="btn-primary">Back to Search</a>
                </div>

            <?php elseif ($results && $results_count > 0): ?>
                <div class="results-header">
                    <h2>Search Results</h2>
                    <p>
                        Showing <?php echo min($offset + 1, $results_count); ?>–<?php echo min($offset + $contacts_per_page, $results_count); ?> 
                        of <?php echo $results_count; ?> result(s) for: 
                        <span style="color: #663300;">"<?php echo htmlspecialchars($query); ?>"</span>
                    </p>
                    <div class="settings-info">
                        <i class="fas fa-cog"></i> Displaying <?php echo $contacts_per_page; ?> contacts per page 
                    </div>
                    <div style="margin-top: 15px;">
                        <a href="index.php" class="btn-secondary">New Search</a>
                        
                        <?php if (isset($_SESSION['admin_id']) && $_SESSION['role'] === 'super'): ?>
                            <a href="system_settings.php" class="btn-admin" style="margin-left: 10px;">
                                <i class="fas fa-cog"></i> Change Settings
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Department</th>
                                <th>Floor</th>
                                <th>Extension</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['contact_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['floor']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Caller_ID']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    if ($page > 1) {
                        echo "<a href='?query=" . urlencode($query) . "&page=" . ($page - 1) . "' class='pagination-prev'><i class='fas fa-chevron-left'></i> Previous</a>";
                    }
                    
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = ($i == $page) ? "class='active'" : "";
                        echo "<a href='?query=" . urlencode($query) . "&page=$i' $active>$i</a>";
                    }
                    
                    if ($page < $total_pages) {
                        echo "<a href='?query=" . urlencode($query) . "&page=" . ($page + 1) . "' class='pagination-next'>Next <i class='fas fa-chevron-right'></i></a>";
                    }
                    ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-frown" style="font-size: 3em; color: #6c757d; margin-bottom: 20px;"></i>
                    <h2>No Results Found</h2>
                    <p>No contact information found for <strong>"<?php echo htmlspecialchars($query); ?>"</strong>.</p>
                    <p>Please try different search terms or check your spelling.</p>
                    <a href="index.php" class="btn-primary">Try Again</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ======= Footer ======= -->
    <footer>
        <div class="footer-top">
            <img src="images/facebook.ico" class="icons" alt="Facebook">
            <img src="images/instagram.ico" class="icons" alt="Instagram">
            <img src="images/twitter.ico" class="icons" alt="Twitter">
            <img src="images/linkedin.ico" class="icons" alt="LinkedIn">
            <img src="images/youtube.ico" class="icons" alt="YouTube">
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Kenya National Bureau of Statistics | All Rights Reserved</p>
        </div>
    </footer>

</body>
</html>

<?php
// Close database connection
if (isset($stmt)) $stmt->close();
$conn->close();
?>