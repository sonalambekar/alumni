<?php
require_once 'includes/db_config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

try {
    // Check if users table has location data
    $query = "
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN 1 ELSE 0 END) as users_with_coords,
            SUM(CASE WHEN location IS NOT NULL THEN 1 ELSE 0 END) as users_with_point
        FROM users
    ";
    
    $result = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);
    
    echo "=== Database Status ===\n";
    echo "Total users: " . $result['total_users'] . "\n";
    echo "Users with lat/lng coordinates: " . $result['users_with_coords'] . "\n";
    echo "Users with POINT location: " . $result['users_with_point'] . "\n\n";
    
    // Test the get_user_locations API
    echo "=== Testing API Endpoint ===\n";
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/alumni/api/get_user_locations.php';
    echo "API URL: $apiUrl\n";
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "HTTP Status: $httpCode\n";
    echo "Response: \n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "Found " . count($data['users'] ?? []) . " users with locations\n";
            if (!empty($data['users'])) {
                echo "First user: " . print_r($data['users'][0], true) . "\n";
            }
        } else {
            echo "Invalid JSON response: " . json_last_error_msg() . "\n";
            echo "Raw response: " . $response . "\n";
        }
    } else {
        echo "Error response: " . $response . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "SQL: " . ($e->queryString ?? 'N/A') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
