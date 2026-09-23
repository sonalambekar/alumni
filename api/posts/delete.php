<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../../includes/db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->post_id) || !isset($data->user_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID and User ID required']);
    exit();
}

try {
    $post_id = (int)$data->post_id;
    $user_id = (int)$data->user_id;

    // First check if the user is the owner of the post
    $check_query = "SELECT user_id FROM posts WHERE id = :post_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $post = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit();
    }

    if ((int)$post['user_id'] !== $user_id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized to delete this post']);
        exit();
    }

    // Soft delete by setting is_active = 0
    $query = "UPDATE posts SET is_active = 0 WHERE id = :post_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
