<?php
require_once __DIR__ . '/../config/db.php';

// Protect this file
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_POST['action'])) {
    header('Location: ../login.php');
    exit();
}

$action = $_POST['action'];

// Message actions
if ($action == 'reply' && isset($_POST['message_id'], $_POST['admin_response'])) {
    $sql = "UPDATE messages SET admin_response = ?, responded_at = NOW() WHERE id = ?";
    $pdo->prepare($sql)->execute([$_POST['admin_response'], $_POST['message_id']]);
    $_SESSION['msg_feedback'] = 'Response sent/updated successfully!';
    header('Location: dashboard.php');
}
elseif ($action == 'approve' && isset($_POST['message_id'])) {
    $sql = "UPDATE messages SET is_public = 1 WHERE id = ?";
    $pdo->prepare($sql)->execute([$_POST['message_id']]);
    $_SESSION['msg_feedback'] = 'Message approved for public view!';
    header('Location: dashboard.php');
}
elseif ($action == 'unapprove' && isset($_POST['message_id'])) {
    $sql = "UPDATE messages SET is_public = 0 WHERE id = ?";
    $pdo->prepare($sql)->execute([$_POST['message_id']]);
    $_SESSION['msg_feedback'] = 'Message hidden from public view.';
    header('Location: dashboard.php');
}
elseif ($action == 'delete_message' && isset($_POST['message_id'])) {
    $sql = "DELETE FROM messages WHERE id = ?";
    $pdo->prepare($sql)->execute([$_POST['message_id']]);
    $_SESSION['msg_feedback'] = 'Message deleted permanently.';
    header('Location: dashboard.php');
}

// User management actions
elseif ($action == 'delete_user' && isset($_POST['user_id'])) {
    $sql = "DELETE FROM users WHERE id = ? AND role = 'student'";
    $pdo->prepare($sql)->execute([$_POST['user_id']]);
    $_SESSION['user_feedback'] = 'User account has been deleted.';
    header('Location: manage_users.php');
}
elseif ($action == 'block_user' && isset($_POST['user_id'], $_POST['phone_number'])) {
    // Add to blocked list first
    $sql = "INSERT INTO blocked_numbers (phone_number) VALUES (?) ON DUPLICATE KEY UPDATE phone_number=phone_number";
    $pdo->prepare($sql)->execute([$_POST['phone_number']]);

    // Then delete user
    $sql_del = "DELETE FROM users WHERE id = ?";
    $pdo->prepare($sql_del)->execute([$_POST['user_id']]);

    $_SESSION['user_feedback'] = 'User deleted and their phone number has been blocked.';
    header('Location: manage_users.php');
}

