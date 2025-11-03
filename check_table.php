<?php
require_once 'includes/db_config.php';

try {
    // Check if jobs table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'jobs'");
    if ($tableCheck->rowCount() === 0) {
        die("Jobs table does not exist.\n");
    }
    
    echo "Jobs table exists.\n\n";
    
    // Get column information
    $columns = $pdo->query("DESCRIBE jobs")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in jobs table:\n";
    echo str_pad("Field", 20) . str_pad("Type", 30) . str_pad("Null", 8) . str_pad("Key", 8) . str_pad("Default", 15) . "Extra\n";
    echo str_repeat("-", 85) . "\n";
    
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 20) . 
             str_pad($col['Type'], 30) . 
             str_pad($col['Null'], 8) . 
             str_pad($col['Key'], 8) . 
             str_pad(($col['Default'] ?? 'NULL'), 15) . 
             $col['Extra'] . "\n";
    }
    
    // Check if table has any data
    $count = $pdo->query("SELECT COUNT(*) as count FROM jobs")->fetch(PDO::FETCH_ASSOC);
    echo "\nNumber of jobs: " . $count['count'] . "\n";
    
    // Show first few records if they exist
    if ($count['count'] > 0) {
        $jobs = $pdo->query("SELECT * FROM jobs LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nSample job records:\n";
        print_r($jobs);
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
