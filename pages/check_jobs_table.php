<?php
require_once '../includes/db_config.php';

try {
    $sql = "SHOW TABLES LIKE 'jobs'";
    $stmt = $pdo->query($sql);
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "Jobs table exists.\n";
        $columns = $pdo->query("DESCRIBE jobs")->fetchAll(PDO::FETCH_ASSOC);
        echo "Columns in jobs table:\n";
        print_r($columns);
    } else {
        echo "Jobs table does not exist.\n";
        // Create the jobs table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS jobs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            company VARCHAR(255) NOT NULL,
            description TEXT,
            requirements TEXT,
            location VARCHAR(255),
            salary_range VARCHAR(100),
            job_type ENUM('Full-time', 'Part-time', 'Contract', 'Internship', 'Temporary') NOT NULL,
            experience_level ENUM('Entry Level', 'Mid Level', 'Senior Level', 'Executive') NOT NULL,
            posted_by INT,
            posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            is_approved BOOLEAN DEFAULT FALSE,
            approved_by INT,
            approved_at TIMESTAMP NULL,
            FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $pdo->exec($sql);
        echo "Created jobs table.\n";
    }
} catch (PDOException $e) {
    die("Error checking jobs table: " . $e->getMessage());
}
?>
