<?php
session_start();
require_once '../includes/db_config.php';

// Check if user is logged in
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Alumni Connect</title>
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

        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }
        }

        .announcements-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .announcements-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .announcements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .announcement-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .announcement-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--primary-color);
            color: white;
        }

        .announcement-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 5px 0;
        }

        .announcement-date {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }

        .announcement-body {
            padding: 20px;
        }

        .announcement-content {
            color: var(--text-color);
            margin: 0 0 15px 0;
            line-height: 1.6;
        }

        .announcement-category {
            display: inline-block;
            background-color: var(--secondary-color);
            color: var(--primary-color);
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 12px;
            margin-right: 8px;
            margin-bottom: 10px;
        }

        .no-announcements {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .announcements-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .announcements-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="announcements-container">
            <div class="announcements-header">
                <h1 class="announcements-title">Announcements</h1>
            </div>

            <div class="announcements-grid">
                <?php
                try {
                    // Fetch announcements from database
                    $stmt = $pdo->query("
                        SELECT a.*, u.name as director_name
                        FROM announcements a
                        LEFT JOIN users u ON a.director_id = u.id
                        ORDER BY a.created_at DESC
                    ");
                    $announcements = $stmt->fetchAll();

                    if (empty($announcements)) {
                        echo '<div class="no-announcements">
                                <i class="fas fa-bullhorn" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                                <h3>No Announcements Yet</h3>
                                <p>There are currently no announcements to display.</p>
                              </div>';
                    } else {
                        foreach ($announcements as $announcement) {
                            $formatted_date = date('M j, Y', strtotime($announcement['created_at']));
                            $category = $announcement['is_global'] ? 'Global' : 'Institute Specific';
                            if ($announcement['target_institute']) {
                                $category = $announcement['target_institute'];
                            }
                            ?>
                            <div class="announcement-card">
                                <div class="announcement-header">
                                    <h3 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                    <p class="announcement-date">Posted on: <?php echo $formatted_date; ?></p>
                                </div>
                                <div class="announcement-body">
                                    <span class="announcement-category"><?php echo htmlspecialchars($category); ?></span>
                                    <p class="announcement-content">
                                        <?php echo htmlspecialchars($announcement['content']); ?>
                                    </p>
                                    <?php if ($announcement['director_name']): ?>
                                        <p style="font-size: 12px; color: var(--text-light); margin-top: 15px; padding-top: 10px; border-top: 1px solid var(--border-color);">
                                            Posted by: <?php echo htmlspecialchars($announcement['director_name']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } catch(PDOException $e) {
                    echo '<div class="no-announcements">
                            <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #f39c12; margin-bottom: 20px;"></i>
                            <h3>Error Loading Announcements</h3>
                            <p>There was a problem loading the announcements. Please try again later.</p>
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add any JavaScript functionality here
            console.log('Announcements page loaded');

            // Example: Toggle mobile menu
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.toggle('active');
                    }
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    this.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
