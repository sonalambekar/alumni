<?php
// Start session and include database configuration
session_start();
require_once 'includes/db_config.php';

echo "<h1>Gallery Images Debug</h1>";

try {
    // Get all galleries with their images
    $stmt = $pdo->query("
        SELECT g.id as gallery_id, g.name, g.cover_image, 
               gi.id as image_id, gi.image_path
        FROM galleries g
        LEFT JOIN gallery_images gi ON g.id = gi.gallery_id
        WHERE g.is_active = 1
        ORDER BY g.created_at DESC, gi.id
    ");
    
    $galleries = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $galleryId = $row['gallery_id'];
        if (!isset($galleries[$galleryId])) {
            $galleries[$galleryId] = [
                'name' => $row['name'],
                'cover_image' => $row['cover_image'],
                'images' => []
            ];
        }
        if (!empty($row['image_id'])) {
            $galleries[$galleryId]['images'][] = $row['image_path'];
        }
    }
    
    // Display galleries and their images
    foreach ($galleries as $galleryId => $gallery) {
        echo "<h2>" . htmlspecialchars($gallery['name']) . " (ID: $galleryId)</h2>";
        
        // Show cover image
        echo "<h3>Cover Image:</h3>";
        if (!empty($gallery['cover_image'])) {
            $coverPath = '../' . ltrim($gallery['cover_image'], '/');
            echo "<div style='margin: 10px 0;'>";
            echo "<div>Path: " . htmlspecialchars($coverPath) . "</div>";
            echo "<div>Exists: " . (file_exists($coverPath) ? 'Yes' : 'No') . "</div>";
            echo "<img src='" . htmlspecialchars($coverPath) . "' style='max-width: 300px; max-height: 200px; border: 1px solid #ccc; margin: 5px 0;'>";
            echo "</div>";
        } else {
            echo "<p>No cover image set</p>";
        }
        
        // Show other images
        if (!empty($gallery['images'])) {
            echo "<h3>Gallery Images:</h3><div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
            foreach ($gallery['images'] as $imagePath) {
                $fullPath = '../' . ltrim($imagePath, '/');
                echo "<div style='border: 1px solid #ddd; padding: 5px; margin: 5px;'>";
                echo "<div>" . htmlspecialchars($imagePath) . "</div>";
                echo "<div>Exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "</div>";
                echo "<img src='" . htmlspecialchars($fullPath) . "' style='max-width: 200px; max-height: 150px;'>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>No images found in this gallery</p>";
        }
        
        echo "<hr style='margin: 20px 0; border: 0; border-top: 1px solid #ccc;'>";
    }
    
    if (empty($galleries)) {
        echo "<p>No active galleries found in the database.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h3 style='color:red;'>Error:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
