<?php
// Admin Jobs Management
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Replace the problematic SQL query section (around line 36-47) with this fixed version:

if ($action === 'add') {
    // Add new job
    $title = $_POST['title'] ?? '';
    $company = $_POST['company'] ?? '';
    $description = $_POST['description'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $location = $_POST['location'] ?? '';
    $job_type = $_POST['job_type'] ?? 'full-time';
    $experience_level = $_POST['experience_level'] ?? 'entry';
    $salary_min = $_POST['salary_min'] ?? '';
    $application_deadline = !empty($_POST['application_deadline']) ? $_POST['application_deadline'] : null;
    $application_link = $_POST['application_link'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';

    if (!empty($title) && !empty($company) && !empty($description)) {
        try {
            // FIXED: Changed apply_link to application_url to match the database schema
            $sql = "
                INSERT INTO jobs (
                    title, company, description, requirements, location, 
                    job_type, experience_level, salary_min, 
                    application_deadline, application_link, contact_email, 
                    posted_by, is_approved, posted_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ";
            $stmt = $pdo->prepare($sql);
            
            $params = [
                $title, 
                $company, 
                $description, 
                $requirements, 
                $location, 
                ucwords(str_replace('-', ' ', $job_type)), 
                ucwords(str_replace('_', ' ', $experience_level)), 
                $salary_min, 
                $application_deadline, 
                $application_link, // Now correctly matches the column name
                $contact_email, 
                $_SESSION['user_id']
            ];
            
            $result = $stmt->execute($params);
            
            if ($result) {
                $success = "Job posting added successfully!";
                // Clear form
                $_POST = [];
            } else {
                $error = "Failed to add job posting";
                $debug = print_r($stmt->errorInfo(), true);
            }
        } catch(PDOException $e) {
            $error = "Error adding job posting: " . $e->getMessage();
            $debug = "SQL: " . $sql . "\nParams: " . print_r($params, true);
        }
    } else {
        $error = "Please fill in all required fields";
    }
} elseif ($action === 'approve_job') {
            // Approve job
            $jobId = $_POST['job_id'] ?? 0;
            try {
                $stmt = $pdo->prepare("UPDATE jobs SET is_approved = 1 WHERE id = ?");
                $stmt->execute([$jobId]);
                $success = "Job approved successfully!";
            } catch(PDOException $e) {
                $error = "Error approving job: " . $e->getMessage();
            }
        } elseif ($action === 'reject_job') {
            // Reject job
            $jobId = $_POST['job_id'] ?? 0;
            try {
                $stmt = $pdo->prepare("UPDATE jobs SET is_approved = 0 WHERE id = ?");
                $stmt->execute([$jobId]);
                $success = "Job rejected successfully!";
            } catch(PDOException $e) {
                $error = "Error rejecting job: " . $e->getMessage();
            }
        } elseif ($action === 'edit') {
            // Update job
            $id = $_POST['job_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $company = $_POST['company'] ?? '';
            $description = $_POST['description'] ?? '';
            $requirements = $_POST['requirements'] ?? '';
            $location = $_POST['location'] ?? '';
            $job_type = $_POST['job_type'] ?? 'full-time';
            $experience_level = $_POST['experience_level'] ?? 'entry';
            $salary_min = $_POST['salary_min'] ?? '';
            $application_deadline = !empty($_POST['application_deadline']) ? $_POST['application_deadline'] : null;
            $application_link = $_POST['application_link'] ?? '';
            $contact_email = $_POST['contact_email'] ?? '';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($title) && !empty($company) && !empty($description) && !empty($id)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE jobs
                        SET title = ?, company = ?, description = ?, requirements = ?, location = ?, job_type = ?, experience_level = ?, salary_min = ?, application_deadline = ?, application_link = ?, contact_email = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $company, $description, $requirements, $location, $job_type, $experience_level, $salary_min, $application_deadline, $application_link, $contact_email, $is_active, $id]);

                    $success = "Job posting updated successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating job posting: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'delete') {
            // Delete job
            $id = $_POST['job_id'] ?? 0;

            if (!empty($id)) {
                try {
                    $stmt = $pdo->delete("DELETE FROM jobs WHERE id = ?", [$id]);
                    $success = "Job posting deleted successfully!";
                } catch(PDOException $e) {
                    $error = "Error deleting job posting: " . $e->getMessage();
                }
            }
        }
    }
}

