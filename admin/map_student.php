<?php
header('Content-Type: application/json');
require_once '../includes/db_config.php';
require_once 'includes/admin_auth.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$mentor_id = isset($_POST['mentor_id']) ? intval($_POST['mentor_id']) : 0;
$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;

// Validate input
if ($mentor_id <= 0 || $student_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid mentor or student ID']);
    exit();
}

try {
    // Check if mapping already exists
    $checkStmt = $pdo->prepare("SELECT id FROM mentor_student_mapping WHERE mentor_id = ? AND student_id = ?");
    $checkStmt->execute([$mentor_id, $student_id]);
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This student is already mapped to the mentor']);
        exit();
    }
    
    // Insert new mapping
    $insertStmt = $pdo->prepare("INSERT INTO mentor_student_mapping (mentor_id, student_id, status, created_at) VALUES (?, ?, 'active', NOW())");
    $insertStmt->execute([$mentor_id, $student_id]);
    
    if ($insertStmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Student mapped to mentor successfully',
            'mapping_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to map student to mentor']);
    }
    
} catch (PDOException $e) {
    error_log('Database error in map_student.php: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error occurred. Please try again.'
    ]);
}
?>
