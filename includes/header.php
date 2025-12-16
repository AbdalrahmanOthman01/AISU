<?php
// Initialize session and database connection
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Common header configuration
$pageTitle = $pageTitle ?? 'AISU - Inquiry System';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AISU Inquiry System - Student Inquiry Platform">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Performance & Security -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/logo.jpg" type="image/jpg">

    <!-- Security headers -->
    <meta name="referrer" content="strict-origin-when-cross-origin">
</head>
<body>
    <header class="site-header" id="siteHeader" role="banner">
        <div class="header-container">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-logo" aria-label="AISU Home">
                <div class="brand-icon">
                    <img src="assets/images/logo.jpg" alt="AISU Logo" loading="lazy">
                    <div class="brand-icon-overlay"></div>
                </div>
                <div class="brand-text">
                    <span class="brand-name">AISU</span>
                    <span class="brand-tagline">Inquiry System</span>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="main-nav" role="navigation" aria-label="Main navigation">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'index.php' ? 'page' : 'false'; ?>">
                            <i class="fas fa-home" aria-hidden="true"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'dashboard.php' ? 'page' : 'false'; ?>">
                                <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] === 'student'): ?>
                        <li class="nav-item">
                            <a href="grades.php" class="nav-link <?php echo $currentPage === 'grades.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'grades.php' ? 'page' : 'false'; ?>">
                                <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                                <span>My Grades</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                            <li class="nav-item">
                                <a href="admin/dashboard.php" class="nav-link" aria-label="Admin Dashboard">
                                    <i class="fas fa-cog" aria-hidden="true"></i>
                                    <span>Admin</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>

