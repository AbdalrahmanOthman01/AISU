<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['submit_message'])) {
    $message_text = trim($_POST['message_text']);
    $user_id = $_SESSION['user_id'];

    if (!empty($message_text)) {
        $sql = "INSERT INTO messages (user_id, message_text) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $message_text]);
    }
}

header('Location: dashboard.php');
exit();
?>