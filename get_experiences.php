<?php
require_once 'includes/db_config.php';

try {
    // Get all approved experiences, ordered by creation date (newest first)
    $stmt = $pdo->prepare("SELECT * FROM experiences WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 20");
    $stmt->execute();
    $experiences = $stmt->fetchAll();

    // Return as JSON
    header('Content-Type: application/json');
    echo json_encode($experiences);

} catch (Exception $e) {
    error_log('Error fetching experiences: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch experiences']);
}
?>
