<?php
require_once 'includes/db_config.php';

header('Content-Type: text/plain');
echo "Checking medals table...\n\n";

try {
    // Check connection
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!\n\n";
    
    // Check medals table
    $stmt = $pdo->query("SHOW TABLES LIKE 'medals'");
    if ($stmt->rowCount() > 0) {
        echo "Medals table exists.\n\n";
        
        // Count medals
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM medals");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total medals in table: " . $count['count'] . "\n\n";
        
        // Show sample data
        echo "Sample medal records (max 5):\n";
        $stmt = $pdo->query("SELECT * FROM medals LIMIT 5");
        $medals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($medals);
        
        // Check the query from institute_medal.php
        echo "\n\nTesting the actual query from institute_medal.php:\n";
        $query = "SELECT m.*, u.profile_picture as user_profile_image, 
                         u.current_city, u.current_country, u.bio, u.linkedin_url
                  FROM medals m
                  LEFT JOIN users u ON m.user_id = u.id
                  WHERE m.status = 'active'
                  ORDER BY m.awarded_date DESC";
        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Number of results: " . count($results) . "\n";
        print_r($results);
        
    } else {
        echo "ERROR: Medals table does not exist!\n";
    }
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

// Check if the default avatar exists
$defaultAvatar = 'assets/images/default-avatar.png';
echo "\nChecking default avatar at: " . $defaultAvatar . "\n";
echo "File exists: " . (file_exists($defaultAvatar) ? 'Yes' : 'No') . "\n";
?>
