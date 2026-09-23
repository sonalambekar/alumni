<?php
// Database setup script
require_once "config/database.php";

echo "<h2>GMU Alumni Database Setup</h2>";

try {
    // First, create database connection without specifying database
    $conn = new PDO("mysql:host=localhost", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS gmu_alumni");
    echo "<p>✓ Database 'gmu_alumni' created successfully</p>";
    
    // Now use the database
    $conn->exec("USE gmu_alumni");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email_id VARCHAR(100) UNIQUE NOT NULL,
        usn VARCHAR(20) UNIQUE NOT NULL,
        password VARCHAR(255),
        profile_picture VARCHAR(255) DEFAULT 'default.jpg',
        bio TEXT,
        is_director BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p>✓ Users table created successfully</p>";
    
    // Create posts table
    $sql = "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "<p>✓ Posts table created successfully</p>";
    
    // Create announcements table
    $sql = "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "<p>✓ Announcements table created successfully</p>";
    
    // Create messages table
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        content TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "<p>✓ Messages table created successfully</p>";
    
    // Check if demo users already exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE usn IN ('DEMO001', 'DIR001')");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert demo users
        $stmt = $conn->prepare("INSERT INTO users (name, email_id, usn, password, bio, is_director, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // Demo student (password: password123)
        $stmt->execute(['Demo Student', 'demo.student@gmu.edu', 'DEMO001', password_hash('password123', PASSWORD_DEFAULT), 'Demo student account for testing', 0, 1]);
        
        // Demo director (password: password123)
        $stmt->execute(['Demo Director', 'demo.director@gmu.edu', 'DIR001', password_hash('password123', PASSWORD_DEFAULT), 'Demo director account for testing', 1, 1]);
        
        // Additional demo users
        $stmt->execute(['John Doe', 'john.doe@gmu.edu', 'GMU001', password_hash('password123', PASSWORD_DEFAULT), 'Alumni from Computer Science department', 0, 1]);
        $stmt->execute(['Jane Smith', 'jane.smith@gmu.edu', 'GMU002', password_hash('password123', PASSWORD_DEFAULT), 'Alumni from Business Administration', 0, 1]);
        
        echo "<p>✓ Demo users created successfully</p>";
    } else {
        echo "<p>✓ Demo users already exist</p>";
    }
    
    echo "<h3>Demo Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Student Account:</strong> USN: DEMO001, Password: password123</li>";
    echo "<li><strong>Director Account:</strong> USN: DIR001, Password: password123</li>";
    echo "<li><strong>Additional Users:</strong> USN: GMU001 or GMU002, Password: password123</li>";
    echo "</ul>";
    
    echo "<p><strong>Setup completed successfully!</strong></p>";
    echo "<p>You can now use the demo credentials to login to your Flutter app.</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
