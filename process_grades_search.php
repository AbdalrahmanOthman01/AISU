<?php
require_once 'config/db.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$announcement_id = (int)($_POST['announcement_id'] ?? 0);
$academic_number = trim($_POST['academic_number'] ?? '');

if (!$announcement_id || empty($academic_number)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Verify the announcement exists and belongs to student's level
$student_level = $_SESSION['level'];

// Debug: Add logging
error_log("Student Level: " . $student_level . " (Type: " . gettype($student_level) . ")");
error_log("Announcement ID: " . $announcement_id);

$stmt = $pdo->prepare("
    SELECT id, title, search_column, level
    FROM grades_announcements
    WHERE id = ? AND level = ? AND is_active = 1
");
$stmt->execute([$announcement_id, $student_level]);
$announcement = $stmt->fetch();

error_log("Announcement found: " . ($announcement ? "YES" : "NO"));
if ($announcement) {
    error_log("Announcement level: " . $announcement['level'] . " (Type: " . gettype($announcement['level']) . ")");
}

if (!$announcement) {
    echo json_encode(['success' => false, 'message' => 'Grade announcement not found or not accessible']);
    exit();
}

// Search for the student's grades (case insensitive)
$stmt = $pdo->prepare("
    SELECT row_data
    FROM grades_data
    WHERE announcement_id = ? AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(row_data, CONCAT('$.', ?)))) = LOWER(?)
    LIMIT 1
");
$stmt->execute([$announcement_id, $announcement['search_column'], $academic_number]);
$result = $stmt->fetch();

if ($result) {
    $grades_data = json_decode($result['row_data'], true);
    echo json_encode([
        'success' => true,
        'data' => $grades_data,
        'announcement_title' => $announcement['title']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No grades found for the entered academic number in this announcement.'
    ]);
}
?>
