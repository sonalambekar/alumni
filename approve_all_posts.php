<?php
require_once 'includes/db_config.php';

try {
    echo "<h2>Auto-Approving All Posts</h2>";
    
    // Get pending posts count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE status = 'pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pendingCount = $result['count'];
    
    echo "<p>Found <strong>$pendingCount</strong> pending posts</p>";
    
    // Approve all pending posts
    $pdo->exec("UPDATE posts SET status = 'approved' WHERE status = 'pending'");
    
    echo "<p style='color: green;'>✓ All posts have been approved!</p>";
    
    // Show approved posts
    $stmt = $pdo->query("SELECT p.id, p.content, u.name, p.created_at 
                         FROM posts p 
                         JOIN users u ON p.user_id = u.id 
                         WHERE p.status = 'approved' 
                         ORDER BY p.created_at DESC 
                         LIMIT 10");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Recent Approved Posts:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Author</th><th>Content</th><th>Created</th></tr>";
    foreach ($posts as $post) {
        echo "<tr>";
        echo "<td>" . $post['id'] . "</td>";
        echo "<td>" . htmlspecialchars($post['name']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['content'], 0, 100)) . "...</td>";
        echo "<td>" . $post['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><a href='index.php'>Go to Home</a> | <a href='api/posts/list.php?user_id=1'>Test Posts API</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
