<?php
$pageTitle = 'Manage Students';
require_once 'partials/header.php';

// Fetch all students
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_feedback = $_SESSION['user_feedback'] ?? null;
$user_error = $_SESSION['user_error'] ?? null;
unset($_SESSION['user_feedback'], $_SESSION['user_error']);
?>

<!-- Student Page Header -->
<div class="admin-page-header">
    <div class="page-title-section">
        <h1><i class="fas fa-graduation-cap"></i> Manage Student Accounts</h1>
        <p class="page-subtitle">Create and manage student accounts for the system</p>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Create New Student Card -->
    <div class="admin-card">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3>Create New Student</h3>
        </div>
        <div class="card-content">
            <?php if ($user_error): ?>
                <div class="alert alert-danger admin-alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($user_error) ?>
                </div>
            <?php endif; ?>
            <?php if ($user_feedback): ?>
                <div class="alert alert-success admin-alert">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($user_feedback) ?>
                </div>
            <?php endif; ?>

            <form action="process_admin_action.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <!-- Profile Image Upload -->
                <div class="form-group">
                    <label for="student_profile_image">Profile Image (Optional)</label>
                    <div class="image-upload-section">
                        <div class="image-preview-container">
                            <img src="../assets/images/default-avatar.png" alt="Profile Preview" id="imagePreview" class="image-preview">
                            <div class="image-overlay">
                                <i class="fas fa-camera"></i>
                                <span>Click to upload</span>
                            </div>
                        </div>
                        <input type="file" id="student_profile_image" name="student_profile_image" accept="image/*" style="display: none;">
                        <div class="upload-controls">
                            <label for="student_profile_image" class="upload-btn">
                                <i class="fas fa-upload"></i>
                                Choose Image
                            </label>
                            <button type="button" class="remove-image-btn" id="removeImageBtn" style="display: none;">
                                <i class="fas fa-times"></i>
                                Remove
                            </button>
                        </div>
                        <span class="input-hint">JPG, PNG, GIF up to 500KB. Square images work best.</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="first_name" name="first_name" placeholder="Enter first name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="last_name" name="last_name" placeholder="Enter last name" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="level">Academic Level</label>
                        <div class="input-wrapper">
                            <i class="fas fa-layer-group input-icon"></i>
                            <select id="level" name="level" required>
                                <option value="" disabled selected>Select academic level</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                                <option value="4">Level 4</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="student_password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="student_password" name="student_password" placeholder="Choose a secure password" required>
                        <span class="input-hint">Minimum 6 characters recommended</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="action" value="create_student" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        Create Student Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Students Card -->
    <div class="admin-card">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>Enrolled Students</h3>
            <span class="card-badge"><?= count($students) ?> total</span>
        </div>
        <div class="card-content">
            <?php if (count($students) > 0): ?>
                <div class="students-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Student</th>
                                <th><i class="fas fa-phone"></i> Phone</th>
                                <th><i class="fas fa-layer-group"></i> Level</th>
                                <th><i class="fas fa-calendar"></i> Joined</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $student): ?>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            <?php if (!empty($student['profile_image'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($student['profile_image']) ?>" alt="Profile" class="avatar-image">
                                            <?php else: ?>
                                                <i class="fas fa-user-graduate"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="student-details">
                                            <span class="student-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></span>
                                            <span class="student-username">@<?= htmlspecialchars($student['username']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="student-phone">
                                        <i class="fas fa-phone"></i>
                                        <?= htmlspecialchars($student['phone_number']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="level-badge level-<?= $student['level'] ?>">
                                        Level <?= htmlspecialchars($student['level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="join-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('M j, Y', strtotime($student['created_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form action="process_admin_action.php" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this student account? This action cannot be undone.')">
                                           <input type="hidden" name="user_id" value="<?= $student['id'] ?>">
                                           <input type="hidden" name="phone_number" value="<?= $student['phone_number'] ?>">
                                           <button type="submit" name="action" value="delete_user" class="btn btn-danger btn-sm" title="Delete Student">
                                               <i class="fas fa-trash-alt"></i>
                                               Delete
                                           </button>
                                        </form>
                                        <form action="process_admin_action.php" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to block this phone number and delete the student? The number cannot be used to register again.')">
                                           <input type="hidden" name="user_id" value="<?= $student['id'] ?>">
                                           <input type="hidden" name="phone_number" value="<?= $student['phone_number'] ?>">
                                           <button type="submit" name="action" value="block_user" class="btn btn-warning btn-sm" title="Block Number">
                                               <i class="fas fa-ban"></i>
                                               Block
                                           </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>No Students Enrolled</h4>
                    <p>Use the form above to create the first student account.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Admin Page Header */
.admin-page-header {
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
}

.admin-page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--bg-glass) 0%, var(--bg-glass) 100%);
    border-radius: 24px;
}

.page-title-section h1 {
    font-size: 2.8rem;
    font-weight: 900;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
    font-family: 'Poppins', sans-serif;
}

.page-title-section h1 i {
    color: var(--secondary-color);
    margin-right: 1rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.page-subtitle {
    font-size: 1.3rem;
    color: var(--text-secondary);
    margin: 0;
    font-weight: 500;
    position: relative;
    z-index: 1;
    font-family: 'Poppins', sans-serif;
}

/* Enhanced Admin Cards */
.admin-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 2px solid var(--bg-glass-border);
    overflow: hidden;
    box-shadow: var(--shadow-glass);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.admin-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(31, 38, 135, 0.4);
    border-color: rgba(99, 102, 241, 0.3);
}

.admin-card-header {
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(99, 102, 241, 0.05) 100%);
    border-bottom: 1px solid var(--bg-glass-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.admin-card-header .card-icon {
    width: 48px;
    height: 48px;
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

.admin-card-header h3 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--text-primary);
    flex: 1;
    margin-left: 1rem;
    font-family: 'Poppins', sans-serif;
}

.card-badge {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.card-content {
    padding: 2rem;
}

/* Admin Form Styles */
.admin-form .form-group {
    margin-bottom: 2rem;
}

.admin-form label {
    display: block;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 1rem;
    z-index: 2;
}

.admin-form input,
.admin-form select {
    width: 100%;
    padding: 1rem 1.25rem 1rem 3rem;
    border: 2px solid var(--bg-glass-border);
    border-radius: 12px;
    background: var(--bg-glass);
    color: var(--text-primary);
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
    position: relative;
}

.admin-form input:focus,
.admin-form select:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
    transform: translateY(-1px);
}

.admin-form input::placeholder {
    color: var(--text-muted);
    opacity: 0.8;
}

.admin-form select {
    color: var(--text-primary);
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13.2-5.4H18.6c-5%200-9.3%201.8-13.2%205.4A17.6%2017.6%200%200%200%200%2082.6c0%204.8%201.8%209.3%205.4%2013.2l128%20128c3.9%203.9%208.4%205.4%2013.2%205.4s9.3-1.8%2013.2-5.4l128-128c3.9-3.9%205.4-8.4%205.4-13.2%200-4.8-1.8-9.3-5.4-13.2z%22%2F%3E%3C%2Fsvg%3E');
    background-repeat: no-repeat;
    background-position: right 15px top 50%;
    background-size: 12px auto;
}

.input-hint {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
    font-style: italic;
}

/* Image Upload Section */
.image-upload-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.image-preview-container {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--bg-glass-border);
    box-shadow: var(--shadow-medium);
    cursor: pointer;
    transition: all 0.3s ease;
}

.image-preview-container:hover {
    border-color: var(--primary-color);
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

.image-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    color: white;
    font-size: 0.9rem;
    text-align: center;
}

.image-preview-container:hover .image-overlay {
    opacity: 1;
}

.image-overlay i {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.upload-controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.upload-btn:hover {
    background: linear-gradient(135deg, var(--primary-hover), var(--secondary-hover));
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

.remove-image-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, var(--error-color), #dc2626);
    color: white;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    border: none;
}

.remove-image-btn:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

.form-actions {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--bg-glass-border);
}

.form-actions .btn {
    width: 100%;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 700;
}

/* Students Table Styles */
.students-table-container {
    overflow-x: auto;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.1);
    border: 1px solid var(--bg-glass-border);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    color: var(--text-primary);
    font-family: 'Poppins', sans-serif;
}

.admin-table thead th {
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(99, 102, 241, 0.1) 100%);
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 700;
    color: var(--text-primary);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--bg-glass-border);
}

