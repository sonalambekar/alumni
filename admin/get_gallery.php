<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once '../includes/db_config.php';

$galleryId = $_GET['id'] ?? 0;

if (empty($galleryId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Gallery ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM galleries WHERE id = ?");
    $stmt->execute([$galleryId]);
    $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gallery) {
        http_response_code(404);
        echo json_encode(['error' => 'Gallery not found']);
        exit();
    }
    
    header('Content-Type: application/json');
    echo json_encode($gallery);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
