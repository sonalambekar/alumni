<?php
require_once 'includes/db_config.php';

// Find the "Free" group
$stmt = $pdo->prepare("SELECT id, name, group_image FROM interest_groups WHERE name LIKE '%Free%'");
$stmt->execute();
$group = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
if ($group) {
    echo "Group found:\n";
    print_r($group);
    
    // Check if image exists
    if (!empty($group['group_image'])) {
        $imagePath = __DIR__ . '/' . ltrim($group['group_image'], '/');
        echo "\nImage path: " . $imagePath . "\n";
        echo "Image exists: " . (file_exists($imagePath) ? 'Yes' : 'No') . "\n";
    } else {
        echo "\nNo image set for this group.\n";
    }
} else {
    echo "No group with 'Free' in the name found.\n";
    
    // List all groups for reference
    echo "\nAll groups:\n";
    $groups = $pdo->query("SELECT id, name, group_image FROM interest_groups")->fetchAll(PDO::FETCH_ASSOC);
    print_r($groups);
}
echo "</pre>";
?>
