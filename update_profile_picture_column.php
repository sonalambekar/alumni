<?php
require_once 'includes/db_config.php';

try {
    // Add profile_picture column if it doesn't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL AFTER email");
    echo "Successfully added profile_picture column to users table.\n";
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/uploads/profile_pictures';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "Created uploads directory at: $uploadDir\n";
    } else {
        echo "Uploads directory already exists at: $uploadDir\n";
    }
    
    echo "Profile picture setup completed successfully!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

echo "<a href='/alumni/pages/profile.php'>Go to Profile</a>";
?>
