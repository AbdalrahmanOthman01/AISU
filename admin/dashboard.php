<?php
$pageTitle = 'Admin Dashboard - Messages';
require_once 'partials/header.php';

// Fetch all messages with user details
$stmt = $pdo->query("
    SELECT m.*, u.first_name, u.last_name, u.profile_image, u.level
    FROM messages m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC
");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg_feedback = $_SESSION['msg_feedback'] ?? null;
unset($_SESSION['msg_feedback']);
?>

<h2>Message Management</h2>
<?php if ($msg_feedback): ?><p class="alert alert-success"><?= htmlspecialchars($msg_feedback) ?></p><?php endif; ?>

<div class="board">
    <?php if (count($messages) > 0): ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message-card glass-ui">
                <div class="message-header">
                    <img src="../uploads/<?= htmlspecialchars($msg['profile_image']) ?>" alt="Profile">
                    <div class="message-header-info">
                        <h4><?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?> (Level <?= $msg['level'] ?>)</h4>
                        <p>Asked on: <?= date('M j, Y, g:i a', strtotime($msg['created_at'])) ?></p>
                    </div>
                </div>
                <div class="message-content">
                    <blockquote>"<?= htmlspecialchars($msg['message_text']) ?>"</blockquote>
                </div>

                <div class="message-response-form" style="margin-top: 1rem;">
                    <form action="process_admin_action.php" method="POST">
                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                        <div class="form-group">
                            <textarea name="admin_response" placeholder="Type your response here..." required><?= htmlspecialchars($msg['admin_response'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="action" value="reply" class="action-btn btn-reply"><i class="fas fa-reply"></i> Reply / Update</button>
                        
                        <?php if ($msg['is_public'] == 0): ?>
                        <button type="submit" name="action" value="approve" class="action-btn btn-approve"><i class="fas fa-eye"></i> Show on Home</button>
                        <?php else: ?>
                        <button type="submit" name="action" value="unapprove" class="action-btn btn-block"><i class="fas fa-eye-slash"></i> Hide from Home</button>
                        <?php endif; ?>

                        <button type="submit" name="action" value="delete_message" onclick="return confirm('Are you sure you want to delete this message?');" class="action-btn btn-delete"><i class="fas fa-trash-alt"></i> Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="alert alert-info glass-ui">There are no messages from users yet.</p>
    <?php endif; ?>
</div>

<?php require_once 'partials/footer.php'; ?>
