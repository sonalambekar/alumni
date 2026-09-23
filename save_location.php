<?php
session_start();
require_once 'includes/db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get latitude and longitude from POST data
$latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

if ($latitude === null || $longitude === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    exit;
}

try {
    // Enable PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, check if columns exist, if not create them
    try {
        $checkColumns = $pdo->query("SHOW COLUMNS FROM users LIKE 'latitude'");
        if ($checkColumns->rowCount() == 0) {
            // Add the new columns
            $pdo->exec("
                ALTER TABLE users 
                ADD COLUMN latitude DECIMAL(10, 8) NULL,
                ADD COLUMN longitude DECIMAL(11, 8) NULL,
                ADD COLUMN location_updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ") or die(print_r($pdo->errorInfo(), true));
        }
    } catch (PDOException $e) {
        // Log the error but continue execution
        error_log("Error checking/adding columns: " . $e->getMessage());
    }

    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Update user's location in the database
        $stmt = $pdo->prepare("UPDATE users SET latitude = :lat, longitude = :lng, location_updated_at = NOW() WHERE id = :user_id");
        $stmt->bindParam(':lat', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':lng', $longitude, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Location updated successfully',
                'coordinates' => ['lat' => $latitude, 'lng' => $longitude]
            ]);
        } else {
            // Check if the user exists
            $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $checkUser->execute([$_SESSION['user_id']]);
            
            if ($checkUser->rowCount() === 0) {
                throw new Exception('User not found');
            } else {
                // User exists but no rows were updated - this is likely because the values are the same
                $pdo->commit();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Location already up to date',
                    'coordinates' => ['lat' => $latitude, 'lng' => $longitude]
                ]);
            }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (PDOException $e) {
    error_log("Error saving location: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}
