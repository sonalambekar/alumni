<?php
session_start();
require_once '../includes/db_config.php';

// Check if user is logged in and is director
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE posts SET status = 'approved' WHERE id = ?");
        $stmt->execute([$post_id]);
        $message = "Post approved successfully!";
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE posts SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$post_id]);
        $message = "Post rejected successfully!";
    }
}

// Get pending posts
$stmt = $pdo->query("
    SELECT p.*, u.name, u.usn, u.email_id
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'pending'
    ORDER BY p.created_at DESC
");
$pendingPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approve Posts - GMU Alumni</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #5B1F1F; margin-bottom: 20px; }
        .message { padding: 15px; background: #4CAF50; color: white; border-radius: 5px; margin-bottom: 20px; }
        .post-card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .post-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; }
        .user-info { flex: 1; }
        .user-info h3 { color: #333; margin-bottom: 5px; }
        .user-info p { color: #666; font-size: 14px; }
        .post-content { margin: 15px 0; line-height: 1.6; }
        .post-image { max-width: 100%; border-radius: 8px; margin: 15px 0; }
        .post-meta { color: #999; font-size: 12px; margin-bottom: 15px; }
        .actions { display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .btn-approve { background: #4CAF50; color: white; }
        .btn-reject { background: #f44336; color: white; }
        .btn:hover { opacity: 0.9; }
        .no-posts { text-align: center; padding: 40px; color: #999; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #5B1F1F; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">← Back to Home</a>
        <h1>Approve Posts</h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (empty($pendingPosts)): ?>
            <div class="no-posts">
                <h2>No pending posts</h2>
                <p>All posts have been reviewed!</p>
            </div>
        <?php else: ?>
            <?php foreach ($pendingPosts as $post): ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="user-info">
                            <h3><?php echo htmlspecialchars($post['name']); ?></h3>
                            <p>USN: <?php echo htmlspecialchars($post['usn']); ?> | Email: <?php echo htmlspecialchars($post['email_id']); ?></p>
                        </div>
                    </div>
                    
                    <div class="post-meta">
                        Posted on: <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                    
                    <?php if ($post['media_type'] === 'image' && $post['media_url']): ?>
                        <img src="../<?php echo htmlspecialchars($post['media_url']); ?>" 
                             alt="Post image" 
                             class="post-image"
                             onerror="this.style.display='none'">
                    <?php endif; ?>
                    
                    <div class="actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-approve">✓ Approve</button>
                        </form>
                        
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-reject">✗ Reject</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
