<?php
// Database Schema Update Script for Institute Filtering
// Run this script ONCE to add institute filtering columns

require_once "config/database.php";

echo "<h2>GMU Alumni - Database Schema Update</h2>";
echo "<p>Adding institute filtering columns to announcements and posts tables...</p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h3>Step 1: Adding columns to announcements table</h3>";
    
    // Check if columns already exist
    $check_announcements = "SHOW COLUMNS FROM announcements LIKE 'target_institute'";
    $stmt = $db->prepare($check_announcements);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add columns to announcements table
        $alter_announcements = "ALTER TABLE announcements 
                               ADD COLUMN target_institute VARCHAR(255) NULL AFTER content,
                               ADD COLUMN is_global BOOLEAN DEFAULT FALSE AFTER target_institute";
        $db->exec($alter_announcements);
        echo "✅ Added target_institute and is_global columns to announcements table<br>";
    } else {
        echo "ℹ️ Columns already exist in announcements table<br>";
    }
    
    echo "<h3>Step 2: Adding columns to posts table</h3>";
    
    // Check if columns already exist in posts table
    $check_posts = "SHOW COLUMNS FROM posts LIKE 'target_institute'";
    $stmt = $db->prepare($check_posts);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add columns to posts table
        $alter_posts = "ALTER TABLE posts 
                       ADD COLUMN target_institute VARCHAR(255) NULL AFTER status,
                       ADD COLUMN is_global BOOLEAN DEFAULT FALSE AFTER target_institute";
        $db->exec($alter_posts);
        echo "✅ Added target_institute and is_global columns to posts table<br>";
    } else {
        echo "ℹ️ Columns already exist in posts table<br>";
    }
    
    echo "<h3>Step 3: Adding indexes for performance</h3>";
    
    // Add indexes (ignore errors if they already exist)
    try {
        $db->exec("CREATE INDEX idx_announcements_target_institute ON announcements(target_institute)");
        echo "✅ Added index on announcements.target_institute<br>";
    } catch (Exception $e) {
        echo "ℹ️ Index on announcements.target_institute already exists<br>";
    }
    
    try {
        $db->exec("CREATE INDEX idx_announcements_is_global ON announcements(is_global)");
        echo "✅ Added index on announcements.is_global<br>";
    } catch (Exception $e) {
        echo "ℹ️ Index on announcements.is_global already exists<br>";
    }
    
    try {
        $db->exec("CREATE INDEX idx_posts_target_institute ON posts(target_institute)");
        echo "✅ Added index on posts.target_institute<br>";
    } catch (Exception $e) {
        echo "ℹ️ Index on posts.target_institute already exists<br>";
    }
    
    try {
        $db->exec("CREATE INDEX idx_posts_is_global ON posts(is_global)");
        echo "✅ Added index on posts.is_global<br>";
    } catch (Exception $e) {
        echo "ℹ️ Index on posts.is_global already exists<br>";
    }
    
    echo "<h3>Step 4: Setting existing content as global (backward compatibility)</h3>";
    
    // Update existing announcements to be global
    $update_announcements = "UPDATE announcements SET is_global = TRUE WHERE target_institute IS NULL";
    $stmt = $db->prepare($update_announcements);
    $stmt->execute();
    $announcements_updated = $stmt->rowCount();
    echo "✅ Updated $announcements_updated existing announcements to be global<br>";
    
    // Update existing posts to be global
    $update_posts = "UPDATE posts SET is_global = TRUE WHERE target_institute IS NULL";
    $stmt = $db->prepare($update_posts);
    $stmt->execute();
    $posts_updated = $stmt->rowCount();
    echo "✅ Updated $posts_updated existing posts to be global<br>";
    
    echo "<h3>Step 5: Verification</h3>";
    
    // Verify announcements table structure
    echo "<h4>Announcements Table Structure:</h4>";
    $describe_announcements = "DESCRIBE announcements";
    $stmt = $db->prepare($describe_announcements);
    $stmt->execute();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verify posts table structure
    echo "<h4>Posts Table Structure:</h4>";
    $describe_posts = "DESCRIBE posts";
    $stmt = $db->prepare($describe_posts);
    $stmt->execute();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show statistics
    echo "<h4>Content Statistics:</h4>";
    
    $stats_announcements = "SELECT 
                               COUNT(*) as total_announcements,
                               SUM(CASE WHEN is_global = 1 THEN 1 ELSE 0 END) as global_announcements,
                               SUM(CASE WHEN is_global = 0 THEN 1 ELSE 0 END) as institute_specific_announcements
                           FROM announcements";
    $stmt = $db->prepare($stats_announcements);
    $stmt->execute();
    $ann_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stats_posts = "SELECT 
                       COUNT(*) as total_posts,
                       SUM(CASE WHEN is_global = 1 THEN 1 ELSE 0 END) as global_posts,
                       SUM(CASE WHEN is_global = 0 THEN 1 ELSE 0 END) as institute_specific_posts
                   FROM posts";
    $stmt = $db->prepare($stats_posts);
    $stmt->execute();
    $post_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Announcements:</strong> {$ann_stats['total_announcements']} total, {$ann_stats['global_announcements']} global, {$ann_stats['institute_specific_announcements']} institute-specific</p>";
    echo "<p><strong>Posts:</strong> {$post_stats['total_posts']} total, {$post_stats['global_posts']} global, {$post_stats['institute_specific_posts']} institute-specific</p>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✅ Database Update Completed Successfully!</h3>";
    echo "<p style='color: #155724; margin-bottom: 0;'>Your database now supports institute-based filtering. The updated API files are ready to use.</p>";
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Test the updated API endpoints with your Flutter app</li>";
    echo "<li>Verify that posts and announcements are filtered correctly by institute</li>";
    echo "<li>You can now delete this update script file for security</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Database Update Failed</h3>";
    echo "<p style='color: #721c24;'>Error: " . $e->getMessage() . "</p>";
    echo "<p style='color: #721c24; margin-bottom: 0;'>Please check your database connection and try again.</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
}

table {
    width: 100%;
    background: white;
}

th {
    background-color: #e9ecef;
    padding: 8px;
    text-align: left;
}

td {
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
}

h2, h3, h4 {
    color: #495057;
}
</style>
