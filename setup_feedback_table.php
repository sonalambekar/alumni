<?php
require_once 'includes/db_config.php';

try {
    // Check if feedback table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'feedback'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create feedback table
        $sql = "CREATE TABLE feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            feedback_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "Feedback table created successfully!";
    } else {
        echo "Feedback table already exists.";
    }
    
    // Check if the foreign key constraint exists
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = 'alumni' 
        AND TABLE_NAME = 'feedback' 
        AND CONSTRAINT_NAME = 'feedback_ibfk_1'");
    
    if ($stmt->fetchColumn() == 0) {
        // Add foreign key constraint if it doesn't exist
        $pdo->exec("ALTER TABLE feedback ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "\nForeign key constraint added to feedback table.";
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<h2>Feedback Table Status</h2>
<p>This page has attempted to verify and create the feedback table if it didn't exist.</p>
<p>You can now go back to the <a href="pages/feedback.php">Feedback Page</a>.</p>
