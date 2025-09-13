<?php
$pageTitle = 'Manage Admins';
require_once 'partials/header.php';

// Fetch all admins
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

$admin_feedback = $_SESSION['admin_feedback'] ?? null;
$admin_error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_feedback'], $_SESSION['admin_error']);
?>

<h2>Manage Admin Accounts</h2>

<div class="dashboard-grid">
    <div class="dashboard-card glass-ui">
        <h3><i class="fas fa-user-plus"></i> Create New Admin</h3>
        <?php if ($admin_error): ?><p class="alert alert-danger"><?= htmlspecialchars($admin_error) ?></p><?php endif; ?>
        <?php if ($admin_feedback): ?><p class="alert alert-success"><?= htmlspecialchars($admin_feedback) ?></p><?php endif; ?>
        
        <form action="process_admin_action.php" method="POST">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <label for="admin_name">Name (for username AISU_Name)</label>
                <input type="text" id="admin_name" name="admin_name" placeholder="Enter admin name" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <label for="admin_password">Password</label>
                <input type="password" id="admin_password" name="admin_password" placeholder="Choose a password" required>
            </div>
            <button type="submit" name="action" value="create_admin" class="btn"><i class="fas fa-plus-circle"></i> Create Admin</button>
        </form>
    </div>

    <div class="dashboard-card glass-ui">
        <h3><i class="fas fa-users-cog"></i> Existing Admins</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($admins as $admin): ?>
                <tr>
                    <td><?= htmlspecialchars($admin['username']) ?></td>
                    <td>
                        <?php if ($admin['username'] !== 'AISU'): ?>
                        <form action="process_admin_action.php" method="POST">
                           <input type="hidden" name="user_id" value="<?= $admin['id'] ?>">
                           <button type="submit" name="action" value="delete_admin" onclick="return confirm('Delete this admin account?');" class="action-btn btn-delete"><i class="fas fa-trash-alt"></i> Delete</button>
                        </form>
                        <?php else: ?>
                            (Main Account)
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
