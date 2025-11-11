<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db_config.php';

// Debug function
function debug_log($message) {
    $log_file = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

debug_log('Join group script started');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to join a group';
    header('Location: login.php');
    exit();
}

// Check if group_id is provided
if (!isset($_GET['group_id']) || !is_numeric($_GET['group_id'])) {
    $_SESSION['error'] = 'Invalid group';
    header('Location: index.php');
    exit();
}

$group_id = (int)$_GET['group_id'];
$user_id = $_SESSION['user_id'];

// Check if the group exists and is active
try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if the group exists and is active
    $stmt = $pdo->prepare("SELECT id, max_members FROM interest_groups WHERE id = ? AND is_active = 1");
    $stmt->execute([$group_id]);
    $group = $stmt->fetch();

    if (!$group) {
        throw new Exception('Group not found or inactive');
    }

    // Get current member count
    $stmt = $pdo->prepare("SELECT COUNT(*) as member_count FROM group_members WHERE group_id = ? AND is_active = 1");
    $stmt->execute([$group_id]);
    $member_count = $stmt->fetch()['member_count'];

    // Check if group has reached maximum members
    if ($group['max_members'] !== null && $member_count >= $group['max_members']) {
        throw new Exception('This group has reached its maximum capacity');
    }

    // Check if user is already a member
    $stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->execute([$group_id, $user_id]);
    
    if ($stmt->fetch()) {
        throw new Exception('You are already a member of this group');
    }

    // Add user to group
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
    $result = $stmt->execute([$group_id, $user_id]);
    
    debug_log("Inserted into group_members. Group ID: $group_id, User ID: $user_id, Result: " . ($result ? 'success' : 'failed'));
    
    if (!$result) {
        throw new Exception('Failed to add user to group');
    }

    // No need to update member count as we'll calculate it dynamically
    debug_log("Added user $user_id to group $group_id");

    // Commit transaction
    $pdo->commit();
    debug_log("Transaction committed successfully");

    $_SESSION['success'] = 'Successfully joined the group!';
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $error_message = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    debug_log($error_message);
    
    // Log the full backtrace
    debug_log('Backtrace: ' . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true));
    
    // Log the SQL error info if available
    if (isset($stmt) && $stmt) {
        $errorInfo = $stmt->errorInfo();
        if (isset($errorInfo[2])) {
            debug_log('SQL Error: ' . $errorInfo[2]);
        }
    }
    
    $_SESSION['error'] = 'Failed to join group. Please try again. Error: ' . $e->getMessage();
}

// Redirect back to the previous page or groups page
$redirect = $_SERVER['HTTP_REFERER'] ?? 'groups.php';
header("Location: $redirect");
exit();
