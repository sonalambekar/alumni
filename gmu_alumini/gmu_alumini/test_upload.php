<?php
echo "<html><head><title>Test Upload</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>📤 Upload Test</h1>";

// Check uploads directory
$uploadDir = __DIR__ . '/uploads/';
echo "<h2>Directory Check</h2>";
echo "<p><strong>Upload directory:</strong> $uploadDir</p>";

if (file_exists($uploadDir)) {
    echo "<p class='success'>✅ Upload directory exists</p>";
    
    if (is_writable($uploadDir)) {
        echo "<p class='success'>✅ Upload directory is writable</p>";
    } else {
        echo "<p class='error'>❌ Upload directory is NOT writable</p>";
        echo "<p class='info'>💡 Try running: chmod 755 " . $uploadDir . "</p>";
    }
    
    // List existing files
    $files = scandir($uploadDir);
    $files = array_diff($files, array('.', '..'));
    
    if (empty($files)) {
        echo "<p class='info'>📁 Upload directory is empty</p>";
    } else {
        echo "<p class='info'>📁 Files in upload directory:</p>";
        echo "<ul>";
        foreach ($files as $file) {
            $filePath = $uploadDir . $file;
            $size = filesize($filePath);
            echo "<li>$file (" . number_format($size) . " bytes)</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p class='error'>❌ Upload directory does NOT exist</p>";
    echo "<p class='info'>💡 Creating upload directory...</p>";
    
    if (mkdir($uploadDir, 0755, true)) {
        echo "<p class='success'>✅ Upload directory created successfully</p>";
    } else {
        echo "<p class='error'>❌ Failed to create upload directory</p>";
    }
}

// Check PHP upload settings
echo "<h2>PHP Upload Settings</h2>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . " seconds</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";

// Test API endpoint
echo "<h2>API Test</h2>";
echo "<p><a href='api/upload_media.php' target='_blank'>Test Upload API: api/upload_media.php</a></p>";

// Simple upload form for testing
echo "<h2>Test Upload Form</h2>";
echo "<form action='api/upload_media.php' method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='media' accept='image/*' required>";
echo "<br><br>";
echo "<input type='submit' value='Test Upload'>";
echo "</form>";

echo "</div></body></html>";
?>
