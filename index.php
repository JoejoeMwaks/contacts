<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Homepage</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

    <!-- ======= Top Menu ======= -->
    <div class="top-menu">
        <nav>
            <ul>
                <li><a href="results.php">Contacts</a></li>
                <li><a href="#">Locations</a></li>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Download</a></li>
                <li><a href="index.php" class="active">Home</a></li>
            </ul>
        </nav>
        <a href="login.php" class="login-btn">Login</a>
    </div>

    <!-- ======= Header Section ======= -->
    <header class="header-bottom">
        <div class="header-flex">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1 class="site-title">KNBS PhoneBook Contacts</h1>
            <img src="images/telephone.jpg" alt="Telephone" class="telephone-img">
        </div>
    </header>

    <!-- ======= Search Section ======= -->
    <?php $search = isset($_GET['query']) ? $_GET['query'] : ''; ?>

   <div class="main-content">
    <main class="search-container">
        <form method="get" action="results.php">
            <input type="text" name="query" placeholder="Search by Floor, Department, Extension, User"
                value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" required>
         <button type="submit">Search</button>
        </form>
        
        <?php if (isset($_SESSION['admin_id'])): ?>
        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn-admin">Go to Admin Dashboard</a>
        </div>
        <?php endif; ?>
    </main>
            </form>
    </main>
    </div>

    <!-- ======= Footer ======= -->
    <footer>
        <div class="footer-top">
            <img src="images/facebook.ico" class="icons">
            <img src="images/instagram.ico" class="icons">
            <img src="images/twitter.ico" class="icons">
            <img src="images/linkedin.ico" class="icons">
            <img src="images/youtube.ico" class="icons">
        </div>
        <div class="footer-bottom">
            <p id="copyright">© 2025 Kenya National Bureau of Statistics. All rights reserved.</p>
        </div>
    </footer>

</body>


</html>

