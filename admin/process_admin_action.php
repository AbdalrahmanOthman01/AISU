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

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['admin_error'] = "Admin username '{$username}' already exists!";
    } else {
        $sql = "INSERT INTO users (username, first_name, last_name, phone_number, password, role) VALUES (?, ?, 'Admin', '', ?, 'admin')";
        $pdo->prepare($sql)->execute([$username, $name, $password]);
        $_SESSION['admin_feedback'] = 'New admin account created successfully!';
    }
    header('Location: manage_admins.php');
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

// Fallback redirect
else {
    header('Location: dashboard.php');
}
exit();