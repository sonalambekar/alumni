<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: /alumni/login.php');
    exit();
}

$currentUser = getCurrentUser();
$isAdmin = isset($currentUser['is_admin']) && $currentUser['is_admin'] == 1;

// Handle job submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_job'])) {
    try {
        // Check if user is admin
        $isAdmin = isset($currentUser['is_admin']) && $currentUser['is_admin'] == 1;

        // Prepare the SQL query based on user role
        if ($isAdmin) {
            // For admin, directly insert as approved
            $stmt = $pdo->prepare("INSERT INTO jobs (title, company, description, requirements, location, job_type, experience_level, salary_min, apply_link, posted_by, is_approved, posted_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->execute([
                $_POST['title'],
                $_POST['company'],
                $_POST['description'],
                $_POST['requirements'] ?? '',
                $_POST['location'],
                $_POST['job_type'],
                $_POST['experience_level'],
                $_POST['salary_min'] ?? null,
                $_POST['apply_link'],
                $currentUser['id']
            ]);
            $success = "Job posted and approved successfully!";
        } else {
            // For regular users, insert as pending approval
            $stmt = $pdo->prepare("INSERT INTO jobs (title, company, description, requirements, location, job_type, experience_level, salary_min, apply_link, posted_by, is_approved, posted_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
            $stmt->execute([
                $_POST['title'],
                $_POST['company'],
                $_POST['description'],
                $_POST['requirements'] ?? '',
                $_POST['location'],
                $_POST['job_type'],
                $_POST['experience_level'],
                $_POST['salary_min'] ?? null,
                $_POST['apply_link'],
                $currentUser['id']
            ]);
            $success = "Job posted successfully! It will be visible after admin approval.";
        }
        // No need for this else block as we've handled both cases above

        // Redirect to prevent form resubmission
        header('Location: jobs.php?success=1');
        exit();
    } catch (PDOException $e) {
        $error = "Error posting job: " . $e->getMessage();
        error_log('Job Posting Error: ' . $e->getMessage());
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success = "Job posted successfully!";
}

// Fetch jobs with filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$jobType = isset($_GET['job_type']) ? trim($_GET['job_type']) : '';
$experience = isset($_GET['experience']) ? trim($_GET['experience']) : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($location)) {
    $where[] = "j.location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($jobType)) {
    $where[] = "j.job_type = ?";
    $params[] = $jobType;
    error_log("Job Type Filter: " . $jobType); // Debug log
}

if (!empty($experience)) {
    $where[] = "j.experience_level = ?";
    $params[] = $experience;
}

$sql = "SELECT j.*, u.name as posted_by_name 
        FROM jobs j 
        LEFT JOIN users u ON j.posted_by = u.id 
        WHERE j.is_active = 1 AND j.is_approved = 1";

// Debug log the SQL query and parameters
error_log("SQL Query: " . $sql);
error_log("Parameters: " . print_r($params, true));

if (!empty($where)) {
    $sql .= " AND " . implode(" AND ", $where);
}

