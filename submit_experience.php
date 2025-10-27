<?php
session_start();
require_once 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Get form data
$title = trim($_POST['title']);
$content = trim($_POST['content']);
$category = $_POST['category'];

if (empty($title) || empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit();
}

// Insert experience
try {
    $stmt = $pdo->prepare("INSERT INTO experiences (title, content, category, author_id, author_name) VALUES (?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $content, $category, $user_id, $user['full_name']]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Experience shared successfully']);
    } else {
        throw new Exception('Failed to insert experience');
    }
} catch (Exception $e) {
    error_log('Error sharing experience: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to share experience']);
}
?>
