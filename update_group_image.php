<?php
require_once 'includes/db_config.php';

// Technology image URL from Unsplash (free to use)
$imageUrl = 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&h=400&fit=crop';
$groupName = 'Technology Enthusiasts';

try {
    // First, check if the group exists
    $stmt = $pdo->prepare("SELECT id FROM interest_groups WHERE name = ?");
    $stmt->execute([$groupName]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($group) {
        // Update the group with the image URL
        $updateStmt = $pdo->prepare("UPDATE interest_groups SET group_image = ? WHERE id = ?");
        $result = $updateStmt->execute([$imageUrl, $group['id']]);
        
        if ($result) {
            echo "Successfully updated $groupName with image: $imageUrl";
        } else {
            echo "Failed to update group image";
        }
    } else {
        echo "Group '$groupName' not found";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
