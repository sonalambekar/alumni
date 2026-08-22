<?php
// Notice Board - Displaying dynamic content from database
require_once '../includes/db_config.php';

// Initialize variables
$notices = [];
$error = '';

// Get all active notices
try {
    // Check if tables exist before querying
    $tablesExist = true;
    try {
        $pdo->query("SELECT 1 FROM noticeboard LIMIT 1");
        $pdo->query("SELECT 1 FROM users LIMIT 1");
    } catch(PDOException $e) {
        $tablesExist = false;
    }

    if ($tablesExist) {
        try {
            // Try to get author name, fallback to author_id if full_name doesn't exist
            $stmt = $pdo->query("
                SELECT n.*,
                       COALESCE(u.full_name, u.name, CONCAT('User #', n.author_id)) as author_name
                FROM noticeboard n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.is_active = 1
                ORDER BY
                    CASE n.priority
                        WHEN 'high' THEN 1
                        WHEN 'medium' THEN 2
                        WHEN 'low' THEN 3
                    END,
                    n.created_at DESC
            ");
            $notices = $stmt->fetchAll();
        } catch(PDOException $e) {
            // If the join fails, try without the author name
            $stmt = $pdo->query("
                SELECT n.*, CONCAT('User #', n.author_id) as author_name
                FROM noticeboard n
                WHERE n.is_active = 1
                ORDER BY
                    CASE n.priority
                        WHEN 'high' THEN 1
                        WHEN 'medium' THEN 2
                        WHEN 'low' THEN 3
                    END,
                    n.created_at DESC
            ");
            $notices = $stmt->fetchAll();
        }
    } else {
        $error = "Database tables not found. Please run the database setup first.";
    }
} catch(PDOException $e) {
    $error = "Error loading notices: " . $e->getMessage();
}

function parse_basic_markdown($text) {
    // Parse **bold** text
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    return $text;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Board - Alumni Connect</title>
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

        .noticeboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .noticeboard-header {
            background: linear-gradient(135deg, var(--primary-color), #8b2b2b);
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px rgba(91,31,31,0.2);
        }

        .noticeboard-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: white;
        }
        
        .noticeboard-subtitle {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 15px;
        }

        .create-notice-btn {
            background: white;
            color: var(--primary-color);
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .create-notice-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }

        .notices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .notice-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .notice-card[data-priority="high"] { border-top: 4px solid #dc2626; }
        .notice-card[data-priority="medium"] { border-top: 4px solid #d97706; }
        .notice-card[data-priority="low"] { border-top: 4px solid #0369a1; }

        .notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .notice-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notice-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: var(--text-color);
            line-height: 1.4;
        }

        .notice-date {
            font-size: 12px;
            color: var(--text-light);
        }

        .notice-body {
            padding: 20px;
        }

        .notice-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 12px;
            color: var(--text-light);
        }

        .notice-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .notice-content {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .notice-actions {
            display: flex;
            gap: 10px;
            padding: 20px;
            border-top: 1px solid var(--border-color);
            margin-top: auto;
        }

        .action-btn {
            background: #f8f9fa;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-color);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(4px);
        }

        .notice-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-high {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .priority-medium {
            background-color: #fef3c7;
            color: #d97706;
        }

        .priority-low {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .no-notices {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .no-notices-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .notices-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .notices-grid {
                grid-template-columns: 1fr;
            }

            .noticeboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .create-notice-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="noticeboard-container">
            <div class="noticeboard-header">
                <div>
                    <h1 class="noticeboard-title">Notice Board</h1>
                    <p class="noticeboard-subtitle">Stay updated with the latest announcements from GM University</p>
                </div>
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/alumni/admin/manage_noticeboard.php" class="create-notice-btn">
                        <i class="fas fa-plus"></i>
                        Manage Notices
                    </a>
                <?php endif; ?>
            </div>

            <div class="notices-grid">
                <?php if (isset($error) && !empty($error)): ?>
                    <div style="grid-column: 1 / -1; background: #fee2e2; color: #991b1b; padding: 20px; border-radius: 8px; border: 1px solid #fca5a5; text-align: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                        <strong>Notice Board Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php elseif (isset($notices) && !empty($notices)): ?>
                    <?php foreach ($notices as $notice): ?>
                        <div class="notice-card" data-priority="<?php echo htmlspecialchars($notice['priority']); ?>">
                            <div class="notice-header">
                                <h3 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h3>
                                <span class="notice-date">
                                    <?php echo date('M j, Y', strtotime($notice['publish_date'])); ?>
                                </span>
                            </div>
                            <div class="notice-body">
                                <div class="notice-meta">
                                    <span class="notice-meta-item">
                                        <i class="far fa-user"></i> <?php echo htmlspecialchars($notice['author_name']); ?>
                                    </span>
                                    <span class="notice-meta-item">
                                        <i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($notice['publish_date'])); ?>
                                    </span>
                                    <span class="notice-priority priority-<?php echo $notice['priority']; ?>">
                                        <?php echo ucfirst($notice['priority']); ?>
                                    </span>
                                </div>
                                <p class="notice-content">
                                    <?php 
                                        $content = strip_tags($notice['content']);
                                        // Remove markdown for clean preview
                                        $clean_content = str_replace('**', '', $content);
                                        $snippet = mb_substr($clean_content, 0, 150);
                                        if (mb_strlen($clean_content) > 150) $snippet .= '...';
                                        echo htmlspecialchars($snippet);
                                    ?>
                                </p>
                            </div>
                            <div class="notice-actions">
                                <button class="action-btn" onclick="viewNotice(<?php echo $notice['id']; ?>)">
                                    View Details <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-notices">
                        <i class="fas fa-bullhorn no-notices-icon"></i>
                        <h3>No notices available</h3>
                        <p>Check back later for important announcements and updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notice Detail Modal -->
    <div class="modal" id="noticeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary-color);">
                <h3 class="modal-title" id="modalTitle">Notice Details</h3>
                <button class="close-btn" onclick="closeNoticeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            <div id="noticeDetailContent">
                <!-- Notice details will be populated here -->
            </div>
        </div>
    </div>

    <script>
        function viewNotice(noticeId) {
            // Find the notice data
            <?php
            $notices_json = json_encode($notices);
            echo "const notices = $notices_json;";
            ?>

            const notice = notices.find(n => n.id == noticeId);

            if (notice) {
                const modal = document.getElementById('noticeModal');
                const title = document.getElementById('modalTitle');
                const content = document.getElementById('noticeDetailContent');

                title.textContent = notice.title;
                content.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; gap: 15px; margin-bottom: 15px; font-size: 12px; color: #6b7280;">
                            <span><i class="far fa-user"></i> ${notice.author_name}</span>
                            <span><i class="far fa-clock"></i> ${new Date(notice.publish_date).toLocaleDateString()} at ${new Date(notice.publish_date).toLocaleTimeString()}</span>
                            <span style="padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; background: ${notice.priority === 'high' ? '#fee2e2; color: #dc2626;' : notice.priority === 'medium' ? '#fef3c7; color: #d97706;' : '#e0f2fe; color: #0369a1;'};">
                                ${notice.priority.toUpperCase()}
                            </span>
                        </div>
                        <div style="font-size: 16px; line-height: 1.6; color: #333; white-space: pre-wrap;">${notice.content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')}</div>
                    </div>
                `;

                modal.style.display = 'flex';
            }
        }

        function closeNoticeModal() {
            document.getElementById('noticeModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('noticeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNoticeModal();
            }
        });

        // Handle "Create New Notice" button for regular users
        document.addEventListener('DOMContentLoaded', function() {
            const createBtn = document.querySelector('.create-notice-btn');
            if (createBtn && !createBtn.href) {
                createBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('Please contact an administrator to post notices.');
                });
            }
        });
    </script>
</body>
</html>
