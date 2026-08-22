<?php
require_once 'includes/db_config.php';

try {
    $usn = 'U23E01AI030';
    $password = 'password123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ?, is_active = 1 WHERE usn = ?");
    $stmt->execute([$hashed_password, $usn]);
    
    echo "Password updated successfully for $usn.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
