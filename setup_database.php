<?php
// Database setup script for Alumni Connect
// This script creates all necessary tables for the admin dashboard system

require_once 'includes/db_config.php';

try {
    // Create users table with admin role
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'alumni') DEFAULT 'alumni',
        profile_image VARCHAR(255),
        graduation_year INT,
        degree VARCHAR(100),
        current_company VARCHAR(100),
        current_position VARCHAR(100),
        location VARCHAR(100),
        bio TEXT,
        linkedin_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE
    )";
    $pdo->exec($sql);

    // Create noticeboard table
    $sql = "CREATE TABLE IF NOT EXISTS noticeboard (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        author_id INT NOT NULL,
        publish_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        expiry_date DATETIME NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);

    // Create news table
    $sql = "CREATE TABLE IF NOT EXISTS news (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        excerpt VARCHAR(500),
        featured_image VARCHAR(255),
        author_id INT NOT NULL,
        publish_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_featured BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);

    // Create events table
    $sql = "CREATE TABLE IF NOT EXISTS events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        event_date DATETIME NOT NULL,
        end_date DATETIME NULL,
        location VARCHAR(255),
        venue VARCHAR(255),
        max_attendees INT,
        current_attendees INT DEFAULT 0,
        registration_deadline DATETIME NULL,
        event_type ENUM('meetup', 'conference', 'workshop', 'social', 'webinar', 'other') DEFAULT 'other',
        featured_image VARCHAR(255),
        author_id INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);

    // Create jobs table
    $sql = "CREATE TABLE IF NOT EXISTS jobs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        requirements TEXT,
        location VARCHAR(255),
        job_type ENUM('full-time', 'part-time', 'contract', 'internship', 'freelance') DEFAULT 'full-time',
        experience_level ENUM('entry', 'mid', 'senior', 'executive') DEFAULT 'entry',
        salary_range VARCHAR(100),
        application_deadline DATE NULL,
        application_url VARCHAR(255),
        contact_email VARCHAR(100),
        author_id INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);

    // Create galleries table
    $sql = "CREATE TABLE IF NOT EXISTS galleries (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATE NULL,
        location VARCHAR(255),
        author_id INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);

    // Create photos table
    $sql = "CREATE TABLE IF NOT EXISTS photos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        gallery_id INT NOT NULL,
        title VARCHAR(255),
        description TEXT,
        image_path VARCHAR(255) NOT NULL,
        caption VARCHAR(255),
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_featured BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Create interest_groups table
    $sql = "CREATE TABLE IF NOT EXISTS interest_groups (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(100),
        group_image VARCHAR(255),
        max_members INT NULL,
        current_members INT DEFAULT 0,
        created_by INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )";
    $pdo->exec($sql);

    // Create group_members table
    $sql = "CREATE TABLE IF NOT EXISTS group_members (
        id INT PRIMARY KEY AUTO_INCREMENT,
        group_id INT NOT NULL,
        user_id INT NOT NULL,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (group_id) REFERENCES interest_groups(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_membership (group_id, user_id)
    )";
    $pdo->exec($sql);

    // Create event_registrations table
    $sql = "CREATE TABLE IF NOT EXISTS event_registrations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        attendance_status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_registration (event_id, user_id)
    )";
    $pdo->exec($sql);

    // Insert default admin user (password: admin123)
    $sql = "INSERT IGNORE INTO users (username, email, password, full_name, role) VALUES
            ('admin', 'admin@alumni.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin')";
    $pdo->exec($sql);

    echo "Database tables created successfully!";

} catch(PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>
