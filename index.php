<?php
$pageTitle = 'Home - AISU Inquiry';
require_once 'includes/header.php';

// Fetch public messages
$stmt = $pdo->query("
    SELECT m.*, u.first_name, u.last_name, u.profile_image
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.is_public = 1
    ORDER BY m.responded_at DESC
");
$public_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Public Inquiry Board</h2>
<p>Here are some of the inquiries that have been answered and made public by the AISU team.</p>
<hr>

<div class="board">
    <?php if (count($public_messages) > 0): ?>
        <?php foreach ($public_messages as $msg): ?>
            <div class="message-card">
                <div class="message-header">
                    <img src="uploads/<?= htmlspecialchars($msg['profile_image']) ?>" alt="Profile">
                    <div class="message-header-info">
                        <h4><?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?></h4>
                        <p>Asked on: <?= date('M j, Y', strtotime($msg['created_at'])) ?></p>
                    </div>
                </div>
                <div class="message-content">
                    <blockquote>"<?= htmlspecialchars($msg['message_text']) ?>"</blockquote>
                </div>
                <?php if ($msg['admin_response']): ?>
                <div class="message-response">
                    <strong>AISU Response:</strong>
                    <p><?= htmlspecialchars($msg['admin_response']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="alert alert-info">No public inquiries to show at the moment.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>