<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in BEFORE any output
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Edit Profile - AISU Inquiry';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success_message = '';

// Fetch user data
$stmt = $pdo->prepare("SELECT username, first_name, last_name, phone_number, level, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found, redirect to login
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    $level = isset($_POST['level']) ? (int)$_POST['level'] : null;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($phone_number)) {
        $error = 'First name, last name, and phone number are required.';
    } elseif ($_SESSION['role'] === 'student' && ($level === null || !in_array($level, [1, 2, 3, 4]))) {
        $error = 'Invalid level selected.';
    } else {
        // Check if phone number already exists for another user
        $stmt_check_phone = $pdo->prepare("SELECT id FROM users WHERE phone_number = ? AND id != ?");
        $stmt_check_phone->execute([$phone_number, $user_id]);
        if ($stmt_check_phone->fetch()) {
            $error = 'Phone number already registered by another user.';
        }
    }

    if (empty($error)) {
        $update_fields = [];
        $update_values = [];

        $update_fields[] = 'first_name = ?';
        $update_values[] = $first_name;
        $update_fields[] = 'last_name = ?';
        $update_values[] = $last_name;
        $update_fields[] = 'phone_number = ?';
        $update_values[] = $phone_number;

        if ($_SESSION['role'] === 'student') {
            $update_fields[] = 'level = ?';
            $update_values[] = $level;
        }

        // Handle password change
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error = 'Current password is required to change password.';
            } else {
                // Verify current password
                $stmt_verify_password = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt_verify_password->execute([$user_id]);
                $db_password = $stmt_verify_password->fetchColumn();

                if (!password_verify($current_password, $db_password)) {
                    $error = 'Incorrect current password.';
                } elseif ($new_password !== $confirm_new_password) {
                    $error = 'New password and confirm new password do not match.';
                } else {
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_fields[] = 'password = ?';
                    $update_values[] = $hashed_new_password;
                }
            }
        }

        // Handle profile image upload
        if (empty($error) && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            $imageFileType = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $new_image_name = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $new_image_name;
            $uploadOk = 1;

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES['profile_image']['tmp_name']);
            if ($check === false) {
                $error = "File is not an image.";
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES['profile_image']['size'] > 500000) { // 500KB
                $error = "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }

            if ($uploadOk == 0) {
                // Error already set
            } else {
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    // Delete old profile image if it's not the default
                    if ($user['profile_image'] !== 'default.png' && file_exists($target_dir . $user['profile_image'])) {
                        unlink($target_dir . $user['profile_image']);
                    }
                    $update_fields[] = 'profile_image = ?';
                    $update_values[] = $new_image_name;
                    $_SESSION['profile_image'] = $new_image_name; // Update session image
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }

        if (empty($error) && !empty($update_fields)) {
            $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $update_values[] = $user_id;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($update_values)) {
                // Update session variables
                $_SESSION['full_name'] = $first_name . ' ' . $last_name;
                $_SESSION['level'] = $level;
                $success_message = 'Profile updated successfully!';
                // Re-fetch user data to update the form with latest info
                $stmt = $pdo->prepare("SELECT username, first_name, last_name, phone_number, level, profile_image FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}
?>

<div class="form-container glass-ui">
    <h2>Edit Profile</h2>
    <?php if ($error): ?><p class="alert alert-danger"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success_message): ?><p class="alert alert-success"><?= htmlspecialchars($success_message) ?></p><?php endif; ?>

    <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
        <div class="profile-image-preview">
            <img src="uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image" id="profileImagePreview">
            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
            <label for="profile_image" class="btn btn-sm">Change Image</label>
        </div>

        <div class="form-group">
            <i class="fas fa-user"></i>
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="form-group">
            <i class="fas fa-user"></i>
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
        <div class="form-group">
            <i class="fas fa-phone"></i>
            <label for="phone_number">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" required>
        </div>

        <?php if ($_SESSION['role'] === 'student'): ?>
        <div class="form-group">
            <i class="fas fa-layer-group"></i>
            <label for="level">Level</label>
            <select id="level" name="level" required>
                <option value="" disabled>Select your level</option>
                <option value="1" <?= ($user['level'] == 1) ? 'selected' : '' ?>>Level 1</option>
                <option value="2" <?= ($user['level'] == 2) ? 'selected' : '' ?>>Level 2</option>
                <option value="3" <?= ($user['level'] == 3) ? 'selected' : '' ?>>Level 3</option>
                <option value="4" <?= ($user['level'] == 4) ? 'selected' : '' ?>>Level 4</option>
            </select>
        </div>
        <?php endif; ?>

        <h3>Change Password (Optional)</h3>
        <div class="form-group">
            <i class="fas fa-lock"></i>
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
        </div>
        <div class="form-group">
            <i class="fas fa-lock"></i>
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
        </div>
        <div class="form-group">
            <i class="fas fa-lock"></i>
            <label for="confirm_new_password">Confirm New Password</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password">
        </div>

        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>

<script>
    document.getElementById('profile_image').addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            document.getElementById('profileImagePreview').src = URL.createObjectURL(file);
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
