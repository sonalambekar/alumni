<?php
require_once 'includes/db_config.php';

try {
    // Check if jobs table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'jobs'");
    
    if ($tableCheck->rowCount() === 0) {
        // Create jobs table if it doesn't exist
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
        echo "Created jobs table with all required columns.\n";
    } else {
        // Check and add missing columns
        $columns = [
            'is_approved' => "ADD COLUMN IF NOT EXISTS is_approved BOOLEAN DEFAULT FALSE",
            'is_active' => "ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE",
            'approved_by' => "ADD COLUMN IF NOT EXISTS approved_by INT NULL, ADD FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL",
            'approved_at' => "ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL"
        ];
        
        $alterTable = "";
        foreach ($columns as $column => $alterSql) {
            $check = $pdo->query("SHOW COLUMNS FROM jobs LIKE '$column'");
            if ($check->rowCount() === 0) {
                $alterTable .= ($alterTable ? ", " : "") . $alterSql;
            }
        }
        
        if (!empty($alterTable)) {
            $sql = "ALTER TABLE jobs " . $alterTable;
            $pdo->exec($sql);
            echo "Updated jobs table with missing columns.\n";
        } else {
            echo "All required columns already exist in jobs table.\n";
        }
    }
    
    // Verify the table structure
    $columns = $pdo->query("DESCRIBE jobs")->fetchAll(PDO::FETCH_COLUMN);
    echo "Current columns in jobs table: " . implode(", ", $columns) . "\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
