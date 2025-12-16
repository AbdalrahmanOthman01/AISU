<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect BEFORE any output
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = 'Sign Up - AISU Inquiry';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $level = (int)$_POST['level'];
    $phone = trim($_POST['phone_number']);
    $password = $_POST['password'];

    // Validation
    if (empty($first_name) || empty($last_name) || empty($level) || empty($phone) || empty($password) || empty($_FILES['profile_image']['name'])) {
        $error = 'All fields are required.';
    } elseif (!in_array($level, [1, 2, 3, 4])) {
        $error = 'Invalid level selected.';
    } else {
        // Check if phone number is unique or blocked
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone_number = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = 'This phone number is already registered.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM blocked_numbers WHERE phone_number = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $error = 'This phone number is blocked and cannot be used.';
            } else {
                // Handle file upload
                $target_dir = "uploads/";
                $image_name = time() . '_' . basename($_FILES["profile_image"]["name"]);
                $target_file = $target_dir . $image_name;
                
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $username = $first_name . $last_name; // Simple username generation
                    
                    // Insert into database
                    $sql = "INSERT INTO users (username, first_name, last_name, phone_number, password, level, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([$username, $first_name, $last_name, $phone, $hashed_password, $level, $image_name])) {
                        $_SESSION['success_message'] = "Account created successfully! Please log in.";
                        header('Location: login.php');
                        exit();
                    } else {
                        $error = 'Something went wrong. Please try again.';
                    }
                } else {
                    $error = 'Sorry, there was an error uploading your file.';
                }
            }
        }
    }
}
?>

<div class="form-container glass-ui">
    <h2>Create Account</h2>
    <?php if ($error): ?><p class="alert alert-danger"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form action="signup.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <i class="fas fa-user"></i>
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
        </div>
        <div class="form-group">
            <i class="fas fa-user"></i>
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
        </div>
        <div class="form-group">
            <i class="fas fa-layer-group"></i>
            <label for="level">Level</label>
            <select id="level" name="level" required>
                <option value="" disabled selected>Select your level</option>
                <option value="1">Level 1</option>
                <option value="2">Level 2</option>
                <option value="3">Level 3</option>
                <option value="4">Level 4</option>
            </select>
        </div>
        <div class="form-group">
            <i class="fas fa-phone"></i>
            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" placeholder="Enter your phone number" required>
        </div>
        <div class="form-group">
            <i class="fas fa-image"></i>
            <label for="profile_image">Profile Image</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*" required>
        </div>
        <div class="form-group">
            <i class="fas fa-lock"></i>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Choose a password" required>
        </div>
        <button type="submit" class="btn">Sign Up</button>
        <p class="form-link">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
