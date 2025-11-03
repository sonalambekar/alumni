<?php
session_start();
require_once 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

echo "<h2>Upload Test</h2>";

// Test directory
$uploadDir = __DIR__ . '/attachments/profile_pictures/';
echo "<p>Upload directory: " . htmlspecialchars($uploadDir) . "</p>";

// Check if directory exists and is writable
if (!file_exists($uploadDir)) {
    echo "<p style='color: orange;'>Directory doesn't exist. Attempting to create...</p>";
    if (!mkdir($uploadDir, 0775, true)) {
        die("<p style='color: red;'>Failed to create directory. Please create it manually and set proper permissions.</p>");
    }
    echo "<p style='color: green;'>Directory created successfully.</p>";
}

if (!is_writable($uploadDir)) {
    echo "<p style='color: red;'>Directory is not writable. Please set proper permissions (chmod 775).</p>";
} else {
    echo "<p style='color: green;'>Directory is writable.</p>";
}

// Test form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<h3>Upload Test Results:</h3>";
    
    $file = $_FILES['test_image'];
    echo "<p>File name: " . htmlspecialchars($file['name']) . "</p>";
    echo "<p>File size: " . $file['size'] . " bytes</p>";
    echo "<p>File type: " . $file['type'] . "</p>";
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "<p style='color: red;'>Upload error: " . $file['error'] . "</p>";
    } else {
        $fileName = 'test_' . time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo "<p style='color: green;'>File uploaded successfully to: " . htmlspecialchars($targetPath) . "</p>";
            echo "<p>File URL: <a href='" . "/alumni/attachments/profile_pictures/" . $fileName . "' target='_blank'>" . 
                 htmlspecialchars($fileName) . "</a></p>";
            
            // Test if file is accessible
            $fileUrl = '/alumni/attachments/profile_pictures/' . $fileName;
            $headers = @get_headers('http://' . $_SERVER['HTTP_HOST'] . $fileUrl);
            if ($headers && strpos($headers[0], '200')) {
                echo "<p style='color: green;'>File is accessible via web server.</p>";
                echo "<img src='$fileUrl' style='max-width: 200px;' alt='Test Image'>";
            } else {
                echo "<p style='color: red;'>File uploaded but not accessible via web server. Check .htaccess or server configuration.</p>";
            }
        } else {
            echo "<p style='color: red;'>Failed to move uploaded file.</p>";
            echo "<p>Error: " . error_get_last()['message'] . "</p>";
        }
    }
}
?>

<h3>Test File Upload</h3>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_image" accept="image/*">
    <button type="submit">Test Upload</button>
</form>

<h3>Current Directory Contents:</h3>
<?php
$files = glob($uploadDir . '*');
echo "<ul>";
foreach ($files as $file) {
    $filename = basename($file);
    $fileUrl = '/alumni/attachments/profile_pictures/' . $filename;
    echo "<li>";
    echo "<a href='$fileUrl' target='_blank'>$filename</a> (" . 
         number_format(filesize($file)) . " bytes, " . 
         date("Y-m-d H:i:s", filemtime($file)) . 
         ") <img src='$fileUrl' style='max-width: 50px; vertical-align: middle;'>";
    echo "</li>";
}
echo "</ul>";
?>
