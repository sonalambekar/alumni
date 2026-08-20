<?php
require_once 'includes/db_config.php';

// Create branch_spocs table
$sql1 = "CREATE TABLE IF NOT EXISTS branch_spocs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch VARCHAR(50) NOT NULL UNIQUE,
    spoc_name VARCHAR(100) NOT NULL,
    spoc_email VARCHAR(100) NOT NULL,
    spoc_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// Create registration_requests table
$sql2 = "CREATE TABLE IF NOT EXISTS registration_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    branch VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    spoc_notified BOOLEAN DEFAULT FALSE,
    notification_sent_at TIMESTAMP NULL,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Insert default SPOCs for each branch
$sql3 = "INSERT INTO branch_spocs (branch, spoc_name, spoc_email, spoc_phone) VALUES
    ('GMIT', 'GMIT SPOC', 'gmit.spoc@example.com', '1234567890'),
    ('GMBS', 'GMBS SPOC', 'gmbs.spoc@example.com', '1234567891'),
    ('GMSAS', 'GMSAS SPOC', 'gmsas.spoc@example.com', '1234567892'),
    ('GMSL', 'GMSL SPOC', 'gmsl.spoc@example.com', '1234567893'),
    ('PHARMACY', 'Pharmacy SPOC', 'pharmacy.spoc@example.com', '1234567894')
ON DUPLICATE KEY UPDATE 
    spoc_name = VALUES(spoc_name),
    spoc_email = VALUES(spoc_email)";

try {
    // Create tables
    $pdo->exec($sql1);
    echo "✓ branch_spocs table created successfully\n";
    
    $pdo->exec($sql2);
    echo "✓ registration_requests table created successfully\n";
    
    // Insert default SPOCs
    $pdo->exec($sql3);
    echo "✓ Default SPOCs inserted successfully\n";
    
    echo "\n✅ SPOC system setup completed!\n";
    echo "\nPlease update the SPOC email addresses in the branch_spocs table.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