// Initialize variables
$jobs = [];
$success = '';
$error = '';

// Get all jobs
try {
    // Check if tables exist before querying
    $tablesExist = true;
    try {
        $pdo->query("SELECT 1 FROM jobs LIMIT 1");
        $pdo->query("SELECT 1 FROM users LIMIT 1");
    } catch(PDOException $e) {
        $tablesExist = false;
    }

    if ($tablesExist) {
        try {
            // Try to get author name, fallback to posted_by if full_name doesn't exist
            $stmt = $pdo->query("
                SELECT j.*,
                       COALESCE(u.full_name, u.name, CONCAT('User #', j.posted_by)) as author_name
                FROM jobs j
                LEFT JOIN users u ON j.posted_by = u.id
                ORDER BY j.created_at DESC
            ");
            $jobs = $stmt->fetchAll();
        } catch(PDOException $e) {
            // If the join fails, try without the author name
            $stmt = $pdo->query("
                SELECT j.*, CONCAT('User #', j.posted_by) as author_name
                FROM jobs j
                ORDER BY j.created_at DESC
            ");
            $jobs = $stmt->fetchAll();
        }
    } else {
        $error = "Database tables not found. Please run the database setup first.";
    }
} catch(PDOException $e) {
    $error = "Error loading jobs: " . $e->getMessage();
}

// Fetch pending jobs for approval
$pendingJobs = [];
$approvedJobs = [];
if ($tablesExist) {
    $pendingJobs = $pdo->query("SELECT j.*, u.name as posted_by_name FROM jobs j LEFT JOIN users u ON j.posted_by = u.id WHERE j.is_approved = 0 ORDER BY j.posted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $approvedJobs = $pdo->query("SELECT j.*, u.name as posted_by_name FROM jobs j LEFT JOIN users u ON j.posted_by = u.id WHERE j.is_approved = 1 ORDER BY j.posted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .admin-header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .admin-nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .admin-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .back-btn {
            background: var(--text-light);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #4b5563;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
        }

        .add-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .add-btn:hover {
            background: #4a1919;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .form-container h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #4a1919;
        }

        .btn-secondary {
            background: var(--text-light);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-approve, .btn-reject {
            padding: 6px 12px;
            font-size: 12px;
            background: var(--text-light);
            color: var(--white);
        }

        .btn-approve:hover, .btn-reject:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #dc2626;
            color: var(--white);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .jobs-table {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .table-header {
            background: var(--bg-light);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-color);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .type-full-time {
            background: #d1fae5;
            color: #065f46;
        }

        .type-part-time {
            background: #fef3c7;
            color: #d97706;
        }

        .type-contract {
            background: #bfdbfe;
            color: #1d4ed8;
        }

        .type-internship {
            background: #ddd6fe;
            color: #7c3aed;
        }

        .type-freelance {
            background: #fed7d7;
            color: #991b1b;
        }

        .level-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .level-entry {
            background: #e0f2fe;
            color: #0369a1;
        }

        .level-mid {
            background: #fef3c7;
            color: #d97706;
        }

        .level-senior {
            background: #fed7d7;
            color: #991b1b;
        }

        .level-executive {
            background: #ddd6fe;
            color: #7c3aed;
        }

        .actions-cell {
            white-space: nowrap;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">Jobs Management</div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">

        <!-- Add Job Form -->
        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Add New Job Posting</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="title">Job Title *</label>
                        <input type="text" id="title" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company">Company *</label>
                        <input type="text" id="company" name="company" class="form-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="job_type">Job Type</label>
                        <select id="job_type" name="job_type" class="form-select">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                            <option value="freelance">Freelance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="experience_level">Experience Level</label>
                        <select id="experience_level" name="experience_level" class="form-select">
                            <option value="entry">Entry Level</option>
                            <option value="mid">Mid Level</option>
                            <option value="senior">Senior Level</option>
                            <option value="executive">Executive</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-input" placeholder="City, State or Remote">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="salary_min">Salary Range</label>
                        <input type="text" id="salary_min" name="salary_min" class="form-input" placeholder="e.g., $50,000 - $70,000">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">Job Description *</label>
                    <textarea id="description" name="description" class="form-textarea" rows="4" required></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" class="form-textarea" rows="3" placeholder="List the job requirements, skills, and qualifications..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="application_deadline">Application Deadline</label>
                        <input type="date" id="application_deadline" name="application_deadline" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="application_link">Application URL</label>
                        <input type="url" id="application_link" name="application_link" class="form-input" placeholder="https://company.com/careers/job">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact_email">Contact Email</label>
                    <input type="email" id="contact_email" name="contact_email" class="form-input" placeholder="hr@company.com">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Post Job
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Jobs List -->
        <!-- Pending Approvals Section -->
        <div class="jobs-table">
            <div class="table-header">
                <h3 class="table-title"><i class="fas fa-clock"></i> Pending Approvals</h3>
            </div>
            <div class="table-container">
                <?php if (empty($pendingJobs)): ?>
                    <div class="no-data">No pending job approvals.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Posted By</th>
                                <th>Posted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingJobs as $job): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['company']); ?></td>
                                    <td><?php echo htmlspecialchars($job['location']); ?></td>
                                    <td><?php echo htmlspecialchars($job['posted_by_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($job['posted_at'])); ?></td>
                                    <td class="actions">
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="action" value="approve_job">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" class="btn btn-approve">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline-block; margin-left: 5px;">
                                            <input type="hidden" name="action" value="reject_job">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this job posting?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approved Jobs Section -->
        <div class="jobs-table" style="margin-top: 30px;">
            <div class="table-header">
                <h3 class="table-title"><i class="fas fa-check-circle"></i> Approved Jobs</h3>
            </div>
            <div class="table-container">
                <?php if (empty($approvedJobs)): ?>
                    <div class="no-data">No approved jobs found.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Posted By</th>
                                <th>Posted On</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedJobs as $job): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['company']); ?></td>
                                    <td><?php echo htmlspecialchars($job['location']); ?></td>
                                    <td><?php echo htmlspecialchars($job['posted_by_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($job['posted_at'])); ?></td>
                                    <td>
                                        <span class="status-badge status-active">Approved</span>
                                    </td>
                                    <td class="actions">
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="action" value="reject_job">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this job posting?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function editJob(jobId) {
            // Find the job data and populate the modal
            const jobs = <?php echo json_encode($jobs); ?>;
            const job = jobs.find(j => j.id == jobId);

            if (job) {
                document.getElementById('edit_job_id').value = job.id;
                document.getElementById('edit_title').value = job.title || '';
                document.getElementById('edit_company').value = job.company || '';
                document.getElementById('edit_job_type').value = job.job_type || 'full-time';
                document.getElementById('edit_experience_level').value = job.experience_level || 'entry';
                document.getElementById('edit_location').value = job.location || '';
                document.getElementById('edit_salary_min').value = job.salary_min || '';
                document.getElementById('edit_description').value = job.description || '';
                document.getElementById('edit_requirements').value = job.requirements || '';
                document.getElementById('edit_application_deadline').value = job.application_deadline || '';
                document.getElementById('edit_application_link').value = job.application_link || '';
                document.getElementById('edit_contact_email').value = job.contact_email || '';
                document.getElementById('edit_is_active').checked = (job.is_active == 1);

                document.getElementById('editModal').classList.add('show');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
    </script>
</body>
</html>