$sql .= " ORDER BY j.posted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Board - GM Alumni Network</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --border-radius: 12px;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
        }

        body.modal-open {
            overflow: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: var(--white);
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Search Container */
        .search-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin: -50px auto 40px;
            max-width: 1200px;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 10;
        }

        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .search-input-wrapper {
            flex: 1;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 14px 20px 14px 50px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .search-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 14px 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .search-btn:hover {
            background-color: #4a1919;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 31, 31, 0.3);
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            background-color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .reset-btn {
            background-color: var(--bg-light);
            color: var(--text-color);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .reset-btn:hover {
            background-color: #e5e7eb;
        }

        /* Jobs Grid */
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 24px;
            padding: 0 20px 60px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .job-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
        }

        .job-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--secondary-color);
        }

        .job-card-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .company-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            color: white;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 16px;
        }

        .job-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .company-name {
            font-size: 1.05rem;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 16px;
        }

        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background-color: var(--bg-light);
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--text-color);
        }

        .meta-badge i {
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .job-card-body {
            padding: 24px;
            flex-grow: 1;
        }

        .job-description {
            color: var(--text-color);
            line-height: 1.7;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .requirements-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .requirements {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .job-card-footer {
            padding: 16px 24px;
            background-color: var(--bg-light);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .posted-info {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .posted-info i {
            margin-right: 4px;
        }

        .apply-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .apply-btn:hover {
            background-color: #4a1919;
            transform: translateX(2px);
        }

        .apply-btn i {
            font-size: 0.85rem;
        }

        .no-jobs {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .no-jobs i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 24px;
            opacity: 0.5;
        }

        .no-jobs h3 {
            color: var(--text-color);
            margin-bottom: 12px;
            font-size: 1.5rem;
        }

        .no-jobs p {
            color: var(--text-light);
            max-width: 500px;
            margin: 0 auto;
        }

        /* Floating Button */
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 64px;
            height: 64px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 6px 20px rgba(91, 31, 31, 0.4);
            cursor: pointer;
            z-index: 999;
            transition: var(--transition);
            border: none;
        }

        .floating-btn:hover {
            background-color: #4a1919;
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 10px 30px rgba(91, 31, 31, 0.5);
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 9998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.show {
            display: block;
            opacity: 1;
        }

        .modal-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            overflow-y: auto;
            padding: 20px;
        }

        .modal-container.show {
            display: block;
        }

        .modal-content {
            background-color: white;
            margin: 40px auto;
            max-width: 650px;
            border-radius: var(--border-radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal-container.show .modal-content {
            transform: translateY(0);
            opacity: 1;
        }

        .modal-header {
            padding: 24px 30px;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: var(--transition);
            line-height: 1;
        }

        .close-modal:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: 30px;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 12px 28px;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #4a1919;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--bg-light);
            color: var(--text-color);
            border: 2px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .alert {
            padding: 16px 20px;
            margin: 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .jobs-grid {
                grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .search-container {
                padding: 20px;
                margin: -30px 10px 30px;
            }

            .search-bar {
                flex-direction: column;
            }

            .jobs-grid {
                grid-template-columns: 1fr;
                padding: 0 10px 60px;
                gap: 20px;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .floating-btn {
                width: 56px;
                height: 56px;
                bottom: 20px;
                right: 20px;
            }

            .modal-content {
                margin: 20px auto;
            }

            .modal-body {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                padding: 40px 15px;
            }

            .job-card-header,
            .job-card-body {
                padding: 20px;
            }

            .job-card-footer {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .apply-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1><i class="fas fa-briefcase"></i> Job Opportunities</h1>
                <p>Discover career opportunities and connect with fellow alumni</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="container">
            <form class="search-container" method="GET" action="jobs.php">
                <div class="search-bar">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by job title, company, or keywords..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>

                <div class="filters">
                    <select class="filter-select" name="location">
                        <option value="">📍 All Locations</option>
                        <option value="Bengaluru" <?php echo $location === 'Bengaluru' ? 'selected' : ''; ?>>Bengaluru
                        </option>
                        <option value="Mumbai" <?php echo $location === 'Mumbai' ? 'selected' : ''; ?>>Mumbai</option>
                        <option value="Delhi" <?php echo $location === 'Delhi' ? 'selected' : ''; ?>>Delhi</option>
                        <option value="Hyderabad" <?php echo $location === 'Hyderabad' ? 'selected' : ''; ?>>Hyderabad
                        </option>
                        <option value="Pune" <?php echo $location === 'Pune' ? 'selected' : ''; ?>>Pune</option>
                        <option value="Remote" <?php echo $location === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                    </select>

                    <select class="filter-select" name="job_type">
                        <option value="">💼 All Job Types</option>
                        <option value="Full-time" <?php echo $jobType === 'Full-time' ? 'selected' : ''; ?>>Full-time
                        </option>
                        <option value="Part-time" <?php echo $jobType === 'Part-time' ? 'selected' : ''; ?>>Part-time
                        </option>
                        <option value="Contract" <?php echo $jobType === 'Contract' ? 'selected' : ''; ?>>Contract
                        </option>
                        <option value="Internship" <?php echo $jobType === 'Internship' ? 'selected' : ''; ?>>Internship
                        </option>
                    </select>

                    <select class="filter-select" name="experience">
                        <option value="">📊 Experience Level</option>
                        <option value="Entry Level" <?php echo $experience === 'Entry Level' ? 'selected' : ''; ?>>Entry
                            Level</option>
                        <option value="Mid Level" <?php echo $experience === 'Mid Level' ? 'selected' : ''; ?>>Mid Level
                        </option>
                        <option value="Senior Level" <?php echo $experience === 'Senior Level' ? 'selected' : ''; ?>>
                            Senior Level</option>
                        <option value="Executive" <?php echo $experience === 'Executive' ? 'selected' : ''; ?>>Executive
                        </option>
                    </select>

                    <a href="jobs.php" class="reset-btn">
                        <i class="fas fa-undo"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Jobs Grid -->
        <div class="jobs-grid">
            <?php if (empty($jobs)): ?>
                <div class="no-jobs">
                    <i class="fas fa-briefcase"></i>
                    <h3>No Jobs Available</h3>
                    <p>There are currently no job postings matching your criteria. Try adjusting your filters or check back
                        later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <div class="company-badge">
                                <?php echo strtoupper(substr($job['company'], 0, 1)); ?>
                            </div>
                            <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <div class="company-name"><?php echo htmlspecialchars($job['company']); ?></div>

                            <div class="job-meta">
                                <?php if (!empty($job['location'])): ?>
                                    <span class="meta-badge">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($job['location']); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($job['job_type'])): ?>
                                    <span class="meta-badge">
                                        <i class="fas fa-briefcase"></i>
                                        <?php echo htmlspecialchars($job['job_type']); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($job['experience_level'])): ?>
                                    <span class="meta-badge">
                                        <i class="fas fa-chart-line"></i>
                                        <?php echo htmlspecialchars($job['experience_level']); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($job['salary_min'])): ?>
                                    <span class="meta-badge">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <?php echo 'From ' . htmlspecialchars($job['salary_min']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="job-card-body">
                            <?php if (!empty($job['description'])): ?>
                                <p class="job-description"><?php echo htmlspecialchars($job['description']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($job['requirements'])): ?>
                                <div class="requirements-label">Requirements:</div>
                                <div class="requirements"><?php echo nl2br(htmlspecialchars($job['requirements'])); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="job-card-footer">
                            <div class="posted-info">
                                <i class="far fa-clock"></i>
                                <?php
                                if (!empty($job['posted_at'])) {
                                    $postedDate = new DateTime($job['posted_at']);
                                    $now = new DateTime();
                                    $diff = $now->diff($postedDate);

                                    if ($diff->days == 0) {
                                        echo 'Today';
                                    } elseif ($diff->days == 1) {
                                        echo 'Yesterday';
                                    } elseif ($diff->days < 7) {
                                        echo $diff->days . ' days ago';
                                    } else {
                                        echo $postedDate->format('M j, Y');
                                    }
                                } else {
                                    echo 'Recently';
                                }
                                ?>
                            </div>

                            <?php if (!empty($job['apply_link'])): ?>
                                <a href="<?php echo htmlspecialchars($job['apply_link']); ?>" class="apply-btn" target="_blank">
                                    Apply Now <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <button class="apply-btn" disabled style="opacity: 0.6; cursor: not-allowed;">
                                    No Link Available
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Floating Action Button -->
        <button type="button" class="floating-btn" onclick="openModal()" title="Post a Job">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Modal Overlay -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>

    <!-- Modal Container -->
    <div class="modal-container" id="modalContainer">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Post a New Job</h2>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="title">Job Title *</label>
                        <input type="text" id="title" name="title" class="form-control"
                            placeholder="e.g., Senior Software Engineer" required>
                    </div>

                    <div class="form-group">
                        <label for="company">Company Name *</label>
                        <input type="text" id="company" name="company" class="form-control"
                            placeholder="e.g., Tech Corp Inc." required>
                    </div>

                    <div class="form-group">
                        <label for="description">Job Description *</label>
                        <textarea id="description" name="description" class="form-control"
                            placeholder="Describe the role, responsibilities, and what makes this opportunity exciting..."
                            required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="requirements">Requirements</label>
                        <textarea id="requirements" name="requirements" class="form-control"
                            placeholder="List the key requirements and qualifications (one per line)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" class="form-control"
                            placeholder="e.g., Bengaluru, India or Remote" required>
                    </div>

                    <div class="form-group">
                        <label for="job_type">Job Type *</label>
                        <select id="job_type" name="job_type" class="form-control" required>
                            <option value="">Select Job Type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                            <option value="Temporary">Temporary</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="experience_level">Experience Level *</label>
                        <select id="experience_level" name="experience_level" class="form-control" required>
                            <option value="">Select Experience Level</option>
                            <option value="Entry Level">Entry Level (0-2 years)</option>
                            <option value="Mid Level">Mid Level (3-5 years)</option>
                            <option value="Senior Level">Senior Level (5+ years)</option>
                            <option value="Executive">Executive/Leadership</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="salary_min">Minimum Salary</label>
                        <input type="text" id="salary_min" name="salary_min" class="form-control"
                            placeholder="e.g., ₹8LPA or $60,000">
                    </div>

                    <div class="form-group">
                        <label for="apply_link">Application Link *</label>
                        <input type="url" id="apply_link" name="apply_link" class="form-control"
                            placeholder="https://example.com/careers/apply" required>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">
                            Cancel
                        </button>
                        <button type="submit" name="submit_job" class="btn btn-primary">
                            <i class="fas fa-check"></i> Post Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Simple modal functions
        function openModal() {
            const overlay = document.getElementById('modalOverlay');
            const container = document.getElementById('modalContainer');

            if (overlay && container) {
                document.body.classList.add('modal-open');
                overlay.classList.add('show');
                container.classList.add('show');
            }
        }

        function closeModal() {
            const overlay = document.getElementById('modalOverlay');
            const container = document.getElementById('modalContainer');

            if (overlay && container) {
                document.body.classList.remove('modal-open');
                overlay.classList.remove('show');
                container.classList.remove('show');

                // Reset form
                const form = container.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        }

        function validateForm() {
            const requiredFields = document.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalid = null;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#f87171';
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                } else {
                    field.style.borderColor = '#e5e7eb';
                }
            });

            if (!isValid && firstInvalid) {
                firstInvalid.focus();
                alert('Please fill in all required fields marked with *');
                return false;
            }

            return true;
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });

            // Auto-resize textareas
            const textareas = document.querySelectorAll('textarea.form-control');
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });
        });
    </script>
</body>

</html>