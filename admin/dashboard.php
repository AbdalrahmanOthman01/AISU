<?php
$pageTitle = 'Admin Dashboard - AISU System Overview';
require_once 'partials/header.php';

// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$totalMessages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$pendingMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE admin_response IS NULL OR admin_response = ''")->fetchColumn();
$respondedMessages = $totalMessages - $pendingMessages;
$publicMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_public = 1")->fetchColumn();

// Get recent messages
$stmt = $pdo->query("
    SELECT m.*, u.first_name, u.last_name, u.profile_image, u.level
    FROM messages m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC
    LIMIT 5
");
$recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent users
$stmt = $pdo->query("
    SELECT username, first_name, last_name, created_at, level
    FROM users
    WHERE role = 'student'
    ORDER BY created_at DESC
    LIMIT 5
");
$recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg_feedback = $_SESSION['msg_feedback'] ?? null;
unset($_SESSION['msg_feedback']);
?>

<!-- Admin Dashboard Header -->
<div class="admin-dashboard-header">
    <div class="admin-welcome-section">
        <h1><i class="fas fa-crown"></i> Administrator Dashboard</h1>
        <p class="admin-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>! Here's what's happening with your AISU system.</p>
    </div>
    <div class="admin-quick-stats">
        <div class="quick-stat-card">
            <div class="stat-icon admin-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $totalUsers; ?></span>
                <span class="stat-label">Total Students</span>
            </div>
        </div>
        <div class="quick-stat-card">
            <div class="stat-icon admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $totalAdmins; ?></span>
                <span class="stat-label">Administrators</span>
            </div>
        </div>
        <div class="quick-stat-card">
            <div class="stat-icon admin-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $totalMessages; ?></span>
                <span class="stat-label">Total Inquiries</span>
            </div>
        </div>
    </div>
</div>

<?php if ($msg_feedback): ?>
    <div class="alert alert-success admin-alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($msg_feedback); ?>
    </div>
<?php endif; ?>

<!-- Main Dashboard Grid -->
<div class="admin-dashboard-grid">

    <!-- System Overview Cards -->
    <div class="admin-card system-overview">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3>System Overview</h3>
        </div>
        <div class="card-content">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value pending"><?php echo $pendingMessages; ?></div>
                    <div class="stat-label">Pending Responses</div>
                    <div class="stat-progress">
                        <div class="progress-bar" style="width: <?php echo $totalMessages > 0 ? ($pendingMessages / $totalMessages) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-value resolved"><?php echo $respondedMessages; ?></div>
                    <div class="stat-label">Resolved Inquiries</div>
                    <div class="stat-progress">
                        <div class="progress-bar resolved" style="width: <?php echo $totalMessages > 0 ? ($respondedMessages / $totalMessages) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-value public"><?php echo $publicMessages; ?></div>
                    <div class="stat-label">Public Messages</div>
                    <div class="stat-progress">
                        <div class="progress-bar public" style="width: <?php echo $totalMessages > 0 ? ($publicMessages / $totalMessages) * 100 : 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-card quick-actions">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <h3>Quick Actions</h3>
        </div>
        <div class="card-content">
            <div class="action-buttons">
                <a href="manage_users.php" class="action-btn primary">
                    <i class="fas fa-users-cog"></i>
                    <span>Manage Users</span>
                </a>
                <a href="manage_admins.php" class="action-btn secondary">
                    <i class="fas fa-user-shield"></i>
                    <span>Manage Admins</span>
                </a>
                <a href="../index.php" class="action-btn info" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>View Public Site</span>
                </a>
                <a href="../edit_profile.php" class="action-btn warning">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Profile</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="admin-card recent-activity">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h3>Recent Inquiries</h3>
            <a href="#all-messages" class="view-all-link">View All</a>
        </div>
        <div class="card-content">
            <?php if (count($recentMessages) > 0): ?>
                <div class="recent-messages-list">
                    <?php foreach ($recentMessages as $msg): ?>
                        <div class="recent-message-item <?php echo $msg['admin_response'] ? 'responded' : 'pending'; ?>">
                            <div class="message-avatar">
                                <img src="../uploads/<?php echo htmlspecialchars($msg['profile_image']); ?>" alt="User">
                            </div>
                            <div class="message-info">
                                <div class="message-meta">
                                    <span class="user-name"><?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?></span>
                                    <span class="message-time"><?php echo date('M j, g:i a', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div class="message-preview">
                                    <?php echo htmlspecialchars(substr($msg['message_text'], 0, 60)) . (strlen($msg['message_text']) > 60 ? '...' : ''); ?>
                                </div>
                                <div class="message-status">
                                    <span class="status-badge <?php echo $msg['admin_response'] ? 'status-resolved' : 'status-pending'; ?>">
                                        <i class="fas fa-<?php echo $msg['admin_response'] ? 'check-circle' : 'clock'; ?>"></i>
                                        <?php echo $msg['admin_response'] ? 'Resolved' : 'Pending'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="message-actions">
                                <a href="#respond-<?php echo $msg['id']; ?>" class="mini-action-btn" title="Respond">
                                    <i class="fas fa-reply"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p>No inquiries yet. Messages from students will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="admin-card recent-users">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3>New Students</h3>
            <a href="manage_users.php" class="view-all-link">View All</a>
        </div>
        <div class="card-content">
            <?php if (count($recentUsers) > 0): ?>
                <div class="recent-users-list">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="recent-user-item">
                            <div class="user-avatar">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                <div class="user-meta">
                                    <span class="user-level">Level <?php echo $user['level']; ?></span>
                                    <span class="user-date"><?php echo date('M j', strtotime($user['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <p>No new students yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- System Status -->
    <div class="admin-card system-status">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-server"></i>
            </div>
            <h3>System Status</h3>
        </div>
        <div class="card-content">
            <div class="status-indicators">
                <div class="status-item">
                    <div class="status-icon online">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-title">Database</span>
                        <span class="status-desc">Connected & Operational</span>
                    </div>
                </div>
                <div class="status-item">
                    <div class="status-icon online">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-title">File System</span>
                        <span class="status-desc">Uploads Active</span>
                    </div>
                </div>
                <div class="status-item">
                    <div class="status-icon online">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-title">Server</span>
                        <span class="status-desc">Running Smoothly</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Message Management Section (Collapsible) -->
<div id="all-messages" class="message-management-section">
    <div class="section-header">
        <h2><i class="fas fa-envelope-open-text"></i> Message Management</h2>
        <button class="toggle-section-btn" id="toggleMessages">
            <i class="fas fa-chevron-down"></i>
            <span>Toggle Section</span>
        </button>
    </div>

    <div class="message-management-content" id="messageContent">
        <?php
        // Fetch all messages for management
        $stmt = $pdo->query("
            SELECT m.*, u.first_name, u.last_name, u.profile_image, u.level
            FROM messages m
            JOIN users u ON m.user_id = u.id
            ORDER BY m.created_at DESC
        ");
        $allMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (count($allMessages) > 0): ?>
            <div class="messages-grid">
                <?php foreach ($allMessages as $msg): ?>
                    <div class="message-management-card" id="respond-<?php echo $msg['id']; ?>">
                        <div class="message-header">
                            <div class="user-info">
                                <img src="../uploads/<?php echo htmlspecialchars($msg['profile_image']); ?>" alt="Profile" class="user-avatar">
                                <div class="user-details">
                                    <h4><?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?></h4>
                                    <span class="user-level">Level <?php echo $msg['level']; ?> • <?php echo date('M j, Y \a\t g:i a', strtotime($msg['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="message-actions">
                                <span class="message-status <?php echo $msg['admin_response'] ? 'status-resolved' : 'status-pending'; ?>">
                                    <i class="fas fa-<?php echo $msg['admin_response'] ? 'check-circle' : 'clock'; ?>"></i>
                                    <?php echo $msg['admin_response'] ? 'Resolved' : 'Pending'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="message-content">
                            <div class="user-message">
                                <h5>Student Inquiry:</h5>
                                <p><?php echo htmlspecialchars($msg['message_text']); ?></p>
                            </div>

                            <?php if ($msg['admin_response']): ?>
                                <div class="admin-response">
                                    <h5>Your Response:</h5>
                                    <p><?php echo htmlspecialchars($msg['admin_response']); ?></p>
                                    <?php if ($msg['responded_at']): ?>
                                        <small class="response-time">Responded on <?php echo date('M j, Y \a\t g:i a', strtotime($msg['responded_at'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="message-actions">
                            <form action="process_admin_action.php" method="POST" class="response-form">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">

                                <div class="form-group">
                                    <label for="response-<?php echo $msg['id']; ?>">Your Response:</label>
                                    <textarea id="response-<?php echo $msg['id']; ?>" name="admin_response" placeholder="Type your response here..." required><?php echo htmlspecialchars($msg['admin_response'] ?? ''); ?></textarea>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" name="action" value="reply" class="btn btn-primary">
                                        <i class="fas fa-reply"></i>
                                        <?php echo $msg['admin_response'] ? 'Update Response' : 'Send Response'; ?>
                                    </button>

                                    <?php if ($msg['is_public'] == 0): ?>
                                        <button type="submit" name="action" value="approve" class="btn btn-success">
                                            <i class="fas fa-eye"></i>
                                            Show Publicly
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="unapprove" class="btn btn-warning">
                                            <i class="fas fa-eye-slash"></i>
                                            Hide from Public
                                        </button>
                                    <?php endif; ?>

                                    <button type="submit" name="action" value="delete_message"
                                            onclick="return confirm('Are you sure you want to delete this message? This action cannot be undone.');"
                                            class="btn btn-danger">
                                        <i class="fas fa-trash-alt"></i>
                                        Delete Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state full">
                <div class="empty-icon large">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <h3>No Messages Yet</h3>
                <p>Student inquiries will appear here once they start sending messages through the system.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Toggle message management section
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleMessages');
    const messageContent = document.getElementById('messageContent');

    if (toggleBtn && messageContent) {
        toggleBtn.addEventListener('click', function() {
            messageContent.classList.toggle('collapsed');
            toggleBtn.classList.toggle('rotated');
        });
    }
});
</script>

<style>
/* Admin Dashboard Specific Styles - Theme Responsive */
.admin-dashboard-header {
    margin-bottom: 3rem;
    padding: 3rem 2rem;
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(139, 92, 246, 0.1) 100%);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 2px solid var(--bg-glass-border);
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-glass);
    transition: all 0.3s ease;
}

[data-theme="dark"] .admin-dashboard-header {
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(139, 92, 246, 0.15) 100%);
    border-color: rgba(139, 92, 246, 0.3);
    box-shadow: 0 8px 32px rgba(139, 92, 246, 0.2);
}

.admin-dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--bg-glass) 0%, var(--bg-glass) 100%);
    border-radius: 24px;
}

.admin-welcome-section h1 {
    font-size: 2.8rem;
    font-weight: 900;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
    font-family: 'Poppins', sans-serif;
}

.admin-welcome-section h1 i {
    color: var(--secondary-color);
    margin-right: 1rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.admin-subtitle {
    font-size: 1.3rem;
    color: var(--text-secondary);
    margin: 0;
    font-weight: 500;
    position: relative;
    z-index: 1;
    font-family: 'Poppins', sans-serif;
}

.admin-quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 2.5rem;
    position: relative;
    z-index: 1;
}

.quick-stat-card {
    background: var(--bg-glass);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: 16px;
    padding: 1.5rem;
    border: 2px solid var(--bg-glass-border);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-light);
}

.quick-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    border-color: var(--primary-color);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-medium);
}

.stat-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Poppins', sans-serif;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: 'Poppins', sans-serif;
}

/* Enhanced hover effects */
.quick-stat-card:hover .stat-number {
    color: var(--primary-color);
    text-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.quick-stat-card:hover .stat-label {
    color: var(--primary-color);
}

/* Message Management Section Styles - Dark Theme */
.message-management-section {
    margin-top: 4rem;
    background: var(--bg-secondary);
    border-radius: 20px;
    border: 2px solid var(--bg-glass-border);
    overflow: hidden;
    box-shadow: var(--shadow-glass);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem;
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(139, 92, 246, 0.1) 100%);
    border-bottom: 1px solid var(--bg-glass-border);
}

.section-header h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.section-header h2 i {
    color: var(--secondary-color);
}

.toggle-section-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(139, 92, 246, 0.1) 100%);
    border: 2px solid var(--bg-glass-border);
    border-radius: 50px;
    color: var(--text-secondary);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-light);
}

