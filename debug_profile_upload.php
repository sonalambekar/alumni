<?php
session_start();
require_once 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please log in first.');
}

// Get current user
$user = getCurrentUser();
if (!$user) {
    die('User not found.');
}

echo "<h2>Profile Picture Upload Debug</h2>";
echo "<pre>";

echo "=== Current User ===\n";
echo "User ID: " . $user['id'] . "\n";
echo "Current Profile Picture: " . ($user['profile_picture'] ?? 'Not set') . "\n\n";

// Check directory permissions
$uploadDir = __DIR__ . '/attachments/profile_pictures/';
$relativeDir = 'attachments/profile_pictures/';

echo "=== Directory Check ===\n";
echo "Upload Directory: " . $uploadDir . "\n";

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    echo "Directory doesn't exist. Attempting to create... ";
    if (mkdir($uploadDir, 0775, true)) {
        echo "Success!\n";
    } else {
        echo "Failed!\n";
        echo "Error: Could not create directory. Check permissions.\n";
    }
} else {
    echo "Directory exists.\n";
}

echo "Directory is writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "\n\n";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "=== Upload Attempt ===\n";
    
    if (isset($_FILES['profile_picture'])) {
        $file = $_FILES['profile_picture'];
        
        echo "File Info:\n";
        echo "- Name: " . $file['name'] . "\n";
        echo "- Type: " . $file['type'] . "\n";
        echo "- Size: " . $file['size'] . " bytes\n";
        echo "- Temp Name: " . $file['tmp_name'] . "\n";
        echo "- Error: " . $file['error'] . "\n";
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Generate a safe filename
            $originalName = preg_replace('/[^\w\-\.]/', '_', $file['name']);
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = 'profile_' . $user['id'] . '_' . time() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                $relativePath = $relativeDir . $fileName;
                
                echo "\nAttempting to move uploaded file...\n";
                echo "- Source: " . $file['tmp_name'] . "\n";
                echo "- Target: " . $targetPath . "\n";
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    echo "File uploaded successfully!\n";
                    
                    // Update database
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                        if ($stmt->execute([$relativePath, $user['id']])) {
                            echo "Database updated successfully!\n";
                            
                            // Update session
                            $_SESSION['user_avatar'] = $relativePath;
                            
                            echo "\n=== Updated User Data ===\n";
                            $updatedUser = getCurrentUser();
                            echo "Profile Picture: " . ($updatedUser['profile_picture'] ?? 'Not set') . "\n";
                            
                            // Show image if it exists
                            if (file_exists($targetPath)) {
                                echo "\n=== Uploaded Image ===\n";
                                echo "<img src='../$relativePath' style='max-width: 200px; margin-top: 20px; border: 1px solid #ddd; padding: 5px;'>\n";
                            } else {
                                echo "\nWarning: File was saved but cannot be found at: $targetPath\n";
                            }
                        } else {
                            echo "Error updating database: " . implode(" ", $stmt->errorInfo()) . "\n";
                        }
                    } catch (PDOException $e) {
                        echo "Database Error: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "Error moving uploaded file. Check directory permissions.\n";
                    echo "Upload error: " . print_r(error_get_last(), true) . "\n";
                }
            } else {
                echo "Error: Only JPG, JPEG, PNG & GIF files are allowed.\n";
            }
        } else {
            echo "Upload error: " . $file['error'] . " - " . getUploadError($file['error']) . "\n";
        }
    } else {
        echo "No file was uploaded.\n";
    }
}

function getUploadError($code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
    ];
    
    return $errors[$code] ?? 'Unknown upload error';
}
?>

<h3>Test Profile Picture Upload</h3>
<form method="POST" enctype="multipart/form-data">
    <div style="margin: 20px 0;">
        <input type="file" name="profile_picture" accept="image/*">
    </div>
    <div>
        <button type="submit" style="padding: 8px 15px; background: #5b1f1f; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Upload Test Image
        </button>
    </div>
</form>

<h3>Current Profile Picture</h3>
<?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])): ?>
    <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" style="max-width: 200px; margin-top: 10px; border: 1px solid #ddd; padding: 5px;">
    <p><?php echo htmlspecialchars($user['profile_picture']); ?></p>
<?php else: ?>
    <p>No profile picture set or file not found.</p>
    <?php if (!empty($user['profile_picture'])): ?>
        <p>Expected path: <?php echo htmlspecialchars(__DIR__ . '/../' . $user['profile_picture']); ?></p>
    <?php endif; ?>
<?php endif; ?>
