<?php
require_once 'includes/db_config.php';

// Get the first group to check its structure
$stmt = $pdo->query("SELECT * FROM interest_groups LIMIT 1");
$group = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
echo "Group Data Structure:\n";
print_r($group);

echo "\nFirst 5 Groups:\n";
$groups = $pdo->query("SELECT id, name, group_image FROM interest_groups LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($groups);

echo "\nUser's Groups:\n";
$userGroups = $pdo->prepare("
    SELECT g.id, g.name, g.group_image 
    FROM interest_groups g
    JOIN group_members gm ON g.id = gm.group_id
    WHERE gm.user_id = ? AND gm.is_active = 1 AND g.is_active = 1
    LIMIT 5
");
$userGroups->execute([$_SESSION['user_id'] ?? 0]);
print_r($userGroups->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
