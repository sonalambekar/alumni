<?php
require_once 'includes/db_config.php';

try {
    $stmt = $pdo->query("SELECT usn FROM users WHERE role = 'student' AND is_active = 1 LIMIT 1");
    $student = $stmt->fetch();
    
    if ($student) {
        echo "STUDENT_USN: " . $student['usn'] . "\n";
    } else {
        echo "No active students found in the database.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
