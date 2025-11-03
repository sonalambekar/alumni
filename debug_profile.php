<?php
session_start();
require_once 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

// Get user data
$user = getCurrentUser();

// Debug information
echo "<h2>Profile Debug Information</h2>";
echo "<pre>";
echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "Profile Picture Path: " . ($user['profile_picture'] ?? 'Not set') . "\n\n";

// Check if file exists
if (!empty($user['profile_picture'])) {
    $filePath = '../' . ltrim($user['profile_picture'], '/');
    echo "File Path: $filePath\n";
    echo "File exists: " . (file_exists($filePath) ? 'Yes' : 'No') . "\n";
    echo "File size: " . (file_exists($filePath) ? filesize($filePath) . ' bytes' : 'N/A') . "\n";
    echo "Is readable: " . (is_readable($filePath) ? 'Yes' : 'No') . "\n";
    
    // Try to get image info
    if (file_exists($filePath)) {
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo !== false) {
            echo "Image Info: " . print_r($imageInfo, true) . "\n";
        } else {
            echo "Not a valid image file or corrupted.\n";
            // Check file contents
            $fileContent = file_get_contents($filePath);
            echo "First 100 bytes: " . substr($fileContent, 0, 100) . "\n";
        }
    }
}

echo "\nUser Data:\n";
print_r($user);

// Check upload directory
$uploadDir = __DIR__ . '/attachments/profile_pictures/';
echo "\n\nUpload Directory: $uploadDir\n";
if (!file_exists($uploadDir)) {
    echo "Upload directory does not exist!\n";
} else {
    echo "Upload directory exists.\n";
    echo "Is writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "\n";
    
    // List files in directory
    echo "\nFiles in upload directory:\n";
    $files = glob($uploadDir . '*');
    foreach ($files as $file) {
        echo "- " . basename($file) . " (" . filesize($file) . " bytes)\n";
    }
}

echo "</pre>";

// Show current profile picture if exists
if (!empty($user['profile_picture'])) {
    $imagePath = '../' . ltrim($user['profile_picture'], '/');
    if (file_exists($imagePath)) {
        echo "<h3>Current Profile Picture:</h3>";
        echo "<img src='$imagePath' style='max-width: 300px; border: 1px solid #ccc;' onerror='this.onerror=null; this.src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPj/HwADBwIAMCbHYQAAAABJRU5ErkJggg==\";'>";
    }
}

// Form to test direct file access
echo "<h3>Test Direct File Access:</h3>";
if (!empty($user['profile_picture'])) {
    $testUrl = '/' . ltrim($user['profile_picture'], '/');
    echo "<p>Try accessing: <a href='$testUrl' target='_blank'>$testUrl</a></p>";
    echo "<p>Or with full URL: <a href='http://" . $_SERVER['HTTP_HOST'] . $testUrl . "' target='_blank'>http://" . $_SERVER['HTTP_HOST'] . $testUrl . "</a></p>";
}
?>
