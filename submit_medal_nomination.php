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

// Get form data
$nominee_name = trim($_POST['nominee_name']);
$nominee_email = trim($_POST['nominee_email']);
$nominee_graduation_year = !empty($_POST['nominee_graduation_year']) ? intval($_POST['nominee_graduation_year']) : NULL;
$nominee_degree = trim($_POST['nominee_degree']);
$category = $_POST['category'];
$reason = trim($_POST['reason']);
$achievements = trim($_POST['achievements']);
$nominator_name = trim($_POST['nominator_name']);
$nominator_email = trim($_POST['nominator_email']);
$nominator_phone = trim($_POST['nominator_phone']);
$nominator_relationship = trim($_POST['nominator_relationship']);

if (empty($nominee_name) || empty($nominee_email) || empty($category) || empty($reason) || empty($nominator_name) || empty($nominator_email) || empty($nominator_relationship)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit();
}

// Insert medal nomination
try {
    $stmt = $pdo->prepare("INSERT INTO medal_nominations (nominee_name, nominee_email, nominee_graduation_year, nominee_degree, category, reason, achievements, nominator_name, nominator_email, nominator_phone, nominator_relationship) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$nominee_name, $nominee_email, $nominee_graduation_year, $nominee_degree, $category, $reason, $achievements, $nominator_name, $nominator_email, $nominator_phone, $nominator_relationship]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Nomination submitted successfully']);
    } else {
        throw new Exception('Failed to insert medal nomination');
    }
} catch (Exception $e) {
    error_log('Error submitting medal nomination: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit nomination']);
}
?>