// Admin management actions
elseif ($action == 'create_admin' && isset($_POST['admin_name'], $_POST['admin_password'])) {
    $name = preg_replace('/[^A-Za-z0-9_]/', '', $_POST['admin_name']); // Sanitize name
    $username = 'AISU_' . $name;
    $password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
    $profile_image = 'default.png'; // Default image

    // Handle profile image upload
    if (isset($_FILES['admin_profile_image']) && $_FILES['admin_profile_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        $imageFileType = strtolower(pathinfo($_FILES['admin_profile_image']['name'], PATHINFO_EXTENSION));
        $new_image_name = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_image_name;
        $uploadOk = 1;

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['admin_profile_image']['tmp_name']);
        if ($check === false) {
            $_SESSION['admin_error'] = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES['admin_profile_image']['size'] > 500000) { // 500KB
            $_SESSION['admin_error'] = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $_SESSION['admin_error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1 && move_uploaded_file($_FILES['admin_profile_image']['tmp_name'], $target_file)) {
            $profile_image = $new_image_name;
        }
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['admin_error'] = "Admin username '{$username}' already exists!";
    } elseif (!isset($_SESSION['admin_error'])) {
        $sql = "INSERT INTO users (username, first_name, last_name, phone_number, password, role, profile_image) VALUES (?, ?, 'Admin', '', ?, 'admin', ?)";
        $pdo->prepare($sql)->execute([$username, $name, $password, $profile_image]);
        $_SESSION['admin_feedback'] = 'New admin account created successfully!';
    }
    header('Location: manage_admins.php');
}
elseif ($action == 'create_student' && isset($_POST['first_name'], $_POST['last_name'], $_POST['phone_number'], $_POST['level'], $_POST['student_password'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    $level = (int)$_POST['level'];
    $password = password_hash($_POST['student_password'], PASSWORD_DEFAULT);
    $profile_image = 'default.png'; // Default image

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($phone_number) || !in_array($level, [1, 2, 3, 4])) {
        $_SESSION['user_error'] = "All fields are required and level must be between 1-4.";
    } else {
        // Check if phone number already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        if ($stmt->fetch()) {
            $_SESSION['user_error'] = "Phone number already registered!";
        } else {
            // Handle profile image upload
            if (isset($_FILES['student_profile_image']) && $_FILES['student_profile_image']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "../uploads/";
                $imageFileType = strtolower(pathinfo($_FILES['student_profile_image']['name'], PATHINFO_EXTENSION));
                $new_image_name = uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $new_image_name;
                $uploadOk = 1;

                // Check if image file is a actual image or fake image
                $check = getimagesize($_FILES['student_profile_image']['tmp_name']);
                if ($check === false) {
                    $_SESSION['user_error'] = "File is not an image.";
                    $uploadOk = 0;
                }

                // Check file size
                if ($_FILES['student_profile_image']['size'] > 500000) { // 500KB
                    $_SESSION['user_error'] = "Sorry, your file is too large.";
                    $uploadOk = 0;
                }

                // Allow certain file formats
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" ) {
                    $_SESSION['user_error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    $uploadOk = 0;
                }

                if ($uploadOk == 1 && move_uploaded_file($_FILES['student_profile_image']['tmp_name'], $target_file)) {
                    $profile_image = $new_image_name;
                }
            }

            if (!isset($_SESSION['user_error'])) {
                // Generate username from first and last name
                $username = strtolower($first_name . '_' . $last_name);
                $username = preg_replace('/[^a-z0-9_]/', '', $username);

                // Ensure username is unique
                $base_username = $username;
                $counter = 1;
                while (true) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if (!$stmt->fetch()) break;
                    $username = $base_username . $counter;
                    $counter++;
                }

                $sql = "INSERT INTO users (username, first_name, last_name, phone_number, password, level, role, profile_image) VALUES (?, ?, ?, ?, ?, ?, 'student', ?)";
                $pdo->prepare($sql)->execute([$username, $first_name, $last_name, $phone_number, $password, $level, $profile_image]);
                $_SESSION['user_feedback'] = "Student account created successfully! Username: {$username}";
            }
        }
    }
    header('Location: manage_users.php');
}
elseif ($action == 'delete_admin' && isset($_POST['user_id'])) {
    // Prevent deleting main account or self
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
    $admin = $stmt->fetch();

    if ($admin['username'] === 'AISU' || $_POST['user_id'] == $_SESSION['user_id']) {
        $_SESSION['admin_error'] = 'Cannot delete the main admin account or your own account.';
    } else {
        $sql = "DELETE FROM users WHERE id = ? AND role = 'admin'";
        $pdo->prepare($sql)->execute([$_POST['user_id']]);
        $_SESSION['admin_feedback'] = 'Admin account has been deleted.';
    }
    header('Location: manage_admins.php');
}

