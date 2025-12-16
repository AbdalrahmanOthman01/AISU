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

<!-- Admin Page Header -->
<div class="admin-page-header">
    <div class="page-title-section">
        <h1><i class="fas fa-users-cog"></i> Manage Admin Accounts</h1>
        <p class="page-subtitle">Create and manage administrator accounts for the system</p>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Create New Admin Card -->
    <div class="admin-card">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3>Create New Admin</h3>
        </div>
        <div class="card-content">
            <?php if ($admin_error): ?>
                <div class="alert alert-danger admin-alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($admin_error) ?>
                </div>
            <?php endif; ?>
            <?php if ($admin_feedback): ?>
                <div class="alert alert-success admin-alert">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($admin_feedback) ?>
                </div>
            <?php endif; ?>

            <form action="process_admin_action.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <!-- Profile Image Upload -->
                <div class="form-group">
                    <label for="admin_profile_image">Profile Image (Optional)</label>
                    <div class="image-upload-section">
                        <div class="image-preview-container">
                            <img src="../assets/images/default-avatar.png" alt="Profile Preview" id="imagePreview" class="image-preview">
                            <div class="image-overlay">
                                <i class="fas fa-camera"></i>
                                <span>Click to upload</span>
                            </div>
                        </div>
                        <input type="file" id="admin_profile_image" name="admin_profile_image" accept="image/*" style="display: none;">
                        <div class="upload-controls">
                            <label for="admin_profile_image" class="upload-btn">
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

                <div class="form-group">
                    <label for="admin_name">Admin Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="admin_name" name="admin_name" placeholder="Enter admin name (e.g., John)" required>
                        <span class="input-hint">Username will be: AISU_John</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="admin_password" name="admin_password" placeholder="Choose a secure password" required>
                        <span class="input-hint">Minimum 8 characters recommended</span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="action" value="create_admin" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        Create Admin Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Admins Card -->
    <div class="admin-card">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <h3>Existing Administrators</h3>
            <span class="card-badge"><?= count($admins) ?> total</span>
        </div>
        <div class="card-content">
            <?php if (count($admins) > 0): ?>
                <div class="admins-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Username</th>
                                <th><i class="fas fa-calendar"></i> Created</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($admins as $admin): ?>
                            <tr class="<?= $admin['username'] === 'AISU' ? 'main-admin-row' : '' ?>">
                                <td>
                                    <div class="admin-info">
                                        <div class="admin-avatar">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                        <div class="admin-details">
                                            <span class="admin-username"><?= htmlspecialchars($admin['username']) ?></span>
                                            <?php if ($admin['username'] === 'AISU'): ?>
                                                <span class="admin-role-badge main-admin">Main Administrator</span>
                                            <?php else: ?>
                                                <span class="admin-role-badge">Administrator</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('M j, Y', strtotime($admin['created_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($admin['username'] !== 'AISU'): ?>
                                        <form action="process_admin_action.php" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this admin account? This action cannot be undone.')">
                                           <input type="hidden" name="user_id" value="<?= $admin['id'] ?>">
                                           <button type="submit" name="action" value="delete_admin" class="btn btn-danger btn-sm" title="Delete Admin">
                                               <i class="fas fa-trash-alt"></i>
                                               Delete
                                           </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="protected-account">
                                            <i class="fas fa-shield-alt"></i>
                                            Protected
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h4>No Admin Accounts</h4>
                    <p>Create your first administrator account using the form above.</p>
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

.admin-form input {
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

.admin-form input:focus {
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

/* Admins Table Styles */
.admins-table-container {
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

.admin-table tbody tr.main-admin-row {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.05) 0%, rgba(34, 197, 94, 0.02) 100%);
    border-left: 4px solid var(--success-color);
}

.admin-table tbody td {
    padding: 1.25rem 1rem;
    vertical-align: middle;
}

/* Admin Info Styles */
.admin-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.admin-avatar {
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
}

.admin-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.admin-username {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 1rem;
}

.admin-role-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.admin-role-badge.main-admin {
    background: linear-gradient(135deg, var(--success-color), #22c55e);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
}

/* Admin Date Styles */
.admin-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.admin-date i {
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

/* Protected Account Styles */
.protected-account {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.05) 100%);
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-radius: 20px;
    color: var(--success-color);
    font-size: 0.8rem;
    font-weight: 600;
}

.protected-account i {
    font-size: 0.9rem;
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

/* Inline Form */
.inline-form {
    display: inline;
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

    .admins-table-container {
        overflow-x: auto;
    }

    .admin-table {
        min-width: 400px;
    }

    .admin-info {
        gap: 0.75rem;
    }

    .admin-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .admin-username {
        font-size: 0.9rem;
    }

    .admin-role-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.6rem;
    }
}
</style>

<script>
// Image Upload Preview Functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('admin_profile_image');
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
