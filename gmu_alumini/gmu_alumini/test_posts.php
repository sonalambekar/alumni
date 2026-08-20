<?php
require_once "config/database.php";

echo "<html><head><title>Test Posts</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f8f9fa; }
    .approved { color: green; font-weight: bold; }
    .pending { color: orange; font-weight: bold; }
    .rejected { color: red; font-weight: bold; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>📝 Posts Test</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<div style='color: red;'>❌ Database connection failed</div>";
        exit;
    }

    echo "<div style='color: green;'>✅ Database connected successfully</div>";

    // Get all posts
    echo "<h2>All Posts</h2>";
    $stmt = $conn->query("SELECT p.*, u.name as user_name FROM posts p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($posts)) {
        echo "<p>No posts found in database.</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>User</th><th>Content</th><th>Status</th><th>Created</th></tr>";
        foreach ($posts as $post) {
            $statusClass = $post['status'] ?? 'pending';
            echo "<tr>";
            echo "<td>{$post['id']}</td>";
            echo "<td>{$post['user_name']} (ID: {$post['user_id']})</td>";
            echo "<td>" . substr($post['content'], 0, 50) . "...</td>";
            echo "<td class='$statusClass'>" . ($post['status'] ?? 'NULL') . "</td>";
            echo "<td>{$post['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Get approved posts only
    echo "<h2>Approved Posts Only</h2>";
    $stmt = $conn->query("SELECT p.*, u.name as user_name FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.status = 'approved' ORDER BY p.created_at DESC");
    $approvedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($approvedPosts)) {
        echo "<p>No approved posts found.</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>User</th><th>Content</th><th>Created</th></tr>";
        foreach ($approvedPosts as $post) {
            echo "<tr>";
            echo "<td>{$post['id']}</td>";
            echo "<td>{$post['user_name']} (ID: {$post['user_id']})</td>";
            echo "<td>" . substr($post['content'], 0, 100) . "</td>";
            echo "<td>{$post['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Test API endpoint
    echo "<h2>Test API Response</h2>";
    echo "<p><a href='api/posts.php?action=get_posts&limit=10' target='_blank'>Test API: api/posts.php?action=get_posts&limit=10</a></p>";

} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
