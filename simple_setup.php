<?php
require_once "config/database.php";

echo "<html><head><title>Simple GMU Alumni Setup</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
    .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    .btn { padding: 10px 20px; background: #5B1F1F; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🎓 Simple GMU Alumni Setup</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<div class='alert alert-warning'>❌ Database connection failed</div>";
        exit;
    }

    echo "<div class='alert alert-info'>✅ Database connected successfully</div>";

    // Step 1: Create tables
    echo "<h2>Step 1: Creating Tables</h2>";
    
    // Users table
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
    echo "✅ Users table created<br>";

    // Posts table
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
    echo "✅ Posts table created<br>";

    // Announcements table
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
    echo "✅ Announcements table created<br>";

    // Messages table
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
    echo "✅ Messages table created<br>";

    // Step 2: Add sample users
    echo "<h2>Step 2: Adding Sample Users</h2>";
    
    // Check if users already exist
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        $users = [
            ['Demo Student', 'demo.student@gmu.edu', 'DEMO001', 'Demo student account for testing', 0],
            ['Demo Director', 'demo.director@gmu.edu', 'DIR001', 'Demo director account for testing', 1],
            ['John Doe', 'john.doe@gmu.edu', 'GMU001', 'Computer Science graduate, now working at Google', 0],
            ['Jane Smith', 'jane.smith@gmu.edu', 'GMU002', 'Business Administration graduate, CEO of startup', 0],
            ['Mike Johnson', 'mike.johnson@gmu.edu', 'GMU003', 'Mechanical Engineering graduate at Tesla', 0],
            ['Sarah Wilson', 'sarah.wilson@gmu.edu', 'GMU004', 'Psychology graduate, practicing therapist', 0],
            ['David Brown', 'david.brown@gmu.edu', 'GMU005', 'Marketing graduate at Apple', 0],
            ['Lisa Davis', 'lisa.davis@gmu.edu', 'GMU006', 'Nursing graduate, Head Nurse', 0]
        ];
        
        $stmt = $conn->prepare("INSERT INTO users (name, email_id, usn, password, bio, is_director) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($users as $user) {
            $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
            $stmt->execute([$user[0], $user[1], $user[2], $hashedPassword, $user[3], $user[4]]);
            echo "✅ Added user: {$user[0]}<br>";
        }
    } else {
        echo "ℹ️ Users already exist ($userCount users)<br>";
    }

    // Step 3: Add sample posts
    echo "<h2>Step 3: Adding Sample Posts</h2>";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM posts");
    $postCount = $stmt->fetchColumn();
    
    if ($postCount == 0) {
        $posts = [
            [1, 'Welcome to GMU Alumni Network!', 'Hello everyone! Welcome to our new alumni network platform.'],
            [3, 'My Journey at Google', 'Just wanted to share my experience working at Google for the past 2 years.'],
            [4, 'Startup Life: Lessons Learned', 'Running a startup has been an incredible journey.'],
            [5, 'Tesla Innovation', 'Working at Tesla has been a dream come true.'],
            [6, 'Mental Health Awareness', 'As a practicing therapist, I want to emphasize the importance of mental health.'],
            [7, 'Digital Marketing Trends 2024', 'The marketing landscape is evolving rapidly.']
        ];
        
        $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        
        foreach ($posts as $post) {
            $stmt->execute($post);
            echo "✅ Added post: {$post[1]}<br>";
        }
    } else {
        echo "ℹ️ Posts already exist ($postCount posts)<br>";
    }

    // Step 4: Add sample announcements
    echo "<h2>Step 4: Adding Sample Announcements</h2>";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM announcements");
    $announcementCount = $stmt->fetchColumn();
    
    if ($announcementCount == 0) {
        $announcements = [
            [2, 'Annual Alumni Meetup 2024', 'Join us for our annual alumni meetup on December 15th, 2024.'],
            [2, 'New Scholarship Program', 'We are excited to announce a new scholarship program for current students.'],
            [2, 'Career Fair Next Month', 'GMU is hosting a career fair next month.'],
            [2, 'Alumni Directory Update', 'Please update your information in the alumni directory.']
        ];
        
        $stmt = $conn->prepare("INSERT INTO announcements (user_id, title, content) VALUES (?, ?, ?)");
        
        foreach ($announcements as $announcement) {
            $stmt->execute($announcement);
            echo "✅ Added announcement: {$announcement[1]}<br>";
        }
    } else {
        echo "ℹ️ Announcements already exist ($announcementCount announcements)<br>";
    }

    // Step 5: Add sample messages
    echo "<h2>Step 5: Adding Sample Messages</h2>";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM messages");
    $messageCount = $stmt->fetchColumn();
    
    if ($messageCount == 0) {
        $messages = [
            [3, 4, 'Hi Jane! Saw your post about your startup. Would love to connect!'],
            [4, 3, 'Hi John! Thanks for reaching out. I\'d be happy to share my experience.'],
            [5, 3, 'Hey John, fellow engineer here! Would love to discuss tech trends.'],
            [6, 8, 'Hi Lisa! As healthcare professionals, we should definitely connect.']
        ];
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        
        foreach ($messages as $message) {
            $stmt->execute($message);
            echo "✅ Added message between users {$message[0]} and {$message[1]}<br>";
        }
    } else {
        echo "ℹ️ Messages already exist ($messageCount messages)<br>";
    }

    echo "<div class='alert alert-success'>";
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<p><strong>Demo Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Student:</strong> USN: DEMO001, Password: password123</li>";
    echo "<li><strong>Director:</strong> USN: DIR001, Password: password123</li>";
    echo "<li><strong>Other Users:</strong> USN: GMU001-GMU005, Password: password123</li>";
    echo "</ul>";
    echo "</div>";

    echo "<a href='database_manager.php' class='btn'>📊 View Database Manager</a>";
    echo "<a href='check_database.php' class='btn'>🔍 Check Database</a>";

} catch(PDOException $e) {
    echo "<div class='alert alert-warning'>❌ Database error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
