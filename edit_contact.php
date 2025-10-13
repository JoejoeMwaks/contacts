<?php
session_start();
include 'db_connection.php';

// Restrict access to logged-in admins only
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Restrict access to super admins OR admins with result-editing rights
// Restrict access to logged-in admins only
if (!isset($_SESSION['admin_id']) || empty($_SESSION['is_active'])) {
    header("Location: login.php");
    exit;
}

// Check if admin has edit permissions OR is super admin
if ($_SESSION['role'] !== 'super' && empty($_SESSION['can_edit_contacts'])) {
    die("Access denied. You do not have permission to edit contacts.");
}


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIXED: Use contact_name instead of User
    $floor = trim($_POST['floor'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $extension = trim($_POST['Caller_ID'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? ''); // CHANGED to contact_name

    if ($id > 0 && $floor && $department && $extension && $contact_name) {
        // FIXED: Update database column to use contact_name instead of User
        $stmt = $conn->prepare("UPDATE contact SET floor=?, department=?, Caller_ID=?, contact_name=? WHERE id=?");
        $stmt->bind_param("ssssi", $floor, $department, $extension, $contact_name, $id);
        if ($stmt->execute()) {
            header("Location: dashboard.php?msg=updated");
            exit;
        } else {
            $error = "Failed to update record.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch existing record
$contact = null;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM contact WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $contact = $result->fetch_assoc();
    if (!$contact) {
        die("Record not found.");
    }
} else {
    die("Invalid contact ID.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Contact</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    
    <div class="top-menu">
        <nav>
            <ul>
                <li><a href="index.php">Contacts</a></li>
                <li><a href="#">Locations</a></li>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Download</a></li>
                <li><a href="#">Feedback</a></li>
            </ul>
        </nav>
    </div>

    <header class="header-bottom">
        <div class="header-flex">
            <a href="dashboard.php"><img src="images/logo.png" alt="Logo" class="logo"></a>
            <h1 class="site-title">Edit Contact</h1>
            <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
        </div>
    </header>

    <div class="main-content">
        <div class="edit-container">
            <?php if (!empty($error)): ?>
                <p class="error" style="color:red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="labels">Floor:</label>
                    <input type="text" name="floor" value="<?php echo htmlspecialchars($contact['floor']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="labels">Department:</label>
                    <input type="text" name="department" value="<?php echo htmlspecialchars($contact['department']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="labels">Extension (Caller ID):</label>
                    <input type="text" name="Caller_ID" value="<?php echo htmlspecialchars($contact['Caller_ID']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="labels">User:</label>
                    <!-- CHANGED: name="User" to name="contact_name" and value from contact['User'] to contact['contact_name'] -->
                    <input type="text" name="contact_name" value="<?php echo htmlspecialchars($contact['contact_name']); ?>" required>
                </div>
                <button type="submit">Update Contact</button>
            </form>

            <div class="back-link">
                <a href="dashboard.php">← Back to Dashboard</a>
            </div>
        </div>
    </div>

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