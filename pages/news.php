<?php
// News Corner - Displaying dynamic content from database
require_once '../includes/db_config.php';

// Initialize variables
$news = [];
$error = '';

// Get all active news articles
try {
    // Check if tables exist before querying
    $tablesExist = true;
    try {
        $pdo->query("SELECT 1 FROM news LIMIT 1");
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
                FROM news n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.is_active = 1
                ORDER BY n.is_featured DESC, n.publish_date DESC
            ");
            $news = $stmt->fetchAll();
        } catch(PDOException $e) {
            // If the join fails, try without the author name
            $stmt = $pdo->query("
                SELECT n.*, CONCAT('User #', n.author_id) as author_name
                FROM news n
                WHERE n.is_active = 1
                ORDER BY n.is_featured DESC, n.publish_date DESC
            ");
            $news = $stmt->fetchAll();
        }
    } else {
        $error = "Database tables not found. Please run the database setup first.";
    }
} catch(PDOException $e) {
    $error = "Error loading news: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Corner - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .news-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            min-height: 100vh;
        }

        .news-title {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .news-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 40px;
        }

        .manage-news {
            text-align: right;
            margin-bottom: 20px;
        }

        .manage-news-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .manage-news-btn:hover {
            background: #4a1a1a;
            transform: translateY(-2px);
        }

        /* Bulletin Board Background */
        .bulletin-board {
            background: linear-gradient(135deg, #f4e4bc 0%, #e8d5a3 50%, #dcc88a 100%);
            min-height: calc(100vh - 200px);
            padding: 40px;
            position: relative;
            border-radius: 15px;
            box-shadow: inset 0 0 50px rgba(139, 115, 85, 0.3);
        }

        .bulletin-board::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(139, 115, 85, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(139, 115, 85, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(139, 115, 85, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Bulletin Cards - Pinned Paper Style */
        .news-bulletin {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            position: relative;
            z-index: 2;
        }

        .bulletin-card {
            background: #fefefe;
            border-radius: 8px;
            padding: 20px;
            box-shadow:
                0 4px 8px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            position: relative;
            transition: all 0.3s ease;
            transform-origin: center;
            animation: bulletinFloat 6s ease-in-out infinite;
            cursor: pointer;
        }

        .bulletin-card:nth-child(3n) {
            animation-delay: -2s;
            transform: rotate(-1deg);
        }

        .bulletin-card:nth-child(5n) {
            animation-delay: -4s;
            transform: rotate(0.5deg);
        }

        .bulletin-card:nth-child(7n) {
            animation-delay: -1s;
            transform: rotate(-0.5deg);
        }

        .bulletin-card:nth-child(8n) {
            animation-delay: -3s;
            transform: rotate(1deg);
        }

        .bulletin-card:hover {
            transform: rotate(0deg) translateY(-5px) scale(1.02);
            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            animation-play-state: paused;
        }

        /* Pin Effect */
        .pin {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 16px;
            background: radial-gradient(circle, #c41e3a 0%, #8b1538 100%);
            border-radius: 50%;
            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .pin::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: radial-gradient(circle, #ff6b6b 0%, #d63031 100%);
            border-radius: 50%;
        }

        /* Card Content */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ddd;
        }

        .news-category {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #f39c12 100%);
            color: var(--primary-color);
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .news-date {
            color: #888;
            font-size: 12px;
            font-style: italic;
        }

        .news-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .news-excerpt {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 20px;
            background: rgba(91, 31, 31, 0.1);
        }

        .featured-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* No News Message */
        .no-news {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .no-news-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        @keyframes bulletinFloat {
            0%, 100% { transform: translateY(0px) rotate(var(--rotation, 0deg)); }
            25% { transform: translateY(-3px) rotate(calc(var(--rotation, 0deg) + 0.5deg)); }
            50% { transform: translateY(-1px) rotate(var(--rotation, 0deg)); }
            75% { transform: translateY(-4px) rotate(calc(var(--rotation, 0deg) - 0.5deg)); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .news-bulletin {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .bulletin-board {
                padding: 20px;
            }

            .news-bulletin {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .bulletin-card {
                padding: 15px;
            }

            .news-title {
                font-size: 16px;
            }

            .news-excerpt {
                font-size: 13px;
            }

            .manage-news {
                text-align: center;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            .news-container {
                padding: 15px;
            }

            .news-title {
                font-size: 1.8rem;
            }

            .bulletin-card {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="news-container">
            <h2 class="news-title">News Corner</h2>
            <p class="news-subtitle">Stay informed with the latest news and updates from our alumni community</p>

            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="manage-news">
                    <a href="/alumni/admin/manage_news.php" class="manage-news-btn">
                        <i class="fas fa-cog"></i> Manage News
                    </a>
                </div>
            <?php endif; ?>

            <?php if (isset($error) && !empty($error)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 20px; border-radius: 8px; border: 1px solid #fca5a5; text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                    <strong>News Corner Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="bulletin-board">
                <div class="news-bulletin">
                    <?php if (isset($news) && !empty($news)): ?>
                        <?php foreach ($news as $article): ?>
                            <div class="bulletin-card <?php echo $article['is_featured'] ? 'featured' : ''; ?>">
                                <?php if ($article['is_featured']): ?>
                                    <div class="featured-badge">Featured</div>
                                <?php endif; ?>
                                <div class="pin"></div>
                                <div class="card-header">
                                    <span class="news-category">
                                        <i class="fas fa-tag"></i> News
                                    </span>
                                    <span class="news-date">
                                        <?php echo date('M j, Y', strtotime($article['publish_date'])); ?>
                                    </span>
                                </div>
                                <h3 class="news-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                                <?php if ($article['excerpt']): ?>
                                    <p class="news-excerpt">
                                        <?php echo htmlspecialchars($article['excerpt']); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="news-excerpt">
                                        <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 120)); ?>...
                                    </p>
                                <?php endif; ?>
                                <div class="news-author">
                                    <i class="fas fa-user"></i> by <?php echo htmlspecialchars($article['author_name']); ?>
                                </div>
                                <a href="#" class="read-more" onclick="viewNews(<?php echo $article['id']; ?>)">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-news">
                            <i class="fas fa-newspaper no-news-icon"></i>
                            <h3>No news articles available</h3>
                            <p>Check back later for the latest updates and alumni news.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- News Detail Modal -->
    <div class="modal" id="newsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary-color);">
                <h3 class="modal-title" id="modalTitle">News Article</h3>
                <button class="close-btn" onclick="closeNewsModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            <div id="newsDetailContent">
                <!-- News details will be populated here -->
            </div>
        </div>
    </div>

    <script>
        function viewNews(newsId) {
            // Find the news data
            <?php
            $news_json = json_encode($news);
            echo "const news = $news_json;";
            ?>

            const article = news.find(n => n.id == newsId);

            if (article) {
                const modal = document.getElementById('newsModal');
                const title = document.getElementById('modalTitle');
                const content = document.getElementById('newsDetailContent');

                title.textContent = article.title;
                content.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; gap: 15px; margin-bottom: 15px; font-size: 12px; color: #6b7280;">
                            <span><i class="fas fa-user"></i> ${article.author_name}</span>
                            <span><i class="fas fa-calendar"></i> ${new Date(article.publish_date).toLocaleDateString()}</span>
                            <span><i class="fas fa-clock"></i> ${new Date(article.publish_date).toLocaleTimeString()}</span>
                        </div>
                        <div style="font-size: 16px; line-height: 1.6; color: #333; white-space: pre-wrap;">${article.content}</div>
                    </div>
                `;

                modal.style.display = 'flex';
            }
        }

        function closeNewsModal() {
            document.getElementById('newsModal').style.display = 'none';
        }

        });
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>
