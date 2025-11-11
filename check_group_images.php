<?php
require_once 'includes/db_config.php';

// Check if attachments directory exists
$attachmentsDir = __DIR__ . '/attachments/groups/';
$attachmentsExist = is_dir($attachmentsDir);

// Get all groups
$groups = $pdo->query("SELECT id, name, group_image FROM interest_groups")->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Group Images Status</h2>";
echo "<p>Attachments directory exists: " . ($attachmentsExist ? 'Yes' : 'No') . "</p>";

if ($attachmentsExist) {
    echo "<p>Contents of attachments/groups directory:</p>";
    $files = scandir($attachmentsDir);
    echo "<pre>";
    print_r($files);
    echo "</pre>";
}

echo "<h3>Groups in Database</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>Status</th></tr>";

foreach ($groups as $group) {
    $status = 'No image';
    $path = '';
    
    if (!empty($group['group_image'])) {
        $path = $group['group_image'];
        $fullPath = __DIR__ . '/' . ltrim($path, '/');
        $status = file_exists($fullPath) ? 'Image exists' : 'Image not found';
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($group['id']) . "</td>";
    echo "<td>" . htmlspecialchars($group['name']) . "</td>";
    echo "<td>" . htmlspecialchars($path) . "</td>";
    echo "<td>" . $status . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
