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
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Get form data
$title = trim($_POST['title']);
$company = trim($_POST['company']);
$description = trim($_POST['description']);
$requirements = trim($_POST['requirements']);
$location = trim($_POST['location']);
$job_type = $_POST['job_type'];
$experience_level = $_POST['experience_level'];
$salary_range = trim($_POST['salary_range']);
$application_deadline = $_POST['application_deadline'];
$contact_email = trim($_POST['contact_email']);
$application_url = trim($_POST['application_url']);

if (empty($title) || empty($company) || empty($description) || empty($location) || empty($contact_email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit();
}

// Set application deadline to NULL if empty
$deadline = !empty($application_deadline) ? $application_deadline : NULL;

// Insert job
try {
    $stmt = $pdo->prepare("INSERT INTO jobs (title, company, description, requirements, location, job_type, experience_level, salary_range, application_deadline, application_url, contact_email, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $company, $description, $requirements, $location, $job_type, $experience_level, $salary_range, $deadline, $application_url, $contact_email, $user_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Job posted successfully']);
    } else {
        throw new Exception('Failed to insert job');
    }
} catch (Exception $e) {
    error_log('Error posting job: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to post job']);
}
?>