.toggle-section-btn:hover {
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(139, 92, 246, 0.2) 100%);
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.toggle-section-btn.rotated i {
    transform: rotate(180deg);
}

.message-management-content {
    max-height: 2000px;
    transition: max-height 0.5s ease, opacity 0.3s ease;
}

.message-management-content.collapsed {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
}

.messages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 2rem;
    padding: 2rem;
}

.message-management-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 2px solid var(--bg-glass-border);
    overflow: hidden;
    box-shadow: var(--shadow-glass);
    transition: all 0.3s ease;
}

.message-management-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(31, 38, 135, 0.4);
    border-color: rgba(99, 102, 241, 0.3);
}

.message-management-card .message-header {
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(99, 102, 241, 0.05) 100%);
    border-bottom: 1px solid var(--bg-glass-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.message-management-card .user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.message-management-card .user-avatar {
    width: 55px;
    height: 55px;
    border-radius: 14px;
    border: 3px solid var(--bg-glass-border);
    box-shadow: var(--shadow-medium);
}

.message-management-card .user-details h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.message-management-card .user-level {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-weight: 500;
}

.message-management-card .message-status {
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.message-management-card .message-content {
    padding: 2rem;
}

.message-management-card .user-message,
.message-management-card .admin-response {
    margin-bottom: 2rem;
}

.message-management-card .user-message:last-child,
.message-management-card .admin-response:last-child {
    margin-bottom: 0;
}

.message-management-card h5 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.message-management-card .user-message h5 {
    color: var(--secondary-color);
}

.message-management-card .admin-response h5 {
    color: var(--success-color);
}

.message-management-card .user-message p,
.message-management-card .admin-response p {
    margin: 0;
    color: var(--text-secondary);
    line-height: 1.6;
    background: rgba(0, 0, 0, 0.2);
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid var(--secondary-color);
}

.message-management-card .admin-response p {
    border-left-color: var(--success-color);
    background: rgba(22, 163, 74, 0.1);
}

.response-time {
    display: block;
    margin-top: 0.75rem;
    font-size: 0.8rem;
    color: var(--text-muted);
    font-style: italic;
}

.message-management-card .message-actions {
    padding: 2rem;
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(0, 0, 0, 0.05) 100%);
    border-top: 1px solid var(--bg-glass-border);
}

.response-form .form-group {
    margin-bottom: 2rem;
}

.response-form label {
    display: block;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.response-form textarea {
    width: 100%;
    min-height: 120px;
    padding: 1rem 1.25rem;
    border: 2px solid var(--bg-glass-border);
    border-radius: 12px;
    background: var(--bg-glass);
    color: var(--text-primary);
    font-size: 1rem;
    resize: vertical;
    transition: all 0.3s ease;
    font-family: inherit;
    line-height: 1.6;
}

.response-form textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

.response-form textarea::placeholder {
    color: var(--text-muted);
    opacity: 0.8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .admin-dashboard-header {
        padding: 2rem 1.5rem;
    }

    .admin-welcome-section h1 {
        font-size: 2.2rem;
    }

    .admin-subtitle {
        font-size: 1.1rem;
    }

    .admin-quick-stats {
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-top: 2rem;
    }

    .quick-stat-card {
        padding: 1.25rem;
    }

    .stat-number {
        font-size: 1.8rem;
    }

    .messages-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .message-management-card {
        border-radius: 16px;
    }

    .message-management-card .message-header,
    .message-management-card .message-content,
    .message-management-card .message-actions {
        padding: 1.5rem;
    }

    .section-header {
        padding: 1.5rem;
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .section-header h2 {
        font-size: 1.5rem;
    }
}
