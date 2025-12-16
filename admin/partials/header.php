<?php
// Initialize session and database connection
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Protect admin pages
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Admin header configuration
$pageTitle = $pageTitle ?? 'Admin - AISU Inquiry System';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AISU Admin Panel - System Administration">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Performance & Security -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/images/logo.jpg" type="image/jpg">

    <!-- Security headers -->
    <meta name="referrer" content="strict-origin-when-cross-origin">
</head>
<body>
    <header class="site-header admin-header" id="siteHeader" role="banner">
        <div class="header-container">
            <!-- Brand Logo -->
            <a href="dashboard.php" class="brand-logo" aria-label="AISU Admin Panel">
                <div class="brand-icon admin-icon">
                    <?php if (file_exists(__DIR__ . '/../assets/images/logo.jpg')): ?>
                        <img src="../assets/images/logo.jpg" alt="AISU Logo" loading="lazy">
                    <?php else: ?>
                        <div class="logo-fallback">🎓</div>
                    <?php endif; ?>
                    <div class="brand-icon-overlay"></div>
                </div>
                <div class="brand-text">
                    <span class="brand-name">AISU</span>
                    <span class="brand-tagline">Admin Panel</span>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="main-nav admin-nav" role="navigation" aria-label="Admin navigation">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="../index.php" class="nav-link" aria-label="View public site">
                            <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                            <span>View Site</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'dashboard.php' ? 'page' : 'false'; ?>">
                            <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_users.php" class="nav-link <?php echo $currentPage === 'manage_users.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'manage_users.php' ? 'page' : 'false'; ?>">
                            <i class="fas fa-users" aria-hidden="true"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_admins.php" class="nav-link <?php echo $currentPage === 'manage_admins.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'manage_admins.php' ? 'page' : 'false'; ?>">
                            <i class="fas fa-user-cog" aria-hidden="true"></i>
                            <span>Admins</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_grades.php" class="nav-link <?php echo $currentPage === 'manage_grades.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'manage_grades.php' ? 'page' : 'false'; ?>">
                            <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                            <span>Grades</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Header Actions -->
            <div class="header-actions">

                <!-- Admin Profile Dropdown -->
                <div class="user-profile admin-profile" role="navigation" aria-label="Admin menu">
                    <button class="profile-trigger admin-trigger" id="profileTrigger" aria-expanded="false" aria-haspopup="true" aria-label="Admin profile menu">
                        <div class="user-avatar avatar-frame admin-avatar">
                            <?php if (!empty($_SESSION['profile_image'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Admin profile picture" loading="lazy" class="avatar-image">
                                <div class="avatar-ring"></div>
                            <?php else: ?>
                                <div class="avatar-placeholder avatar-fallback admin-placeholder">
                                    <i class="fas fa-user-shield avatar-icon"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                            <span class="user-role admin-role">Administrator</span>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                    </button>

                    <div class="profile-dropdown admin-dropdown" id="profileDropdown" role="menu" aria-hidden="true">
                        <div class="dropdown-header admin-header">
                            <div class="dropdown-user">
                                <div class="dropdown-avatar admin-avatar">
                                    <?php if (!empty($_SESSION['profile_image'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Admin profile picture" loading="lazy">
                                    <?php else: ?>
                                        <div class="dropdown-avatar-placeholder admin-placeholder" aria-hidden="true">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown-user-info">
                                    <h4><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></h4>
                                    <p><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                                    <span class="role-indicator admin-indicator">Administrator</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-menu">
                            <a href="../edit_profile.php" class="dropdown-item" role="menuitem">
                                <i class="fas fa-user-edit" aria-hidden="true"></i>
                                <span>Edit Profile</span>
                            </a>
                            <div class="dropdown-divider" aria-hidden="true"></div>
                            <a href="../logout.php" class="dropdown-item logout" role="menuitem">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle admin-toggle" id="mobileToggle" aria-label="Toggle admin navigation menu" aria-expanded="false">
                    <i class="fas fa-bars mobile-menu-icon" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Menu -->
    <div class="mobile-nav-overlay admin-overlay" id="mobileNavOverlay" aria-hidden="true"></div>
    <div class="mobile-navigation admin-navigation" id="mobileNav" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mobile-nav-header admin-nav-header">
            <div class="mobile-brand">
                <img src="../assets/images/logo.jpg" alt="AISU Admin" class="mobile-logo" loading="lazy">
                <div class="mobile-brand-text">
                    <span class="mobile-brand-name">AISU</span>
                    <span class="mobile-brand-tagline">Admin Panel</span>
                </div>
            </div>
            <button class="mobile-nav-close" id="closeNav" aria-label="Close admin navigation menu">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="mobile-nav-content">
            <div class="mobile-user-profile admin-user-profile">
                <div class="mobile-user-avatar admin-avatar">
                    <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Admin profile picture" loading="lazy">
                    <?php else: ?>
                        <div class="mobile-avatar-placeholder admin-placeholder" aria-hidden="true">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mobile-user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></h3>
                    <p><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                    <span class="admin-badge">Administrator</span>
                </div>
            </div>

            <nav aria-label="Admin mobile navigation">
                <ul class="mobile-nav-menu admin-menu">
                    <li class="mobile-nav-item">
                        <a href="dashboard.php" class="mobile-nav-link <?php echo $currentPage === 'dashboard.php' ? 'primary' : ''; ?>">
                            <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard
                        </a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="manage_users.php" class="mobile-nav-link <?php echo $currentPage === 'manage_users.php' ? 'primary' : ''; ?>">
                            <i class="fas fa-users" aria-hidden="true"></i> Manage Users
                        </a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="manage_admins.php" class="mobile-nav-link <?php echo $currentPage === 'manage_admins.php' ? 'primary' : ''; ?>">
                            <i class="fas fa-user-cog" aria-hidden="true"></i> Manage Admins
                        </a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="../index.php" class="mobile-nav-link">
                            <i class="fas fa-external-link-alt" aria-hidden="true"></i> View Public Site
                        </a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="../edit_profile.php" class="mobile-nav-link">
                            <i class="fas fa-user-edit" aria-hidden="true"></i> Edit Profile
                        </a>
                    </li>
                    <li class="mobile-nav-item">
                        <hr class="mobile-nav-separator" aria-hidden="true">
                        <a href="../logout.php" class="mobile-nav-link logout">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <main id="main-content" class="container content-wrapper admin-content" role="main">

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // Header Scroll Effect
            const header = document.getElementById('siteHeader');

            if(header) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 20) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                });
            }

            // User Dropdown
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if(userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                    userMenuBtn.setAttribute('aria-expanded', userDropdown.classList.contains('show'));
                });

                document.addEventListener('click', (e) => {
                    if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.remove('show');
                        userMenuBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Mobile Navigation
            const mobileToggle = document.getElementById('mobileToggle');
            const mobileNav = document.getElementById('mobileNav');
            const mobileNavOverlay = document.getElementById('mobileNavOverlay');
            const closeNav = document.getElementById('closeNav');

            function toggleMenu() {
                if(mobileNav) {
                    mobileNav.classList.toggle('active');
                    mobileNavOverlay.classList.toggle('active');
                    document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
                }
            }

            if(mobileToggle) mobileToggle.addEventListener('click', toggleMenu);
            if(closeNav) closeNav.addEventListener('click', toggleMenu);
            if(mobileNavOverlay) mobileNavOverlay.addEventListener('click', toggleMenu);
        });
    </script>
