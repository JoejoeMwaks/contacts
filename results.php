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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - KNBS PhoneBook</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* Specific overrides for results page */
        .results-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .results-header h2 {
            color: #663300;
        }
        .btn-secondary {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .settings-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 10px 0;
            border-left: 3px solid #17a2b8;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <!-- ======= Top Menu ======= -->
    <div class="top-menu">
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav id="nav-menu">
            <ul>
                <li><a href="results.php" class="active">Contacts</a></li>
                <li><a href="#">Locations</a></li>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Download</a></li>
                <li><a href="index.php">Home</a></li>
            </ul>
        </nav>
        <div class="menu-right">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <span style="color: white; margin-right: 15px;">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ======= Header Section ======= -->
    <header class="header-bottom">
        <div class="header-flex">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1 class="site-title">Search Results</h1>
            <img src="images/telephone.jpg" alt="Telephone" class="telephone-img">
        </div>
    </header>

    <!-- ======= Search Results ======= -->
    <main class="main-content">
        <div class="container">
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

    <script>
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('nav-menu');

        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    </script>
</body>
</html>

<?php
// Close database connection
if (isset($stmt)) $stmt->close();
$conn->close();
?>