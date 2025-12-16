<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once 'config/db.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success_message = '';
$updated_fields = [];

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
        if (empty($error) && isset($_FILES['profile_image_file']) && $_FILES['profile_image_file']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            $imageFileType = strtolower(pathinfo($_FILES['profile_image_file']['name'], PATHINFO_EXTENSION));
            $new_image_name = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $new_image_name;
            $uploadOk = 1;

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES['profile_image_file']['tmp_name']);
            if ($check === false) {
                $error = "File is not an image.";
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES['profile_image_file']['size'] > 500000) { // 500KB
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
                // Get current profile image
                $stmt_current_image = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                $stmt_current_image->execute([$user_id]);
                $current_image = $stmt_current_image->fetchColumn();

                if (move_uploaded_file($_FILES['profile_image_file']['tmp_name'], $target_file)) {
                    // Delete old profile image if it's not the default
                    if ($current_image !== 'default.png' && file_exists($target_dir . $current_image)) {
                        unlink($target_dir . $current_image);
                    }
                    $update_fields[] = 'profile_image = ?';
                    $update_values[] = $new_image_name;
                    $updated_fields['profile_image'] = $new_image_name;
                    $_SESSION['profile_image'] = $new_image_name;
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
                $updated_fields['full_name'] = $_SESSION['full_name'];
                if ($_SESSION['role'] === 'student') {
                    $_SESSION['level'] = $level;
                }
                $success_message = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => empty($error),
    'message' => empty($error) ? $success_message : $error,
    'updated_fields' => $updated_fields
]);
?>
