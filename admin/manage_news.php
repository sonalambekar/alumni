<?php
// Admin News Management
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

        if ($action === 'add') {
            // Add new news article
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $excerpt = $_POST['excerpt'] ?? '';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($title) && !empty($content)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO news (title, content, excerpt, author_id, publish_date, is_featured, is_active)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $content, $excerpt, $_SESSION['user_id'], $publish_date, $is_featured, $is_active]);

                    $success = "News article added successfully!";
                } catch(PDOException $e) {
                    $error = "Error adding news article: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'edit') {
            // Update news article
            $id = $_POST['news_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $excerpt = $_POST['excerpt'] ?? '';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($title) && !empty($content) && !empty($id)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE news
                        SET title = ?, content = ?, excerpt = ?, publish_date = ?, is_featured = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $content, $excerpt, $publish_date, $is_featured, $is_active, $id]);

                    $success = "News article updated successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating news article: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'delete') {
            // Delete news article
            $id = $_POST['news_id'] ?? 0;

            if (!empty($id)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "News article deleted successfully!";
                } catch(PDOException $e) {
                    $error = "Error deleting news article: " . $e->getMessage();
                }
            }
        }
    }
}

// Initialize variables
$news = [];
$success = '';
$error = '';

// Get all news articles
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
    <title>Manage News - Admin Dashboard</title>
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

        .btn-danger {
            background: #dc2626;
            color: var(--white);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .news-table {
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

        .featured-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            background: #fbbf24;
            color: #92400e;
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
            <div class="admin-brand">News Management</div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">

        <!-- Add News Form -->
        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Add New Article</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="title">Title *</label>
                        <input type="text" id="title" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="publish_date">Publish Date</label>
                        <input type="datetime-local" id="publish_date" name="publish_date" class="form-input" value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="excerpt">Excerpt</label>
                    <textarea id="excerpt" name="excerpt" class="form-textarea" rows="3" placeholder="Brief summary of the article..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="content">Content *</label>
                    <textarea id="content" name="content" class="form-textarea" rows="8" required></textarea>
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="is_featured" name="is_featured"> Featured Article
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="is_active" name="is_active" checked> Publish Article (Active)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Publish Article
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- News List -->
        <div class="news-table">
            <div class="table-header">
                <h3 class="table-title"><i class="fas fa-list"></i> All Articles</h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Featured</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($news) && !empty($news)): ?>
                            <?php foreach ($news as $article): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                        <?php if (isset($article['excerpt']) && !empty($article['excerpt'])): ?>
                                            <br>
                                            <small style="color: var(--text-light);">
                                                <?php echo substr(strip_tags($article['excerpt']), 0, 100) . '...'; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($article['is_featured']) && $article['is_featured']): ?>
                                            <span class="featured-badge">Featured</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo (isset($article['is_active']) && $article['is_active']) ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo (isset($article['is_active']) && $article['is_active']) ? 'Published' : 'Draft'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($article['publish_date'])); ?>
                                        <br>
                                        <small style="color: var(--text-light);">
                                            <?php echo date('g:i A', strtotime($article['publish_date'])); ?>
                                        </small>
                                    </td>
                                    <td class="actions-cell">
                                        <button class="btn btn-secondary" onclick="editNews(<?php echo $article['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="news_id" value="<?php echo $article['id']; ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No articles found. Create your first article above.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function editNews(newsId) {
            // Find the news data and populate the modal
            const news = <?php echo json_encode($news); ?>;
            const article = news.find(n => n.id == newsId);

            if (article) {
                document.getElementById('edit_news_id').value = article.id;
                document.getElementById('edit_title').value = article.title || '';
                document.getElementById('edit_excerpt').value = article.excerpt || '';
                document.getElementById('edit_content').value = article.content || '';
                document.getElementById('edit_publish_date').value = article.publish_date ? article.publish_date.slice(0, 16) : '';
                document.getElementById('edit_is_featured').checked = (article.is_featured == 1);
                document.getElementById('edit_is_active').checked = (article.is_active == 1);

                document.getElementById('editModal').classList.add('show');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
    </script>

    <!-- Edit Modal -->
    <div class="modal" id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary-color);">
                <h3 class="modal-title">Edit News Article</h3>
                <button class="close-btn" onclick="closeEditModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="news_id" id="edit_news_id">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_title">Title *</label>
                        <input type="text" id="edit_title" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_publish_date">Publish Date</label>
                        <input type="datetime-local" id="edit_publish_date" name="publish_date" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit_excerpt">Excerpt</label>
                    <textarea id="edit_excerpt" name="excerpt" class="form-textarea" rows="3" placeholder="Brief summary of the article..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="edit_content">Content *</label>
                    <textarea id="edit_content" name="content" class="form-textarea" rows="8" required></textarea>
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="edit_is_featured" name="is_featured"> Featured Article
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="edit_is_active" name="is_active"> Article is Active (Published)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Article
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
