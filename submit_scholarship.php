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
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$amount = floatval($_POST['amount']);
$applicant_name = trim($_POST['applicant_name']);
$applicant_email = trim($_POST['applicant_email']);
$applicant_phone = trim($_POST['applicant_phone']);
$student_details = trim($_POST['student_details']);
$purpose = trim($_POST['purpose']);
$payment_method = trim($_POST['payment_method'] ?? '');
$payment_schedule = trim($_POST['payment_schedule'] ?? '');

if (empty($title) || empty($description) || empty($amount) || empty($applicant_name) || empty($applicant_email) || empty($student_details) || empty($purpose)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit();
}

// Insert scholarship application
try {
    $stmt = $pdo->prepare("INSERT INTO scholarship_applications (title, description, amount, applicant_name, applicant_email, applicant_phone, student_details, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $description, $amount, $applicant_name, $applicant_email, $applicant_phone, $student_details, $purpose]);

    if ($result) {
        // Send confirmation email (you can implement email functionality here)
        // For now, we'll just return success

        echo json_encode(['success' => true, 'message' => 'Scholarship application submitted successfully']);
    } else {
        throw new Exception('Failed to insert scholarship application');
    }
} catch (Exception $e) {
    error_log('Error submitting scholarship: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit scholarship application']);
}
?>
