<?php
$pageTitle = 'View Grades - AISU';
require_once 'includes/header.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

$student_level = $_SESSION['level'] ?? null;
$search_query = trim($_GET['search'] ?? '');
$announcement_id = (int)($_GET['announcement'] ?? 0);

// Debug: Check student level
echo "<!-- Student Level: " . $student_level . " (Type: " . gettype($student_level) . ") -->";

// Fetch available grade announcements for this student's level (with error handling)
try {
    $stmt = $pdo->prepare("
        SELECT ga.*, u.first_name, u.last_name
        FROM grades_announcements ga
        JOIN users u ON ga.uploaded_by = u.id
        WHERE ga.level = ? AND ga.is_active = 1
        ORDER BY ga.created_at DESC
    ");
    $stmt->execute([$student_level]);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Check if announcements are found
    echo "<!-- Found " . count($announcements) . " announcements -->";
    if (count($announcements) > 0) {
        foreach ($announcements as $ann) {
            echo "<!-- Announcement: " . $ann['title'] . " (Level: " . $ann['level'] . ") -->";
        }
    }

} catch (PDOException $e) {
    // Tables don't exist yet
    $announcements = [];
    $system_message = "Grade system is not yet configured. Please contact your administrator.";
    echo "<!-- Database Error: " . $e->getMessage() . " -->";
}

// Get selected announcement details
$selected_announcement = null;
$student_grades = null;

if ($announcement_id > 0) {
    foreach ($announcements as $ann) {
        if ($ann['id'] == $announcement_id) {
            $selected_announcement = $ann;
            break;
        }
    }

    // Search for student's grades if search query provided
    if ($selected_announcement && !empty($search_query)) {
        $stmt = $pdo->prepare("
            SELECT row_data
            FROM grades_data
            WHERE announcement_id = ? AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(row_data, CONCAT('$.', ?)))) LIKE LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$announcement_id, $selected_announcement['search_column'], '%' . $search_query . '%']);
        $result = $stmt->fetch();

        if ($result) {
            $student_grades = json_decode($result['row_data'], true);
        }
    }
}
?>

<!-- Grades Page Header -->
<div class="dashboard-header">
    <div class="welcome-section">
        <h1><i class="fas fa-graduation-cap"></i> My Grades</h1>
        <p class="dashboard-subtitle">View your academic results and grade announcements for Level <?= $student_level ?></p>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Announcements List -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-list"></i>
            </div>
            <h3>Available Grade Announcements</h3>
        </div>
        <div class="card-content">
            <?php if (count($announcements) > 0): ?>
                <div class="announcements-list">
                    <?php foreach($announcements as $announcement): ?>
                        <div class="announcement-item <?= $announcement_id == $announcement['id'] ? 'active' : '' ?>">
                            <div class="announcement-header">
                                <h4 class="announcement-title"><?= htmlspecialchars($announcement['title']) ?></h4>
                                <div class="announcement-meta">
                                    <span class="upload-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('M j, Y', strtotime($announcement['created_at'])) ?>
                                    </span>
                                    <span class="uploaded-by">
                                        <i class="fas fa-user"></i>
                                        By <?= htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']) ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($announcement['description']): ?>
                                <div class="announcement-description">
                                    <?= htmlspecialchars($announcement['description']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="announcement-actions">
                                <form action="grades.php" method="GET" class="grade-search-form-inline">
                                    <input type="hidden" name="announcement" value="<?= $announcement['id'] ?>">
                                    <div class="search-input-wrapper">
                                        <input type="text" name="search" placeholder="Enter your <?= htmlspecialchars($announcement['search_column']) ?>" required>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search"></i>
                                            Search
                                        </button>
                                    </div>
                                </form>
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
                    <p>Grade announcements for your level will appear here when available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <h3>How to View Your Grades</h3>
        </div>
        <div class="card-content">
            <div class="instructions-content">
                <div class="instruction-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Choose a Grade Announcement</h4>
                        <p>Click on any grade announcement from the list on the left to view your grades.</p>
                    </div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Enter Your Identifier</h4>
                        <p>Enter your identifier (such as ID, StudentID, etc.) in the search box below each announcement.</p>
                    </div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>View Your Results</h4>
                        <p>Your grades will be displayed immediately with pass/fail indicators.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <?php if ($student_grades): ?>
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-table"></i>
                </div>
                <h3>Your Grades - <?= htmlspecialchars($selected_announcement['title']) ?></h3>
            </div>
            <div class="card-content">
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    Grades found for <?= htmlspecialchars($selected_announcement['search_column']) ?>: <?= htmlspecialchars($search_query) ?>
                </div>

                <div class="full-data-section">
                    <h4>All Data</h4>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($student_grades) as $header): ?>
                                        <th><?= htmlspecialchars($header) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($student_grades as $key => $value): ?>
                                        <?php
                                        $isGrade = $key !== $selected_announcement['search_column'] && $key !== 'Name' && !is_nan((float)$value);
                                        $isPassing = $isGrade ? (strpos($value, '/') !== false ?
                                            ((float)explode('/', $value)[0] / (float)explode('/', $value)[1] >= 0.5) :
                                            ((float)$value >= 50)) : false;
                                        $cellClass = $isGrade ? ($isPassing ? 'passing' : 'failing') : '';
                                        ?>
                                        <td class="<?= $cellClass ?>"><?= htmlspecialchars($value) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Announcements List */
