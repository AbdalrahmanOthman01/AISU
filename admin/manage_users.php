<?php
$pageTitle = 'Manage Users';
require_once 'partials/header.php';

// Fetch all students
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_feedback = $_SESSION['user_feedback'] ?? null;
unset($_SESSION['user_feedback']);
?>
<h2>Manage Student Accounts</h2>
<?php if ($user_feedback): ?><p class="alert alert-success"><?= htmlspecialchars($user_feedback) ?></p><?php endif; ?>

<div class="dashboard-card glass-ui">
    <table class="admin-table">
        <thead>
            <tr>
                <th><i class="fas fa-user"></i> Full Name</th>
                <th><i class="fas fa-phone"></i> Phone Number</th>
                <th><i class="fas fa-layer-group"></i> Level</th>
                <th><i class="fas fa-calendar-alt"></i> Joined On</th>
                <th><i class="fas fa-cogs"></i> Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                    <td><?= htmlspecialchars($student['phone_number']) ?></td>
                    <td><?= htmlspecialchars($student['level']) ?></td>
                    <td><?= date('M j, Y', strtotime($student['created_at'])) ?></td>
                    <td>
                        <form action="process_admin_action.php" method="POST" style="display:inline;">
                           <input type="hidden" name="user_id" value="<?= $student['id'] ?>">
                           <input type="hidden" name="phone_number" value="<?= $student['phone_number'] ?>">
                           <button type="submit" name="action" value="delete_user" onclick="return confirm('Delete this user? This is permanent.');" class="action-btn btn-delete"><i class="fas fa-trash-alt"></i> Delete User</button>
                           <button type="submit" name="action" value="block_user" onclick="return confirm('Block this number and delete user? The number cannot be used again.');" class="action-btn btn-block"><i class="fas fa-ban"></i> Block Number</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'partials/footer.php'; ?>