<!-- Header Actions -->
<div class="header-actions">

                <?php if ($isLoggedIn): ?>
                    <!-- User Profile Dropdown -->
                    <div class="user-profile" role="navigation" aria-label="User menu">
                        <button class="profile-trigger" id="profileTrigger" aria-expanded="false" aria-haspopup="true" aria-label="User profile menu">
                            <div class="user-avatar avatar-frame">
                                <?php if (!empty($_SESSION['profile_image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile picture of <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>" loading="lazy" class="avatar-image">
                                    <div class="avatar-ring"></div>
                                <?php else: ?>
                                    <div class="avatar-placeholder avatar-fallback">
                                        <i class="fas fa-user-tie avatar-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                                <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                            </div>
                            <i class="fas fa-angle-down dropdown-arrow" aria-hidden="true"></i>
                        </button>

                            <div class="profile-dropdown" id="profileDropdown" role="menu" aria-hidden="true">
                            <div class="dropdown-header">
                                <div class="dropdown-user">
                                    <div class="dropdown-avatar">
                                        <?php if (!empty($_SESSION['profile_image'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile picture" loading="lazy">
                                        <?php else: ?>
                                            <div class="dropdown-avatar-placeholder" aria-hidden="true">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dropdown-user-info">
                                        <h4><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></h4>
                                        <p><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                                        <span class="role-indicator"><?php echo ucfirst($_SESSION['role']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-menu">
                                <button type="button" class="dropdown-item" id="editProfileBtn" role="menuitem">
                                    <i class="fas fa-user-edit" aria-hidden="true"></i>
                                    <span>Edit Personal Info</span>
                                </button>
                                <div class="dropdown-divider" aria-hidden="true"></div>
                                <a href="logout.php" class="dropdown-item logout" role="menuitem">
                                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                    <span>Sign Out</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Auth Buttons -->
                    <div class="auth-section">
                        <a href="login.php" class="auth-link login-link">
                            <span>Sign In</span>
                        </a>
                        <a href="signup.php" class="auth-link signup-link">
                            <span>Sign Up</span>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" id="mobileToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                    <i class="fas fa-bars mobile-menu-icon" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Menu (Hidden by default) -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay" aria-hidden="true" style="display: none;"></div>
    <div class="mobile-navigation" id="mobileNav" role="dialog" aria-modal="true" aria-hidden="true" style="display: none;">
        <div class="mobile-nav-header">
            <button class="mobile-nav-close" id="closeNav" aria-label="Close navigation menu">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="mobile-nav-content">
            <?php if ($isLoggedIn): ?>
                <div class="mobile-user-profile">
                    <div class="mobile-user-avatar">
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile picture" loading="lazy">
                        <?php else: ?>
                            <div class="mobile-avatar-placeholder" aria-hidden="true">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mobile-user-info">
                        <h3><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></h3>
                        <p><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <nav aria-label="Mobile navigation">
                <ul class="mobile-nav-menu">
                    <li class="mobile-nav-item">
                        <a href="index.php" class="mobile-nav-link <?php echo $currentPage === 'index.php' ? 'primary' : ''; ?>">
                            <i class="fas fa-home" aria-hidden="true"></i> Home
                        </a>
                    </li>

                    <?php if ($isLoggedIn): ?>
                        <li class="mobile-nav-item">
                            <a href="dashboard.php" class="mobile-nav-link <?php echo $currentPage === 'dashboard.php' ? 'primary' : ''; ?>">
                                <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard
                            </a>
                        </li>
                        <?php if ($isAdmin): ?>
                            <li class="mobile-nav-item">
                                <a href="admin/dashboard.php" class="mobile-nav-link">
                                    <i class="fas fa-cog" aria-hidden="true"></i> Admin Panel
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="mobile-nav-item">
                            <a href="edit_profile.php" class="mobile-nav-link <?php echo $currentPage === 'edit_profile.php' ? 'primary' : ''; ?>">
                                <i class="fas fa-user-edit" aria-hidden="true"></i> Edit Profile
                            </a>
                        </li>
                        <li class="mobile-nav-item">
                            <hr class="mobile-nav-separator" aria-hidden="true">
                            <a href="logout.php" class="mobile-nav-link logout">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="mobile-nav-item">
                            <a href="login.php" class="mobile-nav-link">
                                <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Login
                            </a>
                        </li>
                        <li class="mobile-nav-item">
                            <a href="signup.php" class="mobile-nav-link primary">
                                <i class="fas fa-user-plus" aria-hidden="true"></i> Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <main id="main-content" class="container content-wrapper" role="main">

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Theme Toggle Functionality
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');

            // Get saved theme from localStorage or default to light
            const savedTheme = localStorage.getItem('theme') || 'light';

            // Apply saved theme
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                if (themeIcon) themeIcon.className = 'fas fa-sun theme-icon';
            } else {
                document.documentElement.removeAttribute('data-theme');
                if (themeIcon) themeIcon.className = 'fas fa-moon theme-icon';
            }

            // Theme toggle click handler
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-theme');

                    if (currentTheme === 'dark') {
                        // Switch to light theme
                        document.documentElement.removeAttribute('data-theme');
                        localStorage.setItem('theme', 'light');
                        if (themeIcon) themeIcon.className = 'fas fa-moon theme-icon';
                    } else {
                        // Switch to dark theme
                        document.documentElement.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        if (themeIcon) themeIcon.className = 'fas fa-sun theme-icon';
                    }

                    // Add transition effect
                    document.body.style.transition = 'background 0.3s ease, color 0.3s ease';
                    setTimeout(() => {
                        document.body.style.transition = '';
                    }, 300);
                });
            }

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

            // Profile Dropdown Toggle
            const profileTrigger = document.getElementById('profileTrigger');
            const profileDropdown = document.getElementById('profileDropdown');

            if(profileTrigger && profileDropdown) {
                profileTrigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                    profileTrigger.setAttribute('aria-expanded', profileDropdown.classList.contains('show'));
                });

                document.addEventListener('click', (e) => {
                    if (!profileTrigger.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                        profileTrigger.setAttribute('aria-expanded', 'false');
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

            // Profile Edit Modal Functionality
            const editProfileBtn = document.getElementById('editProfileBtn');
            const profileModal = document.getElementById('profileModal');
            const closeProfileModal = document.getElementById('closeProfileModal');
            const cancelProfileEdit = document.getElementById('cancelProfileEdit');
            const modalProfileImage = document.getElementById('modalProfileImage');
            const modalProfileImagePreview = document.getElementById('modalProfileImagePreview');

            // Open modal
            if(editProfileBtn && profileModal) {
                editProfileBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    profileModal.classList.add('show');
                    profileModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                });
            }

            // Close modal functions
            function closeModal() {
                if(profileModal) {
                    profileModal.classList.remove('show');
                    profileModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }
            }

            if(closeProfileModal) closeProfileModal.addEventListener('click', closeModal);
            if(cancelProfileEdit) cancelProfileEdit.addEventListener('click', closeModal);

            // Close on overlay click
            if(profileModal) {
                profileModal.addEventListener('click', (e) => {
                    if(e.target === profileModal) {
                        closeModal();
                    }
                });
            }

            // Profile image preview
            if(modalProfileImage && modalProfileImagePreview) {
                modalProfileImage.addEventListener('change', function(event) {
                    const [file] = event.target.files;
                    if (file) {
                        modalProfileImagePreview.src = URL.createObjectURL(file);
                    }
                });
            }

            // Handle form submission via AJAX to avoid page reload
            const profileForm = document.getElementById('profileForm');
            if(profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    fetch('edit_profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        // Check if update was successful
                        if(data.includes('Profile updated successfully')) {
                            // Close modal and show success message
                            closeModal();
                            // You could add a success toast/notification here
                            alert('Profile updated successfully!');
                            // Reload page to reflect changes
                            window.location.reload();
                        } else {
                            // Show error message
                            alert('Error updating profile. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating profile. Please try again.');
                    });
                });
            }
        });
    </script>
