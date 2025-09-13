<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'AISU - Inquiry'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo-container">
                <img src="assets/images/logo.jpg" alt="AISU Logo">
                <h1>AISU - Inquiry</h1>
            </a>
            <div class="user-profile-menu">
                <div class="avatar-container" id="avatarMenuBtn">
                    <?php if(isset($_SESSION['user_id']) && !empty($_SESSION['profile_image'])): ?>
                        <img src="/AISU/uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="User Avatar" class="user-avatar">
                        <?php
                        // Temporary debug output
                        echo "<!-- Debug: Profile Image Session: " . htmlspecialchars($_SESSION['profile_image']) . " -->";
                        echo "<!-- Debug: Full Image Path: /AISU/uploads/" . htmlspecialchars($_SESSION['profile_image']) . " -->";
                        ?>
                    <?php else: ?>
                        <i class="fas fa-user-circle user-avatar-icon"></i>
                        <?php
                        // Temporary debug output
                        echo "<!-- Debug: Profile Image Session is empty or not set. -->";
                        ?>
                    <?php endif; ?>
                </div>
                <div class="dropdown-menu glass-ui" id="userDropdownMenu">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="user-info">
                            <p><strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong></p>
                            <p class="small-text">Level: <?php echo htmlspecialchars($_SESSION['level'] ?? 'N/A'); ?></p>
                        </div>
                        <a href="edit_profile.php" class="dropdown-item"><i class="fas fa-user-edit"></i> Edit Profile</a>
                        <a href="dashboard.php" class="dropdown-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="signup.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <main class="container">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarMenuBtn = document.getElementById('avatarMenuBtn');
            const userDropdownMenu = document.getElementById('userDropdownMenu');

            if (avatarMenuBtn && userDropdownMenu) {
                avatarMenuBtn.addEventListener('click', function() {
                    userDropdownMenu.classList.toggle('show');
                });

                // Close the dropdown if the user clicks outside of it
                window.addEventListener('click', function(event) {
                    if (!avatarMenuBtn.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                        userDropdownMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
</final_file_content>