.announcements-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.announcement-item {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.25rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.announcement-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    border-color: var(--primary-color);
}

.announcement-item.active {
    background: rgba(99, 102, 241, 0.1);
    border-color: var(--primary-color);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
}

.announcement-title {
    margin: 0 0 0.75rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.announcement-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.upload-date,
.uploaded-by {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.upload-date i,
.uploaded-by i {
    color: var(--primary-color);
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
    margin-top: 1rem;
}

/* Inline Search Form */
.grade-search-form-inline {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.search-input-wrapper {
    display: flex;
    align-items: center;
    flex: 1;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.search-input-wrapper:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--primary-color);
}

.search-input-wrapper input {
    flex: 1;
    border: none;
    background: transparent;
    color: var(--text-primary);
    font-size: 0.9rem;
    padding: 0.5rem;
    outline: none;
}

.search-input-wrapper input::placeholder {
    color: var(--text-muted);
}

.search-input-wrapper .btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    border-radius: 6px;
}

/* Grade Search Form */
.grade-search-form {
    margin: 2rem 0;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.search-input-group label {
    display: block;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 1rem;
    font-size: 1rem;
}

.search-input-group .input-wrapper {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.search-input-group input {
    flex: 1;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid var(--bg-glass-border);
    border-radius: 8px;
    background: var(--bg-glass);
    color: var(--text-primary);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

.search-btn {
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.search-btn:hover {
    background: linear-gradient(135deg, var(--primary-hover), var(--secondary-hover));
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

/* Search Controls */
.search-controls {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
}

.announcement-select-wrapper {
    width: 100%;
}

.announcement-select-wrapper label {
    display: block;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.announcement-select-wrapper select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid var(--bg-glass-border);
    border-radius: 8px;
    background: var(--bg-glass);
    color: var(--text-primary);
    font-size: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.announcement-select-wrapper select:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

.announcement-select-wrapper select option {
    background: var(--bg-primary);
    color: var(--text-primary);
    padding: 0.5rem;
}

/* Selected Announcement Info */
.selected-announcement-info {
    margin-top: 2rem;
    padding: 2rem;
    background: rgba(99, 102, 241, 0.05);
    border-radius: 12px;
    border: 2px solid rgba(99, 102, 241, 0.2);
}

.announcement-details h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.announcement-details h4 i {
    color: var(--primary-color);
}

.announcement-meta-info {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.meta-item i {
    color: var(--primary-color);
    width: 16px;
}

.meta-item strong {
    color: var(--text-primary);
}

/* Search Results */
.search-results {
    margin-top: 2rem;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.search-results h4 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 700;
}

/* Success/Warning Messages */
.success-message,
.warning-message {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.success-message {
    background: rgba(34, 197, 94, 0.2);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: var(--success-color);
}

.warning-message {
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: #d97706;
}

.success-message i,
.warning-message i {
    font-size: 1.2rem;
}

/* Full Data Table */
.full-data-section {
    margin-bottom: 2rem;
}

.full-data-section h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 700;
}

.data-table-container {
    overflow-x: auto;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: var(--shadow-medium);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    color: var(--text-primary);
    font-size: 0.9rem;
}

.data-table thead {
    background: rgba(99, 102, 241, 0.1);
}

.data-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 700;
    color: var(--primary-color);
    border-bottom: 2px solid rgba(99, 102, 241, 0.2);
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    vertical-align: middle;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table td.passing {
    background: rgba(34, 197, 94, 0.1);
    color: var(--success-color);
    font-weight: 700;
}

.data-table td.failing {
    background: rgba(239, 68, 68, 0.1);
    color: var(--error-color);
    font-weight: 700;
}

/* Grades Table */
.grades-table-container {
    overflow-x: auto;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.grades-table {
    width: 100%;
    border-collapse: collapse;
    color: var(--text-primary);
}

.grades-table tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.grades-table tr:last-child {
    border-bottom: none;
}

.grades-table td {
    padding: 1rem;
    vertical-align: middle;
}

.grade-label {
    font-weight: 600;
    color: var(--text-secondary);
    min-width: 150px;
}

.grade-value {
    font-weight: 700;
    color: var(--text-primary);
}

.grade-score {
    font-size: 1.1rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.grade-score.passing {
    color: var(--success-color);
}

.grade-score.failing {
    color: var(--error-color);
}

.grade-score i {
    font-size: 0.9rem;
}

/* Grade Actions */
.grade-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

/* No Results */
.no-results ul {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.no-results li {
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}

/* Announcement Info */
.announcement-info {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.announcement-info h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 700;
}

.announcement-info p {
    margin: 0 0 0.5rem 0;
    color: var(--text-secondary);
}

.announcement-info .announcement-description {
    margin: 1rem 0 0 0;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.2);
    border-left: 3px solid var(--primary-color);
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .announcement-meta {
        flex-direction: column;
        gap: 0.5rem;
    }

    .grade-actions {
        flex-direction: column;
    }

    .search-controls {
        gap: 1rem;
    }

    .search-input-group .input-wrapper {
        flex-direction: column;
        gap: 0.5rem;
    }

    .search-input-group input {
        padding-left: 1rem;
    }

    .announcement-meta-info {
        flex-direction: column;
        gap: 0.75rem;
    }

    .selected-announcement-info {
        padding: 1.5rem;
    }

    .announcement-details h4 {
        font-size: 1.1rem;
    }

    .grades-table-container {
        overflow-x: auto;
    }

    .grades-table {
        min-width: 300px;
    }

    .grade-label {
        min-width: 120px;
    }

    .grade-search-form {
        padding: 1.5rem;
    }

    .search-results {
        padding: 1.5rem;
    }
}
</style>

<!-- Grade Search Modal -->
<div id="gradeModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle">View Grades</h3>
            <button type="button" class="modal-close" onclick="closeGradeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-content">
            <!-- Search Step -->
            <div id="searchStep" class="modal-step">
                <div class="search-prompt">
                    <div class="search-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h4>Enter Your Search Value</h4>
                    <p>Please enter your <span id="searchColumnLabel">identifier</span> to search for your grades.</p>

                    <form id="gradeSearchForm" onsubmit="searchGrades(event)">
                        <input type="hidden" id="modalAnnouncementId" name="announcement_id">
                        <div class="modal-input-group">
                            <label for="academicNumber" id="searchLabel">Search Value:</label>
                            <div class="input-wrapper">
                                <i class="fas fa-hashtag input-icon"></i>
                                <input type="text" id="academicNumber" name="academic_number"
                                       placeholder="Enter your search value" required>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeGradeModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Search Grades
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Step -->
            <div id="resultsStep" class="modal-step" style="display: none;">
                <div id="resultsContent">
                    <!-- Results will be loaded here via AJAX -->
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="backToSearch()">
                        <i class="fas fa-arrow-left"></i>
                        Search Again
                    </button>
                    <button type="button" class="btn btn-primary" onclick="closeGradeModal()">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                </div>
            </div>

            <!-- Loading Step -->
            <div id="loadingStep" class="modal-step" style="display: none;">
                <div class="loading-content">
                    <div class="loading-spinner">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </div>
                    <h4>Searching for your grades...</h4>
                    <p>Please wait while we retrieve your results.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Modal functionality
let currentAnnouncementId = null;
let currentAnnouncementTitle = null;
let currentSearchColumn = null;

function openGradeModal(announcementId, title, searchColumn) {
    currentAnnouncementId = announcementId;
    currentAnnouncementTitle = title;
    currentSearchColumn = searchColumn;

    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalAnnouncementId').value = announcementId;
    document.getElementById('academicNumber').value = '';

    // Update labels
    document.getElementById('searchColumnLabel').textContent = searchColumn.toLowerCase();
    document.getElementById('searchLabel').textContent = searchColumn + ':';

    // Show modal
    const modal = document.getElementById('gradeModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Show search step
    showModalStep('searchStep');
}

function closeGradeModal() {
    const modal = document.getElementById('gradeModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';

    // Reset modal state
    currentAnnouncementId = null;
    currentAnnouncementTitle = null;
    showModalStep('searchStep');
}

function showModalStep(stepId) {
    // Hide all steps
    document.querySelectorAll('.modal-step').forEach(step => {
        step.style.display = 'none';
    });

    // Show selected step
    document.getElementById(stepId).style.display = 'block';
}

function backToSearch() {
    showModalStep('searchStep');
}

async function searchGrades(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const academicNumber = formData.get('academic_number').trim();

    if (!academicNumber) {
        alert('Please enter your academic number.');
        return;
    }

    // Show loading
    showModalStep('loadingStep');

    try {
        const response = await fetch('process_grades_search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                announcement_id: currentAnnouncementId,
                academic_number: academicNumber
            })
        });

        const result = await response.json();

        if (result.success) {
            displayResults(result.data);
        } else {
            displayError(result.message);
        }
    } catch (error) {
        displayError('An error occurred while searching. Please try again.');
    }
}

function displayResults(gradesData) {
    const resultsContent = document.getElementById('resultsContent');

    let html = `
        <div class="results-header">
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                Grades found for ${currentSearchColumn}: ${gradesData[currentSearchColumn]}
            </div>
        </div>

        <div class="full-data-section">
            <h4>All Data</h4>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
    `;

    // Add table headers
    Object.keys(gradesData).forEach(header => {
        html += `<th>${header}</th>`;
    });

    html += `
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
    `;

    // Add table data
    Object.entries(gradesData).forEach(([key, value]) => {
        const isGrade = key !== currentSearchColumn && key !== 'Name' && !isNaN(parseFloat(value));
        const isPassing = isGrade ? isGradePassing(value) : false;
        const cellClass = isGrade ? (isPassing ? 'passing' : 'failing') : '';

        html += `<td class="${cellClass}">${value}</td>`;
    });

    html += `
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;

    resultsContent.innerHTML = html;
    showModalStep('resultsStep');
}

function displayError(message) {
    const resultsContent = document.getElementById('resultsContent');

    const html = `
        <div class="error-display">
            <div class="warning-message">
                <i class="fas fa-exclamation-triangle"></i>
                ${message}
            </div>
            <div class="error-suggestions">
                <p><strong>Possible solutions:</strong></p>
                <ul>
                    <li>Check that you entered your academic number correctly</li>
                    <li>Make sure you're searching in the right grade announcement</li>
                    <li>Contact your instructor if you believe this is an error</li>
                </ul>
            </div>
        </div>
    `;

    resultsContent.innerHTML = html;
    showModalStep('resultsStep');
}

function isGradePassing(score) {
    // Handle different score formats (18/20, 95/100, 85, etc.)
    if (typeof score === 'string' && score.includes('/')) {
        const parts = score.split('/');
        if (parts.length === 2) {
            const numerator = parseFloat(parts[0]);
            const denominator = parseFloat(parts[1]);
            if (!isNaN(numerator) && !isNaN(denominator) && denominator > 0) {
                return (numerator / denominator) >= 0.5; // 50% passing
            }
        }
    }

    // Handle percentage or numeric scores
    const numericScore = parseFloat(score);
    if (!isNaN(numericScore)) {
        return numericScore >= 50; // Assume 50 is passing for percentage/numeric
    }

    return false; // Default to failing if format not recognized
}

// Close modal when clicking outside
document.getElementById('gradeModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeGradeModal();
    }
});

// Event listeners for grade view buttons
document.querySelectorAll('.grade-view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const title = this.dataset.title;
        const column = this.dataset.column;
        openGradeModal(id, title, column);
    });
});

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && document.getElementById('gradeModal').style.display === 'flex') {
        closeGradeModal();
    }
});
</script>

<style>
/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.modal-overlay.hidden {
    display: none;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 2px solid var(--bg-glass-border);
    box-shadow: var(--shadow-glass);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem;
    border-bottom: 1px solid var(--bg-glass-border);
    background: linear-gradient(135deg, var(--bg-glass) 0%, rgba(99, 102, 241, 0.05) 100%);
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1.5rem;
    font-weight: 700;
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.modal-content {
    padding: 2rem;
    overflow-y: auto;
    max-height: calc(90vh - 140px);
}

.modal-step {
    min-height: 300px;
    display: flex;
    flex-direction: column;
}

/* Search Step */
.search-prompt {
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.search-icon {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.search-prompt h4 {
    color: var(--text-primary);
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.search-prompt p {
    color: var(--text-secondary);
    margin: 0 0 2rem 0;
    line-height: 1.6;
}

#gradeSearchForm {
    max-width: 400px;
    margin: 0 auto;
}

.modal-input-group {
    margin-bottom: 2rem;
    text-align: left;
}

.modal-input-group label {
    display: block;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.modal-input-group .input-wrapper {
    position: relative;
}

.modal-input-group .input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 1rem;
    z-index: 2;
}

.modal-input-group input {
    width: 100%;
    padding: 1rem 1.25rem 1rem 3rem;
    border: 2px solid var(--bg-glass-border);
    border-radius: 12px;
    background: var(--bg-glass);
    color: var(--text-primary);
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.modal-input-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

/* Results Step */
.results-header {
    margin-bottom: 2rem;
}

.student-info {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(99, 102, 241, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(99, 102, 241, 0.2);
}

.student-info h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 700;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.info-value {
    font-weight: 700;
    color: var(--text-primary);
}

.grades-section h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 700;
}

/* Loading Step */
.loading-content {
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.loading-spinner {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.loading-content h4 {
    color: var(--text-primary);
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.loading-content p {
    color: var(--text-secondary);
    margin: 0;
    line-height: 1.6;
}

/* Error Display */
.error-display {
    text-align: center;
}

.error-suggestions {
    margin-top: 1.5rem;
    text-align: left;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.error-suggestions ul {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}

.error-suggestions li {
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* Modal Actions */
.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--bg-glass-border);
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-container {
        width: 95%;
        margin: 1rem;
    }

    .modal-header {
        padding: 1.5rem;
    }

    .modal-header h3 {
        font-size: 1.3rem;
    }

    .modal-content {
        padding: 1.5rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .modal-actions {
        flex-direction: column;
    }

    .search-prompt h4 {
        font-size: 1.1rem;
    }

    .search-icon {
        font-size: 3rem;
    }
}

/* Instructions Styles */
.instructions-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.instruction-step {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.step-content h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 700;
}

.step-content p {
    margin: 0;
    color: var(--text-secondary);
    line-height: 1.6;
}

@media (max-width: 768px) {
    .instruction-step {
        gap: 0.75rem;
    }

    .step-number {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }

    .step-content h4 {
        font-size: 1rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
