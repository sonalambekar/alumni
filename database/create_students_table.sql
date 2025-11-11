-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    usn VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (optional)
-- INSERT INTO students (student_id, name, email, usn) VALUES
-- ('U23E01AI030', 'LOKESH C H', 'lokesh@example.com', 'U23E01AI030'),
-- ('U23E02EC037', 'SHIDDANAGPUDA HALANAGOUDA MUNDINAMANI', 'halanagouda@example.com', 'U23E02EC037'),
-- ('U23E01CS018', 'KARTHIK J N PATEL', 'karthik@example.com', 'U23E01CS018');
