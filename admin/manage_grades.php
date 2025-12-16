<?php
$pageTitle = 'Manage Grades - AISU Admin';
require_once 'partials/header.php';

// Fetch existing grade announcements (with error handling for missing tables)
try {
    $stmt = $pdo->query("
        SELECT ga.*, u.first_name, u.last_name
        FROM grades_announcements ga
        JOIN users u ON ga.uploaded_by = u.id
        ORDER BY ga.created_at DESC
    ");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tables don't exist yet
    $announcements = [];
    if (strpos($e->getMessage(), 'grades_announcements') !== false) {
        $error = "Database tables not found. Please run the following SQL commands in phpMyAdmin or MySQL console:

CREATE TABLE IF NOT EXISTS `grades_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `level` int(1) NOT NULL,
  `search_column` varchar(100) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `grades_announcements_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `grades_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `announcement_id` int(11) NOT NULL,
  `row_data` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `announcement_id` (`announcement_id`),
  CONSTRAINT `grades_data_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `grades_announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
}

$feedback = $_SESSION['grades_feedback'] ?? null;
$error = $_SESSION['grades_error'] ?? null;
unset($_SESSION['grades_feedback'], $_SESSION['grades_error']);
?>

<!-- Page Header -->
<div class="admin-page-header">
    <div class="page-title-section">
        <div class="page-icon">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="page-text">
            <h1>Manage Grades</h1>
            <p class="page-subtitle">Upload grade announcements and manage student access</p>
        </div>
    </div>
    <div class="page-stats">
        <div class="stat-item">
            <div class="stat-number" id="totalAnnouncements"><?= count($announcements) ?></div>
            <div class="stat-label">Total Announcements</div>
        </div>
        <div class="stat-item">
            <div class="stat-number" id="activeAnnouncements"><?= count(array_filter($announcements, fn($a) => $a['is_active'])) ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Upload Grades Card -->
    <div class="admin-card">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-upload"></i>
            </div>
            <h3>Upload Grade Announcement</h3>
        </div>
        <div class="card-content">
            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($feedback): ?>
                <div class="alert alert-success admin-alert">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($feedback) ?>
                </div>
            <?php endif; ?>

            <form action="process_admin_action.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <!-- Announcement Details -->
                <div class="form-group">
                    <label for="announcement_title">Announcement Title</label>
                    <div class="input-wrapper">
                        <i class="fas fa-heading input-icon"></i>
                        <input type="text" id="announcement_title" name="announcement_title"
                               placeholder="e.g., Mid-term Exam Results - Level 1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="announcement_description">Description (Optional)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-align-left input-icon"></i>
                        <textarea id="announcement_description" name="announcement_description"
                                  placeholder="Additional information about this grade announcement..."
                                  rows="3"></textarea>
                    </div>
                </div>

                <!-- Configuration -->
                <div class="form-group">
                    <label for="grade_level">Academic Level</label>
                    <div class="input-wrapper">
                        <i class="fas fa-layer-group input-icon"></i>
                        <select id="grade_level" name="grade_level" required>
                            <option value="" disabled selected>Select level</option>
                            <option value="1">Level 1</option>
                            <option value="2">Level 2</option>
                            <option value="3">Level 3</option>
                            <option value="4">Level 4</option>
                        </select>
                    </div>
                    <span class="input-hint">Students from this level can only access grades from this file</span>
                </div>

                <div class="form-group">
                    <label for="search_column">Search Column Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-search input-icon"></i>
                        <input type="text" id="search_column" name="search_column"
                               placeholder="e.g., ID, StudentID, RollNo" required>
                    </div>
                    <span class="input-hint">The column name students will use to search their records (case sensitive)</span>
                </div>

                <div class="form-group">
                    <div class="csv-format-info">
                        <h5><i class="fas fa-file-csv"></i> CSV File Format</h5>
                        <div class="format-example">
                            <p><strong>First row (Headers):</strong></p>
                            <code>ID,Name,Subject1,Subject2,Subject3,...</code>
                            <p><strong>Example data rows:</strong></p>
                            <code>1,John Doe,18/20,15/20,19/20</code><br>
                            <code>2,Jane Smith,20/20,17/20,16/20</code>
                        </div>
                        <div class="format-notes">
                            <strong>Notes:</strong>
                            <ul>
                                <li>Include a column for student identifiers (e.g., ID, StudentID, RollNo)</li>
                                <li>Specify the search column name in the "Search Column Name" field above</li>
                                <li>Students will search using values from the specified column</li>
                                <li>Remaining columns can be subject scores, names, etc. (e.g., "18/20", "95/100")</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="form-group">
                    <label for="grades_file">Upload Grades File</label>
                    <div class="file-upload-section">
                        <div class="file-upload-area" id="fileUploadArea">
                            <div class="file-upload-icon">
                                <i class="fas fa-file-csv"></i>
                            </div>
                            <div class="file-upload-text">
                                <span class="primary-text">Drop CSV file here or click to browse</span>
                                <span class="secondary-text">Supports CSV format (Excel files coming soon)</span>
                            </div>
                            <input type="file" id="grades_file" name="grades_file" accept=".csv" style="display: none;" required>
                        </div>
                        <div class="file-info" id="fileInfo" style="display: none;">
                            <div class="file-details">
                                <i class="fas fa-file-csv"></i>
                                <span id="fileName"></span>
                                <span id="fileSize"></span>
                            </div>
                            <button type="button" class="remove-file-btn" id="removeFileBtn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <span class="input-hint">CSV file with headers. First row should contain column names.</span>
                </div>

                <div class="form-actions">
                    <button type="submit" name="action" value="upload_grades" class="btn btn-primary">
                        <i class="fas fa-upload"></i>
                        Upload & Publish Grades
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Announcements Card -->
    <div class="admin-card">
        <div class="card-header admin-card-header">
            <div class="card-icon">
                <i class="fas fa-list"></i>
            </div>
            <h3>Published Announcements</h3>
            <span class="card-badge"><?= count($announcements) ?> total</span>
        </div>
        <div class="card-content">
            <?php if (count($announcements) > 0): ?>
                <div class="announcements-list">
                    <?php foreach($announcements as $announcement): ?>
                        <div class="announcement-item">
                            <div class="announcement-header">
                                <div class="announcement-info">
                                    <h4 class="announcement-title"><?= htmlspecialchars($announcement['title']) ?></h4>
                                    <div class="announcement-meta">
                                        <span class="level-badge level-<?= $announcement['level'] ?>">
                                            Level <?= $announcement['level'] ?>
                                        </span>
                                        <span class="search-info">
                                            <i class="fas fa-search"></i>
                                            Search by: <?= htmlspecialchars($announcement['search_column']) ?>
                                        </span>
                                        <span class="upload-date">
                                            <i class="fas fa-calendar"></i>
                                            <?= date('M j, Y', strtotime($announcement['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="announcement-status">
                                    <?php if ($announcement['is_active']): ?>
                                        <span class="status-active">
                                            <i class="fas fa-check-circle"></i>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="status-inactive">
                                            <i class="fas fa-pause-circle"></i>
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($announcement['description']): ?>
                                <div class="announcement-description">
                                    <?= htmlspecialchars($announcement['description']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="announcement-actions">
                                <form action="process_admin_action.php" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this announcement? This will remove all associated grade data.')">
                                    <input type="hidden" name="announcement_id" value="<?= $announcement['id'] ?>">
                                    <button type="submit" name="action" value="delete_announcement" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                        Delete
                                    </button>
                                </form>

                                <?php if ($announcement['is_active']): ?>
                                    <form action="process_admin_action.php" method="POST" class="inline-form">
                                        <input type="hidden" name="announcement_id" value="<?= $announcement['id'] ?>">
                                        <button type="submit" name="action" value="toggle_announcement" class="btn btn-warning btn-sm">
                                            <i class="fas fa-pause"></i>
                                            Deactivate
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="process_admin_action.php" method="POST" class="inline-form">
                                        <input type="hidden" name="announcement_id" value="<?= $announcement['id'] ?>">
                                        <button type="submit" name="action" value="toggle_announcement" class="btn btn-success btn-sm">
                                            <i class="fas fa-play"></i>
                                            Activate
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>No Grade Announcements</h4>
                    <p>Use the form above to upload your first grade announcement.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Admin Page Header */
.admin-page-header {
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(99, 102, 241, 0.1) 100%);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--bg-glass-border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-glass);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 2rem;
}

.page-title-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 1;
    min-width: 300px;
}

.page-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

.page-text h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    margin: 0;
    font-size: 1rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.page-stats {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    min-width: 120px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 900;
    color: var(--primary-color);
    display: block;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* File Upload Section */
.file-upload-section {
    margin-top: 1rem;
}

.file-upload-area {
    border: 2px dashed var(--bg-glass-border);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    background: var(--bg-glass);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.file-upload-area::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
    transition: left 0.5s;
}

.file-upload-area:hover::before {
    left: 100%;
}

.file-upload-area:hover {
    border-color: var(--primary-color);
    background: rgba(99, 102, 241, 0.05);
    transform: translateY(-2px);
}

.file-upload-area.dragover {
    border-color: var(--success-color);
    background: rgba(34, 197, 94, 0.05);
}

.file-upload-icon {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
}

.file-upload-text {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.primary-text {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.secondary-text {
    font-size: 0.9rem;
    color: var(--text-muted);
}

.file-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: rgba(99, 102, 241, 0.1);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 8px;
    margin-top: 1rem;
}

.file-details {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-primary);
}

.file-details i {
    color: var(--success-color);
    font-size: 1.2rem;
}

.remove-file-btn {
    background: linear-gradient(135deg, var(--error-color), #dc2626);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-file-btn:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: scale(1.1);
}

/* CSV Format Info */
.csv-format-info {
    background: rgba(99, 102, 241, 0.05);
    border: 2px solid rgba(99, 102, 241, 0.2);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1rem;
}

.csv-format-info h5 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.csv-format-info h5 i {
    color: var(--primary-color);
}

.format-example {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.format-example p {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.format-example code {
    display: block;
    background: rgba(0, 0, 0, 0.3);
    color: var(--text-primary);
    padding: 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.format-notes {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-radius: 8px;
    padding: 1rem;
}

.format-notes strong {
    color: var(--success-color);
    display: block;
    margin-bottom: 0.5rem;
}

.format-notes ul {
    margin: 0;
    padding-left: 1.5rem;
}

.format-notes li {
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.4;
}

.format-notes li:last-child {
    margin-bottom: 0;
}

/* Announcements List */
.announcements-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.announcement-item {
    background: var(--bg-glass);
    border-radius: 12px;
    border: 1px solid var(--bg-glass-border);
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.announcement-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    border-color: var(--primary-color);
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.announcement-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.announcement-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
}

.search-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.search-info i {
    color: var(--primary-color);
}

.upload-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-muted);
}

.upload-date i {
    color: var(--text-muted);
}

.announcement-status .status-active {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(34, 197, 94, 0.2);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 20px;
    color: var(--success-color);
    font-size: 0.85rem;
    font-weight: 600;
}

.announcement-status .status-inactive {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 20px;
    color: #d97706;
    font-size: 0.85rem;
    font-weight: 600;
}

.announcement-description {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 1rem;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    border-left: 3px solid var(--primary-color);
}

.announcement-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    flex-wrap: wrap;
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-page-header {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }

    .page-title-section {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .page-text h1 {
        font-size: 1.5rem;
    }

    .page-stats {
        justify-content: center;
        gap: 1rem;
    }

    .stat-item {
        min-width: 100px;
        padding: 0.75rem;
    }

    .stat-number {
        font-size: 1.5rem;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .announcement-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .announcement-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .announcement-actions {
        justify-content: center;
    }

    .file-upload-area {
        padding: 1.5rem;
    }

    .file-upload-icon {
        font-size: 2.5rem;
    }

    .primary-text {
        font-size: 1rem;
    }

    .secondary-text {
        font-size: 0.8rem;
    }
}
</style>

<script>
// File Upload Functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('grades_file');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFileBtn = document.getElementById('removeFileBtn');

    // Handle click on upload area
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Handle drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        fileUploadArea.classList.add('dragover');
    }

    function unhighlight(e) {
        fileUploadArea.classList.remove('dragover');
    }

    fileUploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect({ target: { files: files } });
        }
    }

    // Handle file selection
    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect(event) {
        const file = event.target.files[0];

        if (file) {
            // Validate file type
            if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
                alert('Please select a CSV file.');
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5242880) {
                alert('File size must be less than 5MB.');
                return;
            }

            // Update UI
            fileName.textContent = file.name;
            fileSize.textContent = `(${(file.size / 1024).toFixed(1)} KB)`;
            fileUploadArea.style.display = 'none';
            fileInfo.style.display = 'flex';
        }
    }

    // Handle file removal
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        fileUploadArea.style.display = 'block';
        fileInfo.style.display = 'none';
    });
});
</script>

<?php require_once 'partials/footer.php'; ?>
