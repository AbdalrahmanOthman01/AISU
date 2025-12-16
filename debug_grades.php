<?php
// Debug script to check grades database contents
require_once 'config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Access denied. Admin only.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades Debug - AISU</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 30px 0; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .section h2 { margin-top: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Grades Database Debug</h1>

    <div class="section">
        <h2>Database Tables Check</h2>
        <?php
        try {
            // Check if grades tables exist
            $tables = $pdo->query("SHOW TABLES LIKE 'grades_%'")->fetchAll(PDO::FETCH_COLUMN);
            if (count($tables) >= 2) {
                echo '<p class="success">✓ Grades tables exist: ' . implode(', ', $tables) . '</p>';
            } else {
                echo '<p class="error">✗ Grades tables missing. Please run the SQL commands from manage_grades.php error message.</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">✗ Database error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Grades Announcements</h2>
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM grades_announcements ORDER BY created_at DESC");
            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($announcements) > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Title</th><th>Level</th><th>Search Column</th><th>Active</th><th>Created</th></tr>';
                foreach ($announcements as $ann) {
                    echo '<tr>';
                    echo '<td>' . $ann['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($ann['title']) . '</td>';
                    echo '<td>' . $ann['level'] . ' (' . gettype($ann['level']) . ')</td>';
                    echo '<td>' . $ann['search_column'] . '</td>';
                    echo '<td>' . ($ann['is_active'] ? 'Yes' : 'No') . '</td>';
                    echo '<td>' . $ann['created_at'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No grade announcements found. Upload some grade files first.</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">Error fetching announcements: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Sample Grade Data</h2>
        <?php
        try {
            $stmt = $pdo->query("SELECT gd.*, ga.title FROM grades_data gd JOIN grades_announcements ga ON gd.announcement_id = ga.id LIMIT 5");
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($samples) > 0) {
                foreach ($samples as $sample) {
                    echo '<h3>Announcement: ' . htmlspecialchars($sample['title']) . '</h3>';
                    $data = json_decode($sample['row_data'], true);
                    echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
                }
            } else {
                echo '<p>No grade data found.</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">Error fetching grade data: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Student Level Check</h2>
        <?php
        try {
            $stmt = $pdo->query("SELECT id, username, first_name, last_name, level FROM users WHERE role = 'student' LIMIT 10");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($students) > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Username</th><th>Name</th><th>Level</th><th>Type</th></tr>';
                foreach ($students as $student) {
                    echo '<tr>';
                    echo '<td>' . $student['id'] . '</td>';
                    echo '<td>' . $student['username'] . '</td>';
                    echo '<td>' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</td>';
                    echo '<td>' . $student['level'] . '</td>';
                    echo '<td>' . gettype($student['level']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No students found.</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">Error fetching students: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>PHP Error Log Location</h2>
        <p>Check your PHP error log for debug messages from grades.php and process_grades_search.php</p>
        <p>Common locations:</p>
        <ul>
            <li>XAMPP: C:\xampp\php\logs\php_error_log</li>
            <li>Linux: /var/log/apache2/error.log</li>
            <li>Or check your php.ini for error_log setting</li>
        </ul>
    </div>

    <p><a href="admin/manage_grades.php">← Back to Manage Grades</a></p>
</body>
</html>
