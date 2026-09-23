<?php
// Check Uploads Directory and Files Script

echo "<h2>GMU Alumni - Check Uploads Directory</h2>";

// Check if uploads directory exists
$uploadsDir = __DIR__ . '/uploads';
echo "<h3>Uploads Directory Check</h3>";
echo "<p><strong>Directory Path:</strong> $uploadsDir</p>";

if (is_dir($uploadsDir)) {
    echo "<p>✅ Uploads directory exists</p>";
    
    // List all files in uploads directory
    $files = scandir($uploadsDir);
    $mediaFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && is_file(__DIR__ . '/uploads/' . $file);
    });
    
    echo "<h3>Media Files in Uploads Directory</h3>";
    if (count($mediaFiles) > 0) {
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Filename</th><th>Size</th><th>Type</th><th>Modified</th><th>Access URL</th></tr>";
        
        foreach ($mediaFiles as $file) {
            $filePath = $uploadsDir . '/' . $file;
            $fileSize = filesize($filePath);
            $fileType = pathinfo($file, PATHINFO_EXTENSION);
            $modified = date('Y-m-d H:i:s', filemtime($filePath));
            
            echo "<tr>";
            echo "<td>$file</td>";
            echo "<td>" . number_format($fileSize) . " bytes</td>";
            echo "<td>$fileType</td>";
            echo "<td>$modified</td>";
            echo "<td><a href='uploads/$file' target='_blank'>View File</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>Total Files:</strong> " . count($mediaFiles) . "</p>";
    } else {
        echo "<p>⚠️ No media files found in uploads directory</p>";
    }
} else {
    echo "<p>❌ Uploads directory does not exist</p>";
    echo "<p>Creating uploads directory...</p>";
    
    if (mkdir($uploadsDir, 0755, true)) {
        echo "<p>✅ Uploads directory created successfully</p>";
    } else {
        echo "<p>❌ Failed to create uploads directory</p>";
    }
}

// Check database for media URLs
require_once "config/database.php";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h3>Database Media URLs Check</h3>";
    
    $query = "SELECT id, media_url, media_type, created_at FROM posts WHERE media_type = 'image' AND media_url IS NOT NULL ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr><th>Post ID</th><th>Media URL</th><th>File Exists</th><th>Created At</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mediaUrl = $row['media_url'];
        $filePath = $uploadsDir . '/' . $mediaUrl;
        $fileExists = file_exists($filePath) ? '✅ Yes' : '❌ No';
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>$mediaUrl</td>";
        echo "<td>$fileExists</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
table { border-collapse: collapse; margin: 20px 0; }
th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>";
?>
