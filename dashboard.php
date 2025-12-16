<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Protect page BEFORE any output
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

$pageTitle = 'My Dashboard - AISU Inquiry';
require_once 'includes/header.php';

// Fetch user's messages
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user data for modal
$stmt = $pdo->prepare("SELECT first_name, last_name, phone_number, level, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure user_data is not null
if (!$user_data) {
    // Redirect if user data not found
    header('Location: login.php');
    exit();
}

// Count messages
$total_messages = count($my_messages);
$responded_messages = count(array_filter($my_messages, function($msg) { return !empty($msg['admin_response']); }));
$pending_messages = $total_messages - $responded_messages;
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="welcome-section">
        <h1>Welcome back, <span class="user-name"><?= htmlspecialchars($_SESSION['full_name']) ?></span>!</h1>
        <p class="dashboard-subtitle">Manage your inquiries and track responses from our team.</p>
        <button id="editProfileBtn" class="btn btn-secondary" style="margin-top: 1rem;">
            <i class="fas fa-edit"></i>
            Edit Profile
        </button>
    </div>
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?= $total_messages ?></span>
                <span class="stat-label">Total Inquiries</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?= $pending_messages ?></span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?= $responded_messages ?></span>
                <span class="stat-label">Resolved</span>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Submit New Inquiry Card -->
    <div class="dashboard-card submit-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <h3>Submit New Inquiry</h3>
        </div>
        <div class="card-content">
            <p class="card-description">Have a question or need assistance? Send us your inquiry and we'll get back to you promptly.</p>
            <form action="process_message.php" method="POST" class="inquiry-form">
                <div class="form-group">
                    <label for="message_text">Your Message</label>
                    <textarea id="message_text" name="message_text" placeholder="Type your question or message here..." required></textarea>
                </div>
                <button type="submit" name="submit_message" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Send Inquiry
                </button>
            </form>
        </div>
    </div>

    <!-- Message History Card -->
    <div class="dashboard-card history-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-history"></i>
            </div>
            <h3>Message History</h3>
        </div>
        <div class="card-content">
            <?php if (count($my_messages) > 0): ?>
                <div class="messages-list">
                    <?php foreach($my_messages as $msg): ?>
                        <div class="message-item">
                            <div class="message-header">
                                <div class="message-meta">
                                    <span class="message-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('M j, Y \a\t g:i A', strtotime($msg['created_at'])) ?>
                                    </span>
                                    <span class="message-status <?= $msg['admin_response'] ? 'status-resolved' : 'status-pending' ?>">
                                        <i class="fas fa-<?= $msg['admin_response'] ? 'check-circle' : 'clock' ?>"></i>
                                        <?= $msg['admin_response'] ? 'Resolved' : 'Pending' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="message-content">
                                <div class="user-message">
                                    <div class="message-label">Your Inquiry:</div>
                                    <div class="message-text"><?= htmlspecialchars($msg['message_text']) ?></div>
                                </div>

                                <?php if ($msg['admin_response']): ?>
                                    <div class="admin-response">
                                        <div class="response-header">
                                            <div class="response-label">
                                                <i class="fas fa-reply"></i>
                                                AISU Response
                                            </div>
                                            <span class="response-date">
                                                <?= date('M j, Y \a\t g:i A', strtotime($msg['responded_at'])) ?>
                                            </span>
                                        </div>
                                        <div class="response-text"><?= htmlspecialchars($msg['admin_response']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="pending-notice">
                                        <i class="fas fa-info-circle"></i>
                                        Your inquiry is being reviewed. We'll respond as soon as possible.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4>No inquiries yet</h4>
                    <p>You haven't submitted any inquiries. Use the form to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Edit Profile</h2>
            <button class="modal-close" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-scroll-container">
                <div id="modalMessage" style="display: none;"></div>
                <div class="profile-image-preview">
                    <img src="uploads/<?= htmlspecialchars($user_data['profile_image'] ?? 'default.png') ?>" alt="Profile Image" id="modalProfileImage">
                    <input type="file" id="modal_profile_image" name="profile_image" accept="image/*" style="display: none;">
                    <label for="modal_profile_image" class="btn">
                        <i class="fas fa-camera"></i>
                        Change Image
                    </label>
                </div>

                <form id="editProfileForm">
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="modal_first_name">First Name</label>
                                <input type="text" id="modal_first_name" name="first_name" value="<?= htmlspecialchars($user_data['first_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="modal_last_name">Last Name</label>
                                <input type="text" id="modal_last_name" name="last_name" value="<?= htmlspecialchars($user_data['last_name']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="modal_phone_number">Phone Number</label>
                            <input type="text" id="modal_phone_number" name="phone_number" value="<?= htmlspecialchars($user_data['phone_number']) ?>" required>
                        </div>

                        <?php if ($_SESSION['role'] === 'student'): ?>
                        <div class="form-group">
                            <label for="modal_level">Academic Level</label>
                            <select id="modal_level" name="level">
                                <option value="1" <?= ($_SESSION['level'] == 1) ? 'selected' : '' ?>>Level 1</option>
                                <option value="2" <?= ($_SESSION['level'] == 2) ? 'selected' : '' ?>>Level 2</option>
                                <option value="3" <?= ($_SESSION['level'] == 3) ? 'selected' : '' ?>>Level 3</option>
                                <option value="4" <?= ($_SESSION['level'] == 4) ? 'selected' : '' ?>>Level 4</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-section">
                        <h3>Security Settings</h3>
                        <div class="form-group">
                            <label for="modal_current_password">Current Password</label>
                            <input type="password" id="modal_current_password" name="current_password" placeholder="Enter current password to change">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="modal_new_password">New Password</label>
                                <input type="password" id="modal_new_password" name="new_password" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label for="modal_confirm_new_password">Confirm New Password</label>
                                <input type="password" id="modal_confirm_new_password" name="confirm_new_password" placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="cancelEdit">Cancel</button>
            <button class="btn btn-primary" id="saveProfile">
                <i class="fas fa-save"></i>
                Save Changes
            </button>
        </div>
    </div>
</div>

<script>
// Modal functionality
const editProfileBtn = document.getElementById('editProfileBtn');
const editProfileModal = document.getElementById('editProfileModal');
const modalClose = document.querySelector('.modal-close');
const cancelEdit = document.getElementById('cancelEdit');
const saveProfile = document.getElementById('saveProfile');
const editProfileForm = document.getElementById('editProfileForm');
const modalMessage = document.getElementById('modalMessage');

// Open modal
editProfileBtn.addEventListener('click', () => {
    editProfileModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
});

// Close modal functions
function closeModal() {
    editProfileModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = 'auto';
    modalMessage.style.display = 'none';
    modalMessage.innerHTML = '';
}

modalClose.addEventListener('click', closeModal);
cancelEdit.addEventListener('click', closeModal);

// Close modal when clicking outside
editProfileModal.addEventListener('click', (e) => {
    if (e.target === editProfileModal) {
        closeModal();
    }
});

// Profile image preview
document.getElementById('modal_profile_image').addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('modalProfileImage').src = URL.createObjectURL(file);
    }
});

// Form submission
saveProfile.addEventListener('click', async () => {
    const formData = new FormData(editProfileForm);
    formData.append('profile_image_file', document.getElementById('modal_profile_image').files[0] || null);

    try {
        const response = await fetch('process_profile_update.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        modalMessage.style.display = 'block';
        if (result.success) {
            modalMessage.className = 'alert alert-success';
            modalMessage.innerHTML = result.message;

            // Update session data if needed
            if (result.updated_fields) {
                if (result.updated_fields.full_name) {
                    document.querySelector('.user-name').textContent = result.updated_fields.full_name;
                }
                if (result.updated_fields.profile_image) {
                    document.getElementById('modalProfileImage').src = 'uploads/' + result.updated_fields.profile_image;
                }
            }

            // Close modal after success
            setTimeout(() => {
                closeModal();
                // Optionally reload the page to reflect changes
                window.location.reload();
            }, 2000);
        } else {
            modalMessage.className = 'alert alert-danger';
            modalMessage.innerHTML = result.message;
        }
    } catch (error) {
        modalMessage.style.display = 'block';
        modalMessage.className = 'alert alert-danger';
        modalMessage.innerHTML = 'An error occurred. Please try again.';
        console.error('Error:', error);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
