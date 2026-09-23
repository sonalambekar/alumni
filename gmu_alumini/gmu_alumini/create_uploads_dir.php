<?php
echo "<html><head><title>Create Uploads Directory</title></head><body>";
echo "<h1>📁 Creating Uploads Directory</h1>";

$uploadDir = __DIR__ . '/uploads/';

echo "<p><strong>Target directory:</strong> $uploadDir</p>";

if (file_exists($uploadDir)) {
    echo "<p style='color: green;'>✅ Directory already exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Directory does not exist, creating...</p>";
    
    if (mkdir($uploadDir, 0755, true)) {
        echo "<p style='color: green;'>✅ Directory created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create directory</p>";
        echo "<p>You may need to create it manually or check permissions</p>";
    }
}

// Check permissions
if (file_exists($uploadDir)) {
    $perms = fileperms($uploadDir);
    $permsOctal = substr(sprintf('%o', $perms), -4);
    echo "<p><strong>Current permissions:</strong> $permsOctal</p>";
    
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✅ Directory is writable</p>";
    } else {
        echo "<p style='color: red;'>❌ Directory is NOT writable</p>";
        echo "<p>Try running: <code>chmod 755 $uploadDir</code></p>";
        
        // Try to fix permissions
        if (chmod($uploadDir, 0755)) {
            echo "<p style='color: green;'>✅ Permissions fixed automatically</p>";
        } else {
            echo "<p style='color: red;'>❌ Could not fix permissions automatically</p>";
        }
    }
}

// Create a test file
if (file_exists($uploadDir) && is_writable($uploadDir)) {
    $testFile = $uploadDir . 'test.txt';
    if (file_put_contents($testFile, 'Test file created at ' . date('Y-m-d H:i:s'))) {
        echo "<p style='color: green;'>✅ Test file created successfully</p>";
        unlink($testFile); // Clean up
        echo "<p style='color: green;'>✅ Test file deleted - directory is working</p>";
    } else {
        echo "<p style='color: red;'>❌ Could not create test file</p>";
    }
}

echo "</body></html>";
?>
