<?php
// Admin Dashboard
session_start();

// Check if user is logged in and is director
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header("Location: login.php");
    exit();
}

// Verify user is actually a director in the database
require_once '../includes/db_config.php';
$stmt = $pdo->prepare("SELECT is_director, name FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_director'] != 1) {
    // User is not a director, redirect to appropriate location
    header("Location: ../login.php");
    exit();
}

// Set admin role for this session if not already set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
}

// Update session name if different
if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] != $user['name']) {
    $_SESSION['user_name'] = $user['name'];
}

// Initialize default values for all statistics
$totalUsers = $totalNotices = $totalNews = $totalEvents = $totalJobs = $totalGroups = 0;
$recentActivity = [];
$error = '';

// Get dashboard statistics
try {
    // Total users (exclude directors and admin users)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE (is_director = 0 OR is_director IS NULL) AND is_active = 1");
    $result = $stmt->fetch();
    $totalUsers = $result ? $result['total'] : 0;

    // Total noticeboard posts
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM noticeboard WHERE is_active = 1");
        $result = $stmt->fetch();
        $totalNotices = $result ? $result['total'] : 0;
    } catch(PDOException $e) {
        // Table might not exist yet
        $totalNotices = 0;
    }

    // Total news articles
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM news WHERE is_active = 1");
        $result = $stmt->fetch();
        $totalNews = $result ? $result['total'] : 0;
    } catch(PDOException $e) {
        // Table might not exist yet
        $totalNews = 0;
    }

    // Total events
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE is_active = 1");
        $result = $stmt->fetch();
        $totalEvents = $result ? $result['total'] : 0;
    } catch(PDOException $e) {
        // Table might not exist yet
        $totalEvents = 0;
    }

    // Total jobs
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobs WHERE is_active = 1");
        $result = $stmt->fetch();
        $totalJobs = $result ? $result['total'] : 0;
    } catch(PDOException $e) {
        // Table might not exist yet
        $totalJobs = 0;
    }

    // Total interest groups
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM interest_groups WHERE is_active = 1");
        $result = $stmt->fetch();
        $totalGroups = $result ? $result['total'] : 0;
    } catch(PDOException $e) {
        // Table might not exist yet
        $totalGroups = 0;
    }

    // Pending posts count
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM posts WHERE status = 'pending'");
        $result = $stmt->fetch();
        $pendingPosts = $result ? $result['total'] : 0;
    } catch(PDOException $e) {
        // Table might not exist yet
        $pendingPosts = 0;
    }

    // Recent activity (only from existing tables)
    try {
        $activityQueries = [];
        $activityParams = [];

        // Check which tables exist and build query accordingly
        $tablesToCheck = ['noticeboard', 'news', 'events', 'jobs'];
        $existingTables = [];

        foreach ($tablesToCheck as $table) {
            try {
                $pdo->query("SELECT 1 FROM $table LIMIT 1");
                $existingTables[] = $table;
            } catch(PDOException $e) {
                // Table doesn't exist, skip it
            }
        }

        if (!empty($existingTables)) {
            $unionQueries = [];
            foreach ($existingTables as $table) {
                $typeMap = [
                    'noticeboard' => 'notice',
                    'news' => 'news',
                    'events' => 'event',
                    'jobs' => 'job'
                ];

                if (isset($typeMap[$table])) {
                    $unionQueries[] = "SELECT '{$typeMap[$table]}' as type, title, created_at FROM $table WHERE is_active = 1";
                }
            }

            if (!empty($unionQueries)) {
                try {
                    $stmt = $pdo->query("(" . implode(' UNION ALL ', $unionQueries) . ") ORDER BY created_at DESC LIMIT 10");
                    $recentActivity = $stmt->fetchAll();
                } catch(PDOException $e) {
                    // Query failed, set empty array
                    $recentActivity = [];
                }
            }
        }
    } catch(PDOException $e) {
        // No recent activity or tables don't exist
        $recentActivity = [];
    }

} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    // Set all defaults to 0 if database connection fails
    $totalUsers = $totalNotices = $totalNews = $totalEvents = $totalJobs = $totalGroups = 0;
    $recentActivity = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Director Dashboard - Alumni Connect</title>
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
            position: sticky;
            top: 0;
            z-index: 100;
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

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user-info {
            text-align: right;
        }

        .admin-user-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .admin-user-role {
            font-size: 12px;
            color: var(--text-light);
        }

        .logout-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #4a1919;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-header {
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .dashboard-subtitle {
            color: var(--text-light);
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(91, 31, 31, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .management-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .management-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .management-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .management-card p {
            color: var(--text-light);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .management-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
            text-decoration: none;
        }

        .management-btn:hover {
            background: #4a1919;
        }

        .activity-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-top: 30px;
        }

        .activity-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .activity-list {
            list-style: none;
            padding: 0;
        }

        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-type {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            background: rgba(91, 31, 31, 0.1);
            color: var(--primary-color);
            font-weight: 500;
            text-transform: uppercase;
        }

        .activity-title-text {
            font-weight: 500;
            color: var(--text-color);
            margin-left: 15px;
            flex-grow: 1;
        }

        .activity-date {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .dashboard-container {
                padding: 20px 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .management-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">Alumni Connect Director</div>
            <div class="admin-user">
                <div class="admin-user-info">
                    <div class="admin-user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="admin-user-role">Director</div>
                </div>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Director Dashboard</h1>
            <p class="dashboard-subtitle">Manage your alumni community content and users</p>

            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #fecaca;">
                    <strong>⚠️ Dashboard Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif ($totalUsers == 0 && $totalNotices == 0 && $totalNews == 0 && $totalEvents == 0 && $totalJobs == 0): ?>
                <div style="background: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #fbbf24;">
                    <strong>📋 Setup Required:</strong> Some database tables may not exist yet. <a href="setup_database.php" style="color: #b45309; font-weight: bold;">Run Database Setup</a> to create all required tables and import sample data.
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(91, 31, 31, 0.1); color: var(--primary-color);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Alumni</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(236, 195, 92, 0.1); color: #d97706;">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="stat-number"><?php echo $totalNotices; ?></div>
                <div class="stat-label">Noticeboard Posts</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-number"><?php echo $totalNews; ?></div>
                <div class="stat-label">News Articles</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #7c3aed;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-number"><?php echo $totalEvents; ?></div>
                <div class="stat-label">Events</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-number"><?php echo $totalJobs; ?></div>
                <div class="stat-label">Job Postings</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-number"><?php echo $totalGroups; ?></div>
                <div class="stat-label">Interest Groups</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #2563eb;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $pendingPosts; ?></div>
                <div class="stat-label">Pending Posts</div>
            </div>
        </div>

        <!-- Management Cards -->
        <div class="management-grid">
            <div class="management-card">
                <h3><i class="fas fa-bullhorn"></i> Noticeboard</h3>
                <p>Manage announcements and important notices for the alumni community.</p>
                <a href="manage_noticeboard.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-newspaper"></i> News</h3>
                <p>Publish news articles and updates about alumni achievements and university happenings.</p>
                <a href="manage_news.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-check-circle"></i> Approve Posts</h3>
                <p>Review and approve pending posts from alumni before they appear in the mobile app feed.</p>
                <a href="approve_posts.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-calendar-alt"></i> Events</h3>
                <p>Organize and manage alumni events, meetups, and gatherings.</p>
                <a href="manage_events.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-briefcase"></i> Jobs</h3>
                <p>Post job opportunities and career openings for alumni.</p>
                <a href="manage_jobs.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

<div class="management-card">
                <h3><i class="fas fa-users-cog"></i> Interest Groups</h3>
                <p>Create and manage special interest groups for alumni networking.</p>
                <a href="manage_groups.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-users"></i> User Management</h3>
                <p>Manage alumni user accounts and permissions.</p>
                <a href="manage_users.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

<div class="management-card">
                <h3><i class="fas fa-chalkboard-teacher"></i> Manage Mentors</h3>
                <p>Review and approve mentor applications from alumni members.</p>
                <a href="manage_mentors.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-map-marker-alt"></i> Chapters</h3>
                <p>Manage regional and special interest chapters for alumni communities.</p>
                <a href="chapters.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-medal"></i> Institute Medals</h3>
                <p>Award and manage medals for outstanding alumni achievements.</p>
                <a href="manage_medals.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>

            <div class="management-card">
                <h3><i class="fas fa-people-arrows"></i> Map Mentors</h3>
                <p>Assign and manage mentor-student relationships and pairings.</p>
                <a href="map_mentors.php" class="management-btn">
                    <i class="fas fa-cog"></i> Manage
                </a>
            </div>
        </div>

    </div>
</body>
</html>
