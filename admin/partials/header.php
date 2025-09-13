<?php require_once __DIR__ . '/../../config/db.php'; 

// Protect admin pages
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin - AISU Inquiry'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <a href="dashboard.php" class="logo-container">
                <img src="../assets/images/logo.jpg" alt="AISU Logo">
                <h1>Admin Panel</h1>
            </a>
            <div class="user-profile-menu">
                <div class="avatar-container" id="avatarMenuBtn">
                    <?php if(isset($_SESSION['user_id']) && !empty($_SESSION['profile_image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Admin Avatar" class="user-avatar">
                    <?php else: ?>
                        <i class="fas fa-user-shield user-avatar-icon"></i>
                    <?php endif; ?>
                </div>
                <div class="dropdown-menu glass-ui" id="userDropdownMenu">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="user-info">
                            <p><strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong></p>
                            <p class="small-text">Role: <?php echo htmlspecialchars($_SESSION['role'] ?? 'N/A'); ?></p>
                        </div>
                        <a href="dashboard.php" class="dropdown-item"><i class="fas fa-envelope"></i> Messages</a>
                        <a href="manage_users.php" class="dropdown-item"><i class="fas fa-users"></i> Manage Users</a>
                        <a href="manage_admins.php" class="dropdown-item"><i class="fas fa-user-cog"></i> Manage Admins</a>
                        <a href="../logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="../login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Login</a>
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
