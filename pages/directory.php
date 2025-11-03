<?php
session_start();
require_once '../includes/db_config.php';

// Require authentication
requireLogin();

// Debug: Check database connection
$db_connected = false;
$db_error = '';
$alumni = [];

try {
    // Test the database connection
    $pdo->query("SELECT 1");
    $db_connected = true;
    
    // First, check if the users table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (empty($tables)) {
        throw new Exception("The 'users' table does not exist in the database");
    }
    
    // Get all users with all fields
    $query = "SELECT * FROM users";
    $stmt = $pdo->query($query);
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process all users
    foreach ($all_users as $user) {
        // Map the user data to our expected format
        $alumni[] = [
            'id' => $user['id'] ?? null,
            'name' => $user['name'] ?? 'No Name',
            'email' => $user['email'] ?? ($user['email_id'] ?? 'No Email'),
            'photo' => $user['profile_pic'] ?? $user['profile_picture'] ?? null,
            'institute' => $user['institute'] ?? 'Not specified',
            'branch' => $user['branch'] ?? 'Not specified',
            'designation' => $user['current_job'] ?? ($user['designation'] ?? 'Not specified'),
            'year_of_graduation' => $user['graduation_year'] ?? ($user['batch'] ?? 'N/A')
        ];
    }
    
} catch (PDOException $e) {
    $db_error = "PDO Error: " . $e->getMessage();
    error_log($db_error);
} catch (Exception $e) {
    $db_error = "Error: " . $e->getMessage();
    error_log($db_error);
    $debug_error = $db_error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --primary-light: #7a2a2a;
            --primary-dark: #4a1818;
            --secondary-color: #ecc35c;
            --bg-light: #f5f5f5;
            --bg-white: #ffffff;
            --text-dark: #2c3e50;
            --text-medium: #5a6c7d;
            --text-light: #95a5a6;
            --border-color: #e0e0e0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Header */
        .directory-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 48px 24px;
            text-align: center;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Search and Filter Section */
        .controls-section {
            background: var(--bg-white);
            padding: 24px;
            margin: -32px 24px 24px;
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            position: relative;
            z-index: 10;
        }

        .controls-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 15px;
            background: var(--bg-white);
            transition: all 0.2s ease;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(236, 195, 92, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .filter-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-medium);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-white);
            font-size: 14px;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-select:hover {
            border-color: var(--text-medium);
        }

        .filter-select:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236, 195, 92, 0.1);
        }

        /* Results Section */
        .results-section {
            padding: 0 24px 60px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .results-header {
            margin-bottom: 24px;
            text-align: center;
        }

        .results-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .results-count {
            color: var(--text-medium);
            font-size: 14px;
        }

        /* Alumni Grid */
        .alumni-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        /* Alumni Card */
        .alumni-card {
            background: var(--bg-white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .alumni-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--secondary-color);
        }

        .card-header {
            text-align: center;
            padding: 28px 20px 20px;
            background: linear-gradient(to bottom, #fafafa, var(--bg-white));
            border-bottom: 1px solid var(--border-color);
        }

        .alumni-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 16px;
            overflow: hidden;
            border: 3px solid var(--bg-white);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .alumni-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .alumni-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .alumni-designation {
            color: var(--primary-color);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .alumni-institute {
            color: var(--text-medium);
            font-size: 13px;
            margin-bottom: 12px;
        }

        .alumni-batch {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, rgba(236, 195, 92, 0.15), rgba(236, 195, 92, 0.25));
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
            flex-grow: 1;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 18px;
            height: 18px;
            color: var(--secondary-color);
            flex-shrink: 0;
            margin-top: 2px;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
            word-break: break-word;
        }

        .info-value a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .info-value a:hover {
            color: var(--secondary-color);
        }

        /* Empty State */
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-white);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .no-results i {
            color: var(--text-light);
            margin-bottom: 16px;
        }

        .no-results h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .no-results p {
            font-size: 14px;
            color: var(--text-medium);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .directory-header {
                padding: 36px 16px;
            }

            .header-title {
                font-size: 28px;
            }

            .header-subtitle {
                font-size: 14px;
            }

            .controls-section {
                margin: -24px 16px 16px;
                padding: 20px;
            }

            .filters-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .results-section {
                padding: 0 16px 40px;
            }

            .alumni-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .results-title {
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .header-title {
                font-size: 24px;
            }

            .controls-section {
                padding: 16px;
            }

            .card-header {
                padding: 20px 16px 16px;
            }

            .card-body {
                padding: 16px;
            }
        }

        /* Smooth transitions */
        .alumni-card {
            opacity: 0;
            animation: fadeInUp 0.4s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stagger animation for cards */
        .alumni-card:nth-child(1) { animation-delay: 0.05s; }
        .alumni-card:nth-child(2) { animation-delay: 0.1s; }
        .alumni-card:nth-child(3) { animation-delay: 0.15s; }
        .alumni-card:nth-child(4) { animation-delay: 0.2s; }
        .alumni-card:nth-child(5) { animation-delay: 0.25s; }
        .alumni-card:nth-child(6) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="directory-header">
            <div class="header-content">
                <h1 class="header-title">Alumni Directory</h1>
                <p class="header-subtitle">Connect with graduates from around the world</p>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="controls-section">
            <div class="controls-container">
                <!-- Search -->
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search by name, designation, or institute..." id="alumniSearch">
                </div>

                <!-- Filters -->
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Graduation Year</label>
                        <select class="filter-select" id="batchFilter">
                            <option value="">All Years</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                            <option value="2020">2020</option>
                            <option value="2019">2019</option>
                            <option value="2018">2018</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Institute</label>
                        <select class="filter-select" id="instituteFilter">
                            <option value="">All Institutes</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Branch</label>
                        <select class="filter-select" id="branchFilter">
                            <option value="">All Branches</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="results-section">
            <div class="results-header">
                <h2 class="results-title">Alumni Profiles</h2>
                <p class="results-count" id="resultsCount">Showing all alumni</p>
            </div>

            <div class="alumni-grid">
                <?php 
                if (isset($debug_error)): ?>
                    <div class="no-results">
                        <i class="fas fa-exclamation-triangle fa-3x"></i>
                        <h3>Database Error</h3>
                        <p><?php echo htmlspecialchars($debug_error); ?></p>
                    </div>
                <?php elseif (empty($alumni)): ?>
                    <div class="no-results">
                        <i class="fas fa-users fa-3x"></i>
                        <h3>No Alumni Found</h3>
                        <p>The directory is currently empty.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($alumni as $alumnus): ?>
                        <div class="alumni-card" 
                             data-name="<?php echo strtolower(htmlspecialchars($alumnus['name'])); ?>"
                             data-batch="<?php echo htmlspecialchars($alumnus['year_of_graduation']); ?>"
                             data-institute="<?php echo strtolower(htmlspecialchars($alumnus['institute'])); ?>"
                             data-branch="<?php echo strtolower(htmlspecialchars($alumnus['branch'])); ?>"
                             data-designation="<?php echo strtolower(htmlspecialchars($alumnus['designation'])); ?>">
                            <div class="card-header">
                                <div class="alumni-avatar">
                                    <img src="<?php 
                                        if (!empty($alumnus['photo'])) {
                                            echo htmlspecialchars($alumnus['photo']);
                                        } else {
                                            echo 'https://ui-avatars.com/api/?name=' . urlencode($alumnus['name']) . '&size=160&background=5b1f1f&color=fff&bold=true';
                                        }
                                    ?>" alt="<?php echo htmlspecialchars($alumnus['name']); ?>">
                                </div>
                                <h3 class="alumni-name"><?php echo htmlspecialchars($alumnus['name']); ?></h3>
                                <?php if (!empty($alumnus['designation']) && $alumnus['designation'] !== 'Not specified'): ?>
                                    <p class="alumni-designation"><?php echo htmlspecialchars($alumnus['designation']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($alumnus['institute']) && $alumnus['institute'] !== 'Not specified'): ?>
                                    <p class="alumni-institute"><?php echo htmlspecialchars($alumnus['institute']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($alumnus['year_of_graduation']) && $alumnus['year_of_graduation'] !== 'N/A'): ?>
                                    <span class="alumni-batch">
                                        <i class="fas fa-graduation-cap"></i>
                                        Class of <?php echo htmlspecialchars($alumnus['year_of_graduation']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($alumnus['branch']) && $alumnus['branch'] !== 'Not specified'): ?>
                                    <div class="info-item">
                                        <i class="fas fa-book info-icon"></i>
                                        <div class="info-content">
                                            <div class="info-label">Branch</div>
                                            <div class="info-value"><?php echo htmlspecialchars($alumnus['branch']); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($alumnus['email']) && $alumnus['email'] !== 'No Email'): ?>
                                    <div class="info-item">
                                        <i class="fas fa-envelope info-icon"></i>
                                        <div class="info-content">
                                            <div class="info-label">Email</div>
                                            <div class="info-value">
                                                <a href="mailto:<?php echo htmlspecialchars($alumnus['email']); ?>">
                                                    <?php echo htmlspecialchars($alumnus['email']); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alumniSearch = document.getElementById('alumniSearch');
            const batchFilter = document.getElementById('batchFilter');
            const instituteFilter = document.getElementById('instituteFilter');
            const branchFilter = document.getElementById('branchFilter');
            const profileCards = document.querySelectorAll('.alumni-card');
            const resultsCount = document.getElementById('resultsCount');

            populateFilters();

            function searchAlumni() {
                const searchTerm = alumniSearch.value.toLowerCase();
                const batchValue = batchFilter.value;
                const instituteValue = instituteFilter.value.toLowerCase();
                const branchValue = branchFilter.value.toLowerCase();

                let visibleCount = 0;

                profileCards.forEach(card => {
                    const profileName = card.dataset.name || '';
                    const profileBatch = card.dataset.batch || '';
                    const profileInstitute = card.dataset.institute || '';
                    const profileBranch = card.dataset.branch || '';
                    const profileDesignation = card.dataset.designation || '';

                    const matchesSearch = searchTerm === '' ||
                        profileName.includes(searchTerm) ||
                        profileDesignation.includes(searchTerm) ||
                        profileInstitute.includes(searchTerm) ||
                        profileBranch.includes(searchTerm);

                    const matchesBatch = batchValue === '' || profileBatch === batchValue;
                    const matchesInstitute = instituteValue === '' || profileInstitute.includes(instituteValue);
                    const matchesBranch = branchValue === '' || profileBranch.includes(branchValue);

                    if (matchesSearch && matchesBatch && matchesInstitute && matchesBranch) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (visibleCount === profileCards.length) {
                    resultsCount.textContent = `Showing all ${visibleCount} alumni`;
                } else if (visibleCount === 0) {
                    resultsCount.textContent = 'No alumni found';
                } else {
                    resultsCount.textContent = `Showing ${visibleCount} of ${profileCards.length} alumni`;
                }
            }

            function populateFilters() {
                const institutes = new Set();
                const branches = new Set();

                profileCards.forEach(card => {
                    const institute = card.dataset.institute;
                    const branch = card.dataset.branch;

                    if (institute && institute !== 'not specified') {
                        institutes.add(institute);
                    }
                    if (branch && branch !== 'not specified') {
                        branches.add(branch);
                    }
                });

                Array.from(institutes).sort().forEach(institute => {
                    const option = document.createElement('option');
                    option.value = institute;
                    option.textContent = institute.split(' ').map(word => 
                        word.charAt(0).toUpperCase() + word.slice(1)
                    ).join(' ');
                    instituteFilter.appendChild(option);
                });

                Array.from(branches).sort().forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch;
                    option.textContent = branch.split(' ').map(word => 
                        word.charAt(0).toUpperCase() + word.slice(1)
                    ).join(' ');
                    branchFilter.appendChild(option);
                });
            }

            if (alumniSearch) alumniSearch.addEventListener('input', searchAlumni);
            if (batchFilter) batchFilter.addEventListener('change', searchAlumni);
            if (instituteFilter) instituteFilter.addEventListener('change', searchAlumni);
            if (branchFilter) branchFilter.addEventListener('change', searchAlumni);

            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    
                    document.querySelectorAll('.has-dropdown').forEach(item => {
                        if (item !== parent) {
                            item.classList.remove('active');
                        }
                    });
                    
                    parent.classList.toggle('active');
                });
            });
        });
    </script>
</body>
</html>