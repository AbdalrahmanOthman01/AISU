<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection before any database operations
require_once __DIR__ . '/config/db.php';

// If user is already logged in, redirect BEFORE any output
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$pageTitle = 'Login - AISU Inquiry';

// Handle POST request first, before including header
$error = '';
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier']; // Can be username or phone
    $password = $_POST['password'];

    // Admin login with 'AISU' username is a special case
    if ($identifier === 'AISU' || strpos($identifier, 'AISU_') === 0) {
        $sql = "SELECT * FROM users WHERE username = ? AND role = 'admin'";
    } else {
        $sql = "SELECT * FROM users WHERE phone_number = ? AND role = 'student'";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, start session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['level'] = $user['level']; // Set the user's level in the session
        $_SESSION['profile_image'] = $user['profile_image']; // Set the user's profile image in the session

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid credentials. Please try again.';
    }
}

require_once 'includes/header.php';
?>

<div class="form-container glass-ui">
    <h2>Login</h2>
    <?php if ($error): ?><p class="alert alert-danger"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success_message): ?><p class="alert alert-success"><?= htmlspecialchars($success_message) ?></p><?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <i class="fas fa-user"></i>
            <label for="identifier">Username or Phone Number</label>
            <input type="text" id="identifier" name="identifier" placeholder="Enter your username or phone number" required>
        </div>
        <div class="form-group">
            <i class="fas fa-lock"></i>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <p class="form-link">Don't have an account? <a href="signup.php">Sign up here</a></p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
