-- Alumni Connect Database Schema
-- Run this SQL script in phpMyAdmin or MySQL command line to create all necessary tables

CREATE DATABASE IF NOT EXISTS alumni;
USE alumni;

-- Users table with admin role
CREATE TABLE IF NOT EXISTS users (
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
);

-- Noticeboard table
CREATE TABLE IF NOT EXISTS noticeboard (
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
);

-- News table
CREATE TABLE IF NOT EXISTS news (
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
);

-- Events table
CREATE TABLE IF NOT EXISTS events (
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
);

-- Jobs table
CREATE TABLE IF NOT EXISTS jobs (
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
);

-- Galleries table
CREATE TABLE IF NOT EXISTS galleries (
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
);

-- Photos table
CREATE TABLE IF NOT EXISTS photos (
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
);

-- Interest groups table
CREATE TABLE IF NOT EXISTS interest_groups (
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
);

-- Group members table
CREATE TABLE IF NOT EXISTS group_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (group_id) REFERENCES interest_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_membership (group_id, user_id)
);

-- Event registrations table
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
);

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@alumni.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Insert some sample data for demonstration
INSERT IGNORE INTO users (username, email, password, full_name, role, graduation_year, degree, current_company, current_position, location) VALUES
('john_doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'alumni', 2020, 'Computer Science', 'Tech Corp', 'Software Engineer', 'New York'),
('jane_smith', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'alumni', 2019, 'Business Administration', 'Marketing Inc', 'Marketing Manager', 'California');

-- Insert sample noticeboard content
INSERT IGNORE INTO noticeboard (title, content, priority) VALUES
('Welcome to Alumni Connect', 'Welcome to our new alumni platform! Connect with old friends, discover opportunities, and stay updated with the latest news from your alma mater.', 'high'),
('Annual Reunion 2024', 'Mark your calendars! The annual alumni reunion is scheduled for March 15, 2024. Don\'t miss this opportunity to reconnect with your classmates.', 'medium');

-- Insert sample news
INSERT IGNORE INTO news (title, content, excerpt, is_featured) VALUES
('Alumni Startup Raises $2M in Funding', 'Congratulations to our alumni startup for securing Series A funding! This achievement showcases the entrepreneurial spirit of our community.', 'Our alumni startup reaches new heights with major funding round...', TRUE),
('New Research Center Opens', 'The university has opened a state-of-the-art research center focusing on sustainable technology and innovation.', 'University expands research capabilities with new center...', FALSE);

-- Insert sample events
INSERT IGNORE INTO events (title, description, event_date, location, venue, event_type) VALUES
('Alumni Networking Mixer', 'Join us for an evening of networking and reconnecting with fellow alumni. Light refreshments will be served.', '2024-02-15 18:00:00', 'New York', 'Downtown Conference Center', 'social'),
('Career Development Workshop', 'Learn about the latest trends in your industry and get tips for career advancement from industry experts.', '2024-03-01 10:00:00', 'Online', 'Virtual Event', 'workshop');

-- Insert sample jobs
INSERT IGNORE INTO jobs (title, company, description, location, job_type, experience_level) VALUES
('Senior Software Engineer', 'Tech Innovations Inc', 'We are looking for an experienced software engineer to join our growing team. You will be working on cutting-edge projects and mentoring junior developers.', 'San Francisco', 'full-time', 'senior'),
('Marketing Coordinator', 'Global Marketing Solutions', 'Join our marketing team to help develop and execute marketing campaigns. Experience with digital marketing preferred.', 'Remote', 'full-time', 'mid');

-- Couch listings table for alumni to offer accommodation
CREATE TABLE IF NOT EXISTS couch_listings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    available_from DATE NOT NULL,
    available_to DATE NOT NULL,
    max_guests INT DEFAULT 1,
    amenities TEXT,
    rules TEXT,
    contact_info VARCHAR(255),
    author_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Couch requests table for alumni seeking accommodation
CREATE TABLE IF NOT EXISTS couch_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location_needed VARCHAR(255) NOT NULL,
    arrival_date DATE NOT NULL,
    departure_date DATE NOT NULL,
    num_guests INT DEFAULT 1,
    preferences TEXT,
    contact_info VARCHAR(255),
    author_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);
