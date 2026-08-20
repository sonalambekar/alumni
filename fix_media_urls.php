<?php
// Fix Media URLs Script
// This script will clean up any posts that have JSON strings stored as media URLs

require_once "config/database.php";

echo "<h2>GMU Alumni - Fix Media URLs</h2>";
echo "<p>Cleaning up posts with JSON strings as media URLs...</p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Find posts with JSON strings as media URLs
    $query = "SELECT id, media_url FROM posts WHERE media_url LIKE '{%' OR media_url LIKE '%success%'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $postsToFix = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $postsToFix[] = $row;
    }
    
    echo "<h3>Found " . count($postsToFix) . " posts with JSON media URLs</h3>";
    
    if (count($postsToFix) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr><th>Post ID</th><th>Current Media URL</th><th>Fixed Media URL</th><th>Status</th></tr>";
        
        $fixedCount = 0;
        
        foreach ($postsToFix as $post) {
            $postId = $post['id'];
            $currentUrl = $post['media_url'];
            $fixedUrl = $currentUrl;
            $status = 'No change needed';
            
            // Try to extract URL from JSON
            $cleanUrl = $currentUrl;
            // Remove leading backtick if present
            if (strpos($cleanUrl, '`') === 0) {
                $cleanUrl = substr($cleanUrl, 1);
            }
            
            if (strpos($cleanUrl, '{') === 0) {
                try {
                    $decoded = json_decode($cleanUrl, true);
                    if (isset($decoded['url'])) {
                        $fixedUrl = $decoded['url'];
                        
                        // Update the database
                        $updateQuery = "UPDATE posts SET media_url = :fixed_url WHERE id = :post_id";
                        $updateStmt = $db->prepare($updateQuery);
                        $updateStmt->bindParam(':fixed_url', $fixedUrl);
                        $updateStmt->bindParam(':post_id', $postId);
                        
                        if ($updateStmt->execute()) {
                            $status = '✅ Fixed';
                            $fixedCount++;
                        } else {
                            $status = '❌ Failed to update';
                        }
                    } else {
                        $status = 'No URL found in JSON';
                    }
                } catch (Exception $e) {
                    $status = 'JSON decode failed';
                }
            }
            
            echo "<tr>";
            echo "<td>$postId</td>";
            echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($currentUrl, 0, 100)) . (strlen($currentUrl) > 100 ? '...' : '') . "</td>";
            echo "<td>" . htmlspecialchars($fixedUrl) . "</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h3 style='color: #155724; margin-top: 0;'>✅ Media URL Cleanup Completed!</h3>";
        echo "<p style='color: #155724;'>Fixed $fixedCount out of " . count($postsToFix) . " posts with JSON media URLs.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h3 style='color: #0c5460; margin-top: 0;'>ℹ️ No Issues Found</h3>";
        echo "<p style='color: #0c5460; margin-bottom: 0;'>All media URLs are already in the correct format.</p>";
        echo "</div>";
    }
    
    // Show current media URL statistics
    echo "<h3>Media URL Statistics:</h3>";
    
    $statsQuery = "SELECT 
                      COUNT(*) as total_posts,
                      SUM(CASE WHEN media_type != 'none' THEN 1 ELSE 0 END) as posts_with_media,
                      SUM(CASE WHEN media_url IS NOT NULL AND media_url != '' THEN 1 ELSE 0 END) as posts_with_media_url
                   FROM posts";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Posts:</strong> {$stats['total_posts']}</p>";
    echo "<p><strong>Posts with Media:</strong> {$stats['posts_with_media']}</p>";
    echo "<p><strong>Posts with Media URL:</strong> {$stats['posts_with_media_url']}</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Test creating new posts with media to ensure URLs are stored correctly</li>";
    echo "<li>Verify that existing posts with media display properly in the app</li>";
    echo "<li>You can delete this fix script file after confirming everything works</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Database Error</h3>";
    echo "<p style='color: #721c24;'>Error: " . $e->getMessage() . "</p>";
    echo "<p style='color: #721c24; margin-bottom: 0;'>Please check your database connection and try again.</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
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
    vertical-align: top;
}

h2, h3, h4 {
    color: #495057;
}
</style>
