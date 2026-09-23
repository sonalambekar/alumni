<?php
require_once "config/database.php";

echo "<html><head><title>Fix GMU Alumni Database</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
    .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    .btn { padding: 10px 20px; background: #5B1F1F; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f8f9fa; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔧 Fix GMU Alumni Database</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<div class='alert alert-warning'>❌ Database connection failed</div>";
        exit;
    }

    echo "<div class='alert alert-info'>✅ Database connected successfully</div>";

    // Check current table structure
    echo "<h2>📋 Current Database Structure</h2>";
    
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "<h3>Table: $table</h3>";
        
        try {
            $stmt = $conn->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col['Field']}</td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            echo "<p>Error describing table: " . $e->getMessage() . "</p>";
        }
    }

    // Fix database structure
    echo "<h2>🔧 Fixing Database Structure</h2>";

    // Drop and recreate tables with correct structure
    echo "<h3>Recreating Tables...</h3>";

    // Drop tables in correct order (reverse of foreign key dependencies)
    $dropTables = ['messages', 'announcements', 'posts', 'users'];
    foreach ($dropTables as $table) {
        try {
            $conn->exec("DROP TABLE IF EXISTS $table");
            echo "✅ Dropped table: $table<br>";
        } catch (Exception $e) {
            echo "⚠️ Could not drop $table: " . $e->getMessage() . "<br>";
        }
    }

    // Create users table
    $sql = "CREATE TABLE users (
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
    echo "✅ Created users table<br>";

    // Create posts table with title column
    $sql = "CREATE TABLE posts (
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
    echo "✅ Created posts table with title column<br>";

    // Create announcements table
    $sql = "CREATE TABLE announcements (
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
    echo "✅ Created announcements table<br>";

    // Create messages table
    $sql = "CREATE TABLE messages (
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
    echo "✅ Created messages table<br>";

    // Add sample data
    echo "<h3>Adding Sample Data...</h3>";

    // Add users
    $users = [
        ['Demo Student', 'demo.student@gmu.edu', 'DEMO001', 'Demo student account for testing', 0],
        ['Demo Director', 'demo.director@gmu.edu', 'DIR001', 'Demo director account for testing', 1],
        ['John Doe', 'john.doe@gmu.edu', 'GMU001', 'Computer Science graduate, now working at Google as Software Engineer', 0],
        ['Jane Smith', 'jane.smith@gmu.edu', 'GMU002', 'Business Administration graduate, currently CEO of a startup', 0],
        ['Mike Johnson', 'mike.johnson@gmu.edu', 'GMU003', 'Mechanical Engineering graduate, working at Tesla', 0],
        ['Sarah Wilson', 'sarah.wilson@gmu.edu', 'GMU004', 'Psychology graduate, practicing therapist', 0],
        ['David Brown', 'david.brown@gmu.edu', 'GMU005', 'Marketing graduate, Digital Marketing Manager at Apple', 0],
        ['Lisa Davis', 'lisa.davis@gmu.edu', 'GMU006', 'Nursing graduate, Head Nurse at GMU Hospital', 0]
    ];
    
    $stmt = $conn->prepare("INSERT INTO users (name, email_id, usn, password, bio, is_director) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($users as $user) {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->execute([$user[0], $user[1], $user[2], $hashedPassword, $user[3], $user[4]]);
    }
    echo "✅ Added 8 users<br>";

    // Add posts with titles
    $posts = [
        [1, 'Welcome to GMU Alumni Network!', 'Hello everyone! Welcome to our new alumni network platform. Here you can connect with fellow graduates, share your experiences, and stay updated with the latest news from our alma mater.'],
        [3, 'My Journey at Google', 'Just wanted to share my experience working at Google for the past 2 years. The computer science program at GMU really prepared me well for this role. Happy to mentor anyone interested in tech!'],
        [4, 'Startup Life: Lessons Learned', 'Running a startup has been an incredible journey. The business skills I learned at GMU have been invaluable. Here are some key lessons I\'ve learned along the way...'],
        [5, 'Tesla Innovation', 'Working at Tesla has been a dream come true. The engineering principles we learned at GMU are being applied to real-world sustainable technology. Exciting times ahead!'],
        [6, 'Mental Health Awareness', 'As a practicing therapist, I want to emphasize the importance of mental health, especially for recent graduates. Remember, it\'s okay to seek help and support.'],
        [7, 'Digital Marketing Trends 2024', 'The marketing landscape is evolving rapidly. Here are the top digital marketing trends I\'m seeing in 2024 that every business should know about.'],
        [8, 'Healthcare Heroes', 'Proud to be serving our community as a nurse. The healthcare program at GMU gave me the foundation to make a real difference in people\'s lives.']
    ];
    
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
    
    foreach ($posts as $post) {
        $stmt->execute($post);
    }
    echo "✅ Added 7 posts<br>";

    // Add announcements
    $announcements = [
        [2, 'Annual Alumni Meetup 2024', 'Join us for our annual alumni meetup on December 15th, 2024. Great opportunity to network and reconnect with old friends!'],
        [2, 'New Scholarship Program', 'We are excited to announce a new scholarship program for current students. Alumni contributions have made this possible.'],
        [2, 'Career Fair Next Month', 'GMU is hosting a career fair next month. Alumni are welcome to participate as mentors and recruiters.'],
        [2, 'Alumni Directory Update', 'Please update your information in the alumni directory to stay connected with the community.']
    ];
    
    $stmt = $conn->prepare("INSERT INTO announcements (user_id, title, content) VALUES (?, ?, ?)");
    
    foreach ($announcements as $announcement) {
        $stmt->execute($announcement);
    }
    echo "✅ Added 4 announcements<br>";

    // Add messages
    $messages = [
        [3, 4, 'Hi Jane! Saw your post about your startup. Would love to connect and learn more about your journey.'],
        [4, 3, 'Hi John! Thanks for reaching out. I\'d be happy to share my experience. Let\'s schedule a call.'],
        [5, 3, 'Hey John, fellow engineer here! Would love to discuss tech trends and maybe collaborate.'],
        [6, 8, 'Hi Lisa! As healthcare professionals, we should definitely connect. How has your experience been?'],
        [7, 4, 'Jane, your business insights are amazing! Could we discuss some marketing strategies for startups?']
    ];
    
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    
    foreach ($messages as $message) {
        $stmt->execute($message);
    }
    echo "✅ Added 5 messages<br>";

    echo "<div class='alert alert-success'>";
    echo "<h3>🎉 Database Fixed Successfully!</h3>";
    echo "<p><strong>Demo Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Student:</strong> USN: DEMO001, Password: password123</li>";
    echo "<li><strong>Director:</strong> USN: DIR001, Password: password123</li>";
    echo "<li><strong>Other Users:</strong> USN: GMU001-GMU006, Password: password123</li>";
    echo "</ul>";
    echo "<p>Your app screens should now load with content instead of showing loading indefinitely!</p>";
    echo "</div>";

    echo "<a href='database_manager.php' class='btn'>📊 View Database Manager</a>";

} catch(PDOException $e) {
    echo "<div class='alert alert-warning'>❌ Database error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