// Grades management actions
elseif ($action == 'upload_grades' && isset($_POST['announcement_title'], $_POST['grade_level'], $_POST['search_column']) && isset($_FILES['grades_file'])) {
    try {
        $title = trim($_POST['announcement_title']);
        $description = trim($_POST['announcement_description'] ?? '');
        $level = (int)$_POST['grade_level'];
        $search_column = trim($_POST['search_column']);

        if (empty($title) || $level < 1 || $level > 4 || empty($search_column)) {
            $_SESSION['grades_error'] = 'Please fill in all required fields correctly.';
            header('Location: manage_grades.php');
            exit();
        }

        // Validate file
        if ($_FILES['grades_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['grades_error'] = 'File upload failed.';
            header('Location: manage_grades.php');
            exit();
        }

        if ($_FILES['grades_file']['size'] > 5242880) { // 5MB
            $_SESSION['grades_error'] = 'File size must be less than 5MB.';
            header('Location: manage_grades.php');
            exit();
        }

        // Parse CSV file
        $file = $_FILES['grades_file']['tmp_name'];
        $handle = fopen($file, 'r');

        if (!$handle) {
            $_SESSION['grades_error'] = 'Could not read the uploaded file.';
            header('Location: manage_grades.php');
            exit();
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 2) {
            $_SESSION['grades_error'] = 'CSV file must have at least 2 columns.';
            fclose($handle);
            header('Location: manage_grades.php');
            exit();
        }

        // Validate search column exists in headers
        if (!in_array($search_column, $headers)) {
            $_SESSION['grades_error'] = "Search column '{$search_column}' not found in CSV headers. Available columns: " . implode(', ', $headers);
            fclose($handle);
            header('Location: manage_grades.php');
            exit();
        }

        // Read all data
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === count($headers)) {
                $row_data = array_combine($headers, $data);
                if ($row_data && isset($row_data[$search_column]) && !empty($row_data[$search_column])) {
                    $rows[] = $row_data;
                }
            }
        }
        fclose($handle);

        if (empty($rows)) {
            $_SESSION['grades_error'] = "No valid data found in CSV file. Make sure the search column '{$search_column}' contains valid data.";
            header('Location: manage_grades.php');
            exit();
        }

        // Save file
        $file_name = uniqid() . '_grades.csv';
        $upload_path = '../uploads/' . $file_name;
        if (!move_uploaded_file($_FILES['grades_file']['tmp_name'], $upload_path)) {
            $_SESSION['grades_error'] = 'Failed to save uploaded file.';
            header('Location: manage_grades.php');
            exit();
        }

        // Insert announcement
        $stmt = $pdo->prepare("
            INSERT INTO grades_announcements (title, description, level, search_column, file_name, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $level, $search_column, $file_name, $_SESSION['user_id']]);
        $announcement_id = $pdo->lastInsertId();

        // Insert grade data
        $stmt = $pdo->prepare("INSERT INTO grades_data (announcement_id, row_data) VALUES (?, ?)");
        foreach ($rows as $row) {
            $stmt->execute([$announcement_id, json_encode($row)]);
        }

        $_SESSION['grades_feedback'] = "Grade announcement uploaded successfully! {$title} for Level {$level}. Students can search using their {$search_column}.";
    } catch (PDOException $e) {
        $_SESSION['grades_error'] = 'Database error: ' . $e->getMessage() . '. Please make sure the database tables are created.';
    }
    header('Location: manage_grades.php');
}
elseif ($action == 'delete_announcement' && isset($_POST['announcement_id'])) {
    // Delete file if exists
    $stmt = $pdo->prepare("SELECT file_name FROM grades_announcements WHERE id = ?");
    $stmt->execute([$_POST['announcement_id']]);
    $announcement = $stmt->fetch();

    if ($announcement) {
        $file_path = '../uploads/' . $announcement['file_name'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Delete from database (CASCADE will handle grades_data)
    $stmt = $pdo->prepare("DELETE FROM grades_announcements WHERE id = ?");
    $stmt->execute([$_POST['announcement_id']]);

    $_SESSION['grades_feedback'] = 'Grade announcement deleted successfully.';
    header('Location: manage_grades.php');
}
elseif ($action == 'toggle_announcement' && isset($_POST['announcement_id'])) {
    $stmt = $pdo->prepare("UPDATE grades_announcements SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_POST['announcement_id']]);

    $_SESSION['grades_feedback'] = 'Announcement status updated successfully.';
    header('Location: manage_grades.php');
}

// Fallback redirect
else {
    header('Location: dashboard.php');
}
exit();
