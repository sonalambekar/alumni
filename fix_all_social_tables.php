<?php
require_once 'includes/db_config.php';

try {
    echo "<h2>Fixing All Social Feature Tables</h2>";
    
    // Fix posts table
    echo "<h3>Posts Table</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM posts LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE posts ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER status");
        echo "<p>✓ Added is_active column to posts table</p>";
    } else {
        echo "<p>✓ is_active column already exists in posts</p>";
    }
    
    // Fix announcements table
    echo "<h3>Announcements Table</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM announcements LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE announcements ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER content");
        echo "<p>✓ Added is_active column to announcements table</p>";
    } else {
        echo "<p>✓ is_active column already exists in announcements</p>";
    }
    
    // Fix users table
    echo "<h3>Users Table</h3>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) AFTER role");
        echo "<p>✓ Added profile_picture column to users table</p>";
    } else {
        echo "<p>✓ profile_picture column already exists</p>";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'auth_token'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN auth_token VARCHAR(255) AFTER password");
        echo "<p>✓ Added auth_token column to users table</p>";
    } else {
        echo "<p>✓ auth_token column already exists</p>";
    }
    
    // Update existing posts to be active
    $pdo->exec("UPDATE posts SET is_active = 1 WHERE is_active IS NULL");
    echo "<p>✓ Updated existing posts to be active</p>";
    
    // Update existing announcements to be active
    $pdo->exec("UPDATE announcements SET is_active = 1 WHERE is_active IS NULL");
    echo "<p>✓ Updated existing announcements to be active</p>";
    
    echo "<h3 style='color: green;'>✓ All social feature tables fixed successfully!</h3>";
    echo "<p><strong>You can now use:</strong></p>";
    echo "<ul>";
    echo "<li>Posts API</li>";
    echo "<li>Messages API</li>";
    echo "<li>Announcements API</li>";
    echo "<li>User Discovery API</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to Home</a> | <a href='api/posts/list.php?user_id=1'>Test Posts API</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
