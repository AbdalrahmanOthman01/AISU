<?php
$pageTitle = 'My Dashboard - AISU Inquiry';
require_once 'includes/header.php';

// Protect page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Fetch user's messages
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>
<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3>Submit a New Inquiry</h3>
        <form action="process_message.php" method="POST">
            <div class="form-group">
                <label for="message_text">Your question or message:</label>
                <textarea id="message_text" name="message_text" required></textarea>
            </div>
            <button type="submit" name="submit_message" class="btn btn-primary">Send Message</button>
        </form>
    </div>
    <div class="dashboard-card">
        <h3>My Message History</h3>
        <?php if (count($my_messages) > 0): ?>
            <?php foreach($my_messages as $msg): ?>
                <div class="message-card" style="margin-bottom: 1rem;">
                    <p><strong>You asked on <?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?>:</strong></p>
                    <blockquote><?= htmlspecialchars($msg['message_text']) ?></blockquote>
                    
                    <?php if ($msg['admin_response']): ?>
                        <div class="message-response">
                            <strong>AISU Response on <?= date('M j, Y g:i A', strtotime($msg['responded_at'])) ?>:</strong>
                            <p><?= htmlspecialchars($msg['admin_response']) ?></p>
                        </div>
                    <?php else: ?>
                        <p class="alert alert-info">Awaiting response from admin.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have not sent any messages yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>