.admin-table thead th i {
    margin-right: 0.5rem;
    color: var(--primary-color);
}

.admin-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid var(--bg-glass-border);
}

.admin-table tbody tr:hover {
    background: rgba(99, 102, 241, 0.02);
}

.admin-table tbody tr:last-child {
    border-bottom: none;
}

.admin-table tbody td {
    padding: 1.25rem 1rem;
    vertical-align: middle;
}

/* Student Info Styles */
.student-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.student-avatar {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-medium);
    overflow: hidden;
}

.student-avatar .avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.student-name {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 1rem;
}

.student-username {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* Level Badge */
.level-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.level-badge.level-1 {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
}

.level-badge.level-2 {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.level-badge.level-3 {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.level-badge.level-4 {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
}

/* Contact Info Styles */
.student-phone {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.student-phone i {
    color: var(--primary-color);
}

.join-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.join-date i {
    color: var(--primary-color);
}

/* Action Buttons */
.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    border-radius: 8px;
}

.btn-danger {
    background: linear-gradient(135deg, var(--error-color), #dc2626);
    color: white;
    border: 1px solid rgba(239, 68, 68, 0.3);
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.btn-danger:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, var(--warning-color), #d97706);
    color: white;
    border: 1px solid rgba(245, 158, 11, 0.3);
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

.btn-warning:hover {
    background: linear-gradient(135deg, #d97706, #b45309);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.inline-form {
    display: inline;
}

/* Alert Styles */
.admin-alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.admin-alert i {
    font-size: 1.2rem;
    flex-shrink: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: rgba(255, 255, 255, 0.7);
}

.empty-icon {
    font-size: 4rem;
    color: rgba(255, 255, 255, 0.3);
    margin-bottom: 1.5rem;
}

.empty-state h4 {
    font-size: 1.3rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    margin: 0 0 0.5rem 0;
}

.empty-state p {
    font-size: 1rem;
    margin: 0;
    line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-page-header {
        padding: 2rem 1.5rem;
    }

    .page-title-section h1 {
        font-size: 2.2rem;
    }

    .page-subtitle {
        font-size: 1.1rem;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .admin-card-header {
        padding: 1.25rem 1.5rem;
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .admin-card-header h3 {
        margin-left: 0;
        font-size: 1.2rem;
    }

    .card-content {
        padding: 1.5rem;
    }

    .students-table-container {
        overflow-x: auto;
    }

    .admin-table {
        min-width: 600px;
    }

    .student-info {
        gap: 0.75rem;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .student-name {
        font-size: 0.9rem;
    }

    .level-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
    }

    .action-buttons {
        flex-direction: row;
        justify-content: center;
    }
}
</style>

<script>
// Image Upload Preview Functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('student_profile_image');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewContainer = document.querySelector('.image-preview-container');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const defaultAvatarSrc = '../assets/images/default-avatar.png';

    // Handle file selection
    imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, or GIF).');
                return;
            }

            // Validate file size (500KB max)
            if (file.size > 512000) {
                alert('Image size must be less than 500KB.');
                return;
            }

            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                removeImageBtn.style.display = 'inline-flex';
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle click on preview container to trigger file input
    imagePreviewContainer.addEventListener('click', function() {
        imageInput.click();
    });

    // Handle remove image
    removeImageBtn.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();

        // Reset file input
        imageInput.value = '';

        // Reset preview to default
        imagePreview.src = defaultAvatarSrc;

        // Hide remove button
        removeImageBtn.style.display = 'none';
    });
});
</script>

<?php require_once 'partials/footer.php'; ?>
