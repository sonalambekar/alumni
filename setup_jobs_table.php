<?php
require_once 'includes/db_config.php';

try {
    // Create jobs table
    $sql = "CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        description TEXT,
        requirements TEXT,
        location VARCHAR(255),
        job_type ENUM('Full-time', 'Part-time', 'Contract', 'Internship', 'Temporary'),
        experience_level ENUM('Entry Level', 'Mid Level', 'Senior Level', 'Executive'),
        salary_range VARCHAR(100),
        apply_link VARCHAR(500),
        posted_by INT,
        posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        is_approved BOOLEAN DEFAULT FALSE,
        approved_by INT NULL,
        approved_at TIMESTAMP NULL,
        FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql);
    echo "Jobs table created successfully or already exists.\n";
    
    // Add some sample data if the table is empty
    $count = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    
    if ($count == 0) {
        $sampleJobs = [
            [
                'title' => 'Senior Software Engineer',
                'company' => 'Tech Innovations Inc.',
                'description' => 'We are looking for an experienced software engineer to join our team.',
                'requirements' => "- 5+ years of experience\n- Strong PHP and JavaScript skills\n- Experience with modern frameworks",
                'location' => 'Bengaluru',
                'job_type' => 'Full-time',
                'experience_level' => 'Senior Level',
                'salary_range' => '₹15,00,000 - ₹25,00,000 per year',
                'apply_link' => 'https://example.com/apply/senior-software-engineer',
                'posted_by' => 1,
                'is_approved' => 1
            ],
            [
                'title' => 'Product Manager',
                'company' => 'Digital Solutions Ltd',
                'description' => 'Looking for a product manager to lead our product development team.',
                'requirements' => "- 3+ years of product management experience\n- Strong analytical skills\n- Excellent communication skills",
                'location' => 'Mumbai',
                'job_type' => 'Full-time',
                'experience_level' => 'Mid Level',
                'salary_range' => '₹12,00,000 - ₹20,00,000 per year',
                'apply_link' => 'https://example.com/apply/product-manager',
                'posted_by' => 1,
                'is_approved' => 1
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO jobs (title, company, description, requirements, location, job_type, experience_level, salary_range, apply_link, posted_by, is_approved) 
                             VALUES (:title, :company, :description, :requirements, :location, :job_type, :experience_level, :salary_range, :apply_link, :posted_by, :is_approved)");
        
        foreach ($sampleJobs as $job) {
            $stmt->execute($job);
        }
        
        echo "Added sample job listings.\n";
    }
    
    echo "Setup completed successfully.\n";
    echo "<a href='pages/jobs.php'>Go to Jobs Page</a>";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
