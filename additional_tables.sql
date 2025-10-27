-- Additional tables for new features
-- Add these tables to your alumni database

USE alumni;

-- Experience sharing table
CREATE TABLE IF NOT EXISTS experiences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('career', 'entrepreneurship', 'leadership', 'personal', 'industry', 'other') DEFAULT 'other',
    author_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Scholarship applications table
CREATE TABLE IF NOT EXISTS scholarship_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    applicant_name VARCHAR(100) NOT NULL,
    applicant_email VARCHAR(100) NOT NULL,
    applicant_phone VARCHAR(20),
    student_details TEXT,
    purpose TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- Institute medal nominations table
CREATE TABLE IF NOT EXISTS medal_nominations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nominee_name VARCHAR(100) NOT NULL,
    nominee_email VARCHAR(100) NOT NULL,
    nominee_graduation_year INT,
    nominee_degree VARCHAR(100),
    category ENUM('academic', 'professional', 'community', 'entrepreneurship', 'innovation', 'lifetime') DEFAULT 'academic',
    reason TEXT NOT NULL,
    achievements TEXT,
    nominator_name VARCHAR(100) NOT NULL,
    nominator_email VARCHAR(100) NOT NULL,
    nominator_phone VARCHAR(20),
    nominator_relationship VARCHAR(100),
    status ENUM('pending', 'approved', 'rejected', 'awarded') DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- Add sample experience data
INSERT IGNORE INTO experiences (title, content, category, author_id, author_name) VALUES
('From Campus to CEO: My Journey', 'Starting as a fresh graduate, I never imagined I would one day lead a company of 200 people. The key lessons I learned were perseverance, continuous learning, and building strong relationships. My advice to current students: never stop networking and always be willing to learn from failures.', 'career', 1, 'John Doe'),
('Building My Startup from Scratch', 'Two years ago, I quit my comfortable corporate job to pursue my dream of starting a tech company. It was the scariest yet most rewarding decision of my life. The alumni network was crucial in connecting me with mentors and early investors. Today, we have 50 employees and are growing rapidly.', 'entrepreneurship', 2, 'Jane Smith'),
('Leading Through Crisis', 'When the pandemic hit, our team was scattered across different cities. I had to quickly adapt my leadership style to manage remote teams effectively. Communication became more important than ever, and I learned that empathy and trust are the foundation of good leadership.', 'leadership', 1, 'John Doe');

-- Add sample scholarship application
INSERT IGNORE INTO scholarship_applications (title, description, amount, applicant_name, applicant_email, student_details, purpose, status) VALUES
('Support for Underprivileged Students', 'This scholarship will help students from economically challenged backgrounds pursue their education without financial burden.', 50000.00, 'Alumni Association', 'alumni@university.edu', 'Open to all undergraduate students with family income less than ₹5 LPA', 'To provide financial assistance to meritorious students from underprivileged backgrounds', 'approved');

-- Add sample medal nomination
INSERT IGNORE INTO medal_nominations (nominee_name, nominee_email, nominee_graduation_year, nominee_degree, category, reason, achievements, nominator_name, nominator_email, nominator_relationship, status) VALUES
('Dr. Sarah Johnson', 'sarah.johnson@email.com', 1995, 'PhD Computer Science', 'academic', 'Revolutionary contributions to artificial intelligence and machine learning research. Published over 100 papers and holds 15 patents.', 'Led breakthrough research in neural networks, founded AI research institute, mentored 50+ PhD students who now hold prominent positions in academia and industry.', 'Michael Chen', 'm.chen@email.com', 'Former student and colleague', 'pending');
