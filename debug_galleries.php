<?php
// Start session and include database configuration
session_start();
require_once 'includes/db_config.php';

echo "<h1>Debug Gallery Data</h1>";

try {
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES LIKE 'galleries'");
    $galleriesTableExists = $tables->rowCount() > 0;
    
    $tables = $pdo->query("SHOW TABLES LIKE 'gallery_images'");
    $imagesTableExists = $tables->rowCount() > 0;
    
    echo "<h3>Table Status:</h3>";
    echo "galleries table exists: " . ($galleriesTableExists ? 'Yes' : 'No') . "<br>";
    echo "gallery_images table exists: " . ($imagesTableExists ? 'Yes' : 'No') . "<br>";
    
    if ($galleriesTableExists) {
        // Get galleries count
        $count = $pdo->query("SELECT COUNT(*) as count FROM galleries")->fetch();
        echo "<h3>Galleries Count: " . $count['count'] . "</h3>";
        
        // Get sample data
        $galleries = $pdo->query("SELECT * FROM galleries LIMIT 5")->fetchAll();
        
        echo "<h3>Sample Gallery Data:</h3>";
        echo "<pre>";
        print_r($galleries);
        echo "</pre>";
        
        // Check if there are any images
        if ($imagesTableExists) {
            $images = $pdo->query("SELECT * FROM gallery_images LIMIT 5")->fetchAll();
            echo "<h3>Sample Image Data:</h3>";
            echo "<pre>";
            print_r($images);
            echo "</pre>";
        }
    }
    
} catch (PDOException $e) {
    echo "<h3 style='color:red;'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<h4>Connection Details:</h4>";
    echo "<pre>" . print_r($pdo->getAvailableDrivers(), true) . "</pre>";
}

// Show the actual query being used
echo "<h3>Query Being Used:</h3>";
$query = "SELECT g.*, 
         (SELECT image_path FROM gallery_images WHERE gallery_id = g.id LIMIT 1) as cover_image,
         (SELECT COUNT(*) FROM gallery_images WHERE gallery_id = g.id) as image_count
         FROM galleries g 
         ORDER BY g.created_at DESC";
echo "<pre>" . htmlspecialchars($query) . "</pre>";
?>
