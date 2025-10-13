<?php
session_start();
include 'db_connection.php';

// Ensure only logged-in admins can access
// Restrict access to logged-in admins only
if (!isset($_SESSION['admin_id']) || empty($_SESSION['is_active'])) {
    header("Location: login.php");
    exit;
}

// Check if admin has add permissions OR is super admin
// (Assuming you want to restrict who can add contacts)
if ($_SESSION['role'] !== 'super' && empty($_SESSION['can_edit_contacts'])) {
    die("Access denied. You do not have permission to add contacts.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim input
    $floor = trim($_POST['floor']);
    $department = trim($_POST['department']);
    $caller_id = trim($_POST['Caller_ID']);
    $contact_name = trim($_POST['contact_name']);

    if ($floor && $department && $caller_id && $contact_name) {
        // Prepare SQL insert
        $stmt = $conn->prepare("INSERT INTO contact (floor, department, Caller_ID, contact_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $floor, $department, $caller_id, $contact_name);

        if ($stmt->execute()) {
            $success = "✅ Contact added successfully.";
        } else {
            $error = "❌ Failed to add contact. Please try again.";
        }
    } else {
        $error = "⚠️ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Contact</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    
        <nav class="top-menu">
            <div class="menu-left">
    
                <ul>
                <li><a href="#">Contacts</a></il>
                <li><a href="#">Locations</a></il>
                <li><a href="#">FAQs</a></il>
                <li><a href="#">Download</a></il>
                <li><a href="#">Feedback</a></il>
                </ul>
       </div>
        <div class="menu-right">
            <a href="logout.php" class="login-btn">Logout</a>
            <img src="images/telephone.jpg" alt="Phone Icon">
        </div>
    </nav>
        <header class="header-bottom">
            <div class="header-flex"></div>
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1 class="site-title">Add New Contact</h1>
            <img src="images/telephone.jpg" alt="Header Visual" class="header-image">
        </div>
    </header>

    <div class="main-content">
    <div class="contact-container">
        <?php if ($error): ?>
            <p style="color:red; text-align:center;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color:green; text-align:center;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" action="add_contact.php">
            <div class="form-group">
                <label class="labels">Floor:</label>
                <input type="text" name="floor" required>
            </div>
            <div class="form-group">
                <label class="labels">Department:</label>
                <input type="text" name="department" required>
            </div>
            <div class="form-group">
                <label class="labels">Extension (Caller ID):</label>
                <input type="text" name="Caller_ID" required>
            </div>
            <div class="form-group">
                <label class="labels">Contact Name:</label>
                <input type="text" name="contact_name" required>
            </div>
            <button type="submit">Add Contact</button>
        </form>

        <p style="text-align:center;"><a href="dashboard.php">← Back to Dashboard</a></p>
    </div>
            </div>
    <footer>
        <div class="footer-top">
            <img src="images/facebook.ico" class="icons">
            <img src="images/instagram.ico" class="icons">
            <img src="images/twitter.ico" class="icons">
            <img src="images/linkedin.ico" class="icons">
            <img src="images/youtube.ico" class="icons">
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Kenya National Bureau of Statistics | All Rights Reserved</p>
        </div>
    </footer>
</body>

</html>
