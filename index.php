<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KNBS PhoneBook - Homepage</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
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
    <div class="main-content">
        <main class="search-container">
            <h1 style="color: #b06443; margin-bottom: 20px;">Find a Contact</h1>
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
            <p id="copyright">© 2025 Kenya National Bureau of Statistics. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('nav-menu');

        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    </script>
</body>


</html>

