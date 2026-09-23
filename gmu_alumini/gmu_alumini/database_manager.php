<?php
require_once "config/database.php";

// Get action from URL parameter
$action = $_GET['action'] ?? 'explore';

echo "<html><head><title>GMU Alumni Database Manager</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .nav { margin-bottom: 20px; }
    .nav a { display: inline-block; padding: 10px 20px; margin-right: 10px; background: #5B1F1F; color: white; text-decoration: none; border-radius: 5px; }
    .nav a:hover { background: #3F1414; }
    .nav a.active { background: #D4B95A; color: #5B1F1F; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f8f9fa; font-weight: bold; }
    tr:hover { background-color: #f5f5f5; }
    .btn { padding: 8px 16px; background: #5B1F1F; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    .btn:hover { background: #3F1414; }
    .btn-success { background: #28a745; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-danger { background: #dc3545; }
    .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
    .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    .count { font-weight: bold; color: #5B1F1F; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🎓 GMU Alumni Database Manager</h1>";

// Navigation
echo "<div class='nav'>";
echo "<a href='?action=explore' class='" . ($action == 'explore' ? 'active' : '') . "'>📊 Explore Database</a>";
echo "<a href='?action=add_sample' class='" . ($action == 'add_sample' ? 'active' : '') . "'>➕ Add Sample Data</a>";
echo "<a href='?action=clear_data' class='" . ($action == 'clear_data' ? 'active' : '') . "'>🗑️ Clear Data</a>";
echo "<a href='?action=sql_runner' class='" . ($action == 'sql_runner' ? 'active' : '') . "'>⚡ SQL Runner</a>";
echo "</div>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<div class='alert alert-warning'>❌ Database connection failed</div>";
        exit;
    }

    switch($action) {
        case 'explore':
            exploreDatabase($conn);
            break;
        case 'add_sample':
            addSampleData($conn);
            break;
        case 'clear_data':
            clearData($conn);
            break;
        case 'sql_runner':
            sqlRunner($conn);
            break;
        default:
            exploreDatabase($conn);
    }

} catch(PDOException $e) {
    echo "<div class='alert alert-warning'>❌ Database error: " . $e->getMessage() . "</div>";
}

function exploreDatabase($conn) {
    echo "<h2>📊 Database Structure & Data</h2>";
    
    // Get all tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<div class='alert alert-warning'>⚠️ No tables found. <a href='?action=add_sample'>Create sample data</a></div>";
        return;
    }
    
    foreach ($tables as $table) {
        echo "<h3>📋 Table: $table</h3>";
        
        // Get table structure
        $stmt = $conn->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Structure:</h4>";
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
        
        // Get row count
        $stmt = $conn->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        
        echo "<h4>Data: <span class='count'>$count rows</span></h4>";
        
        if ($count > 0) {
            // Show sample data
            $stmt = $conn->query("SELECT * FROM $table LIMIT 10");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($data)) {
                echo "<table>";
                echo "<tr>";
                foreach (array_keys($data[0]) as $header) {
                    echo "<th>$header</th>";
                }
                echo "</tr>";
                
                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                        echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
                
                if ($count > 10) {
                    echo "<p><em>Showing first 10 of $count rows</em></p>";
                }
            }
        } else {
            echo "<p><em>No data in this table</em></p>";
        }
        
        echo "<hr>";
    }
}

function addSampleData($conn) {
    echo "<h2>➕ Add Sample Data</h2>";
    
    if ($_POST['confirm'] ?? false) {
        try {
            // Clear existing data first (without transaction for TRUNCATE)
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Check if tables exist before truncating
            $tables = ['messages', 'announcements', 'posts', 'users'];
            foreach ($tables as $table) {
                $stmt = $conn->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->rowCount() > 0) {
                    $conn->exec("TRUNCATE TABLE $table");
                }
            }
            
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            // Start transaction for inserts
            $conn->beginTransaction();
            
            // Add users
            $users = [
                ['Demo Student', 'demo.student@gmu.edu', 'DEMO001', 'password123', 'Demo student account for testing', 0, 1],
                ['Demo Director', 'demo.director@gmu.edu', 'DIR001', 'password123', 'Demo director account for testing', 1, 1],
                ['John Doe', 'john.doe@gmu.edu', 'GMU001', 'password123', 'Computer Science graduate, now working at Google as Software Engineer', 0, 1],
                ['Jane Smith', 'jane.smith@gmu.edu', 'GMU002', 'password123', 'Business Administration graduate, currently CEO of a startup', 0, 1],
                ['Mike Johnson', 'mike.johnson@gmu.edu', 'GMU003', 'password123', 'Mechanical Engineering graduate, working at Tesla', 0, 1],
                ['Sarah Wilson', 'sarah.wilson@gmu.edu', 'GMU004', 'password123', 'Psychology graduate, practicing therapist', 0, 1],
                ['David Brown', 'david.brown@gmu.edu', 'GMU005', 'password123', 'Marketing graduate, Digital Marketing Manager at Apple', 0, 1],
                ['Lisa Davis', 'lisa.davis@gmu.edu', 'GMU006', 'password123', 'Nursing graduate, Head Nurse at GMU Hospital', 0, 1],
                ['Robert Miller', 'robert.miller@gmu.edu', 'GMU007', 'password123', 'Finance graduate, Investment Banker at Goldman Sachs', 0, 1],
                ['Emily Taylor', 'emily.taylor@gmu.edu', 'GMU008', 'password123', 'Art graduate, Freelance Graphic Designer', 0, 1]
            ];
            
            $stmt = $conn->prepare("INSERT INTO users (name, email_id, usn, password, bio, is_director, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($users as $user) {
                $hashedPassword = password_hash($user[3], PASSWORD_DEFAULT);
                $stmt->execute([$user[0], $user[1], $user[2], $hashedPassword, $user[4], $user[5], $user[6]]);
            }
            
            // Add posts
            $posts = [
                [1, 'Welcome to GMU Alumni Network!', 'Hello everyone! Welcome to our new alumni network platform. Here you can connect with fellow graduates, share your experiences, and stay updated with the latest news from our alma mater.'],
                [3, 'My Journey at Google', 'Just wanted to share my experience working at Google for the past 2 years. The computer science program at GMU really prepared me well for this role. Happy to mentor anyone interested in tech!'],
                [4, 'Startup Life: Lessons Learned', 'Running a startup has been an incredible journey. The business skills I learned at GMU have been invaluable. Here are some key lessons I\'ve learned along the way...'],
                [5, 'Tesla Innovation', 'Working at Tesla has been a dream come true. The engineering principles we learned at GMU are being applied to real-world sustainable technology. Exciting times ahead!'],
                [6, 'Mental Health Awareness', 'As a practicing therapist, I want to emphasize the importance of mental health, especially for recent graduates. Remember, it\'s okay to seek help and support.'],
                [7, 'Digital Marketing Trends 2024', 'The marketing landscape is evolving rapidly. Here are the top digital marketing trends I\'m seeing in 2024 that every business should know about.'],
                [8, 'Healthcare Heroes', 'Proud to be serving our community as a nurse. The healthcare program at GMU gave me the foundation to make a real difference in people\'s lives.'],
                [9, 'Investment Tips for New Graduates', 'Starting your financial journey can be overwhelming. Here are some basic investment tips for new graduates to build wealth over time.'],
                [10, 'Creative Freelancing', 'Being a freelance graphic designer has its challenges and rewards. Here\'s how I built my client base and manage my creative business.']
            ];
            
            $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())");
            foreach ($posts as $post) {
                $stmt->execute($post);
            }
            
            // Add announcements
            $announcements = [
                [2, 'Annual Alumni Meetup 2024', 'Join us for our annual alumni meetup on December 15th, 2024. Great opportunity to network and reconnect with old friends!', 1],
                [2, 'New Scholarship Program', 'We are excited to announce a new scholarship program for current students. Alumni contributions have made this possible.', 1],
                [2, 'Career Fair Next Month', 'GMU is hosting a career fair next month. Alumni are welcome to participate as mentors and recruiters.', 1],
                [2, 'Alumni Directory Update', 'Please update your information in the alumni directory to stay connected with the community.', 1]
            ];
            
            $stmt = $conn->prepare("INSERT INTO announcements (user_id, title, content, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
            foreach ($announcements as $announcement) {
                $stmt->execute($announcement);
            }
            
            // Add sample messages
            $messages = [
                [3, 4, 'Hi Jane! Saw your post about your startup. Would love to connect and learn more about your journey.'],
                [4, 3, 'Hi John! Thanks for reaching out. I\'d be happy to share my experience. Let\'s schedule a call.'],
                [5, 3, 'Hey John, fellow engineer here! Would love to discuss tech trends and maybe collaborate.'],
                [6, 8, 'Hi Lisa! As healthcare professionals, we should definitely connect. How has your experience been?'],
                [7, 4, 'Jane, your business insights are amazing! Could we discuss some marketing strategies for startups?'],
                [9, 7, 'David, I\'m interested in your marketing expertise. Could you help with some investment marketing?']
            ];
            
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, created_at) VALUES (?, ?, ?, NOW())");
            foreach ($messages as $message) {
                $stmt->execute($message);
            }
            
            $conn->commit();
            
            echo "<div class='alert alert-success'>✅ Sample data added successfully!</div>";
            echo "<p><strong>Demo Credentials:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Student:</strong> USN: DEMO001, Password: password123</li>";
            echo "<li><strong>Director:</strong> USN: DIR001, Password: password123</li>";
            echo "<li><strong>Other Users:</strong> USN: GMU001-GMU008, Password: password123</li>";
            echo "</ul>";
            echo "<a href='?action=explore' class='btn'>📊 View Database</a>";
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div class='alert alert-warning'>❌ Error adding sample data: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-info'>";
        echo "<h3>⚠️ This will add comprehensive sample data to your database</h3>";
        echo "<p><strong>What will be added:</strong></p>";
        echo "<ul>";
        echo "<li>10 sample users (including demo accounts)</li>";
        echo "<li>9 sample posts from different users</li>";
        echo "<li>4 sample announcements</li>";
        echo "<li>6 sample messages between users</li>";
        echo "</ul>";
        echo "<p><strong>⚠️ Warning:</strong> This will clear all existing data first!</p>";
        echo "</div>";
        
        echo "<form method='post'>";
        echo "<input type='hidden' name='confirm' value='1'>";
        echo "<button type='submit' class='btn btn-warning'>🚀 Add Sample Data</button>";
        echo "</form>";
    }
}

function clearData($conn) {
    echo "<h2>🗑️ Clear Database Data</h2>";
    
    if ($_POST['confirm'] ?? false) {
        try {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Check if tables exist before truncating
            $tables = ['messages', 'announcements', 'posts', 'users'];
            foreach ($tables as $table) {
                $stmt = $conn->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->rowCount() > 0) {
                    $conn->exec("TRUNCATE TABLE $table");
                }
            }
            
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            echo "<div class='alert alert-success'>✅ All data cleared successfully!</div>";
            echo "<a href='?action=add_sample' class='btn'>➕ Add Sample Data</a>";
            
        } catch (Exception $e) {
            echo "<div class='alert alert-warning'>❌ Error clearing data: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>";
        echo "<h3>⚠️ This will permanently delete all data</h3>";
        echo "<p>This action cannot be undone!</p>";
        echo "</div>";
        
        echo "<form method='post'>";
        echo "<input type='hidden' name='confirm' value='1'>";
        echo "<button type='submit' class='btn btn-danger'>🗑️ Clear All Data</button>";
        echo "</form>";
    }
}

function sqlRunner($conn) {
    echo "<h2>⚡ SQL Runner</h2>";
    
    if ($_POST['sql'] ?? false) {
        $sql = $_POST['sql'];
        echo "<h3>Executing SQL:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($sql) . "</pre>";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            if (stripos($sql, 'SELECT') === 0) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($results)) {
                    echo "<h3>Results:</h3>";
                    echo "<table>";
                    echo "<tr>";
                    foreach (array_keys($results[0]) as $header) {
                        echo "<th>$header</th>";
                    }
                    echo "</tr>";
                    
                    foreach ($results as $row) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No results returned.</p>";
                }
            } else {
                $rowCount = $stmt->rowCount();
                echo "<div class='alert alert-success'>✅ Query executed successfully. Affected rows: $rowCount</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-warning'>❌ SQL Error: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<form method='post'>";
    echo "<textarea name='sql' rows='10' cols='80' placeholder='Enter your SQL query here...' style='width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;'>" . ($_POST['sql'] ?? '') . "</textarea><br><br>";
    echo "<button type='submit' class='btn'>⚡ Execute SQL</button>";
    echo "</form>";
    
    echo "<h3>Quick Queries:</h3>";
    echo "<button onclick=\"document.querySelector('textarea[name=sql]').value='SELECT * FROM users LIMIT 10'\" class='btn btn-success'>👥 Show Users</button>";
    echo "<button onclick=\"document.querySelector('textarea[name=sql]').value='SELECT * FROM posts ORDER BY created_at DESC LIMIT 10'\" class='btn btn-success'>📝 Show Posts</button>";
    echo "<button onclick=\"document.querySelector('textarea[name=sql]').value='SELECT * FROM announcements ORDER BY created_at DESC'\" class='btn btn-success'>📢 Show Announcements</button>";
    echo "<button onclick=\"document.querySelector('textarea[name=sql]').value='SELECT * FROM messages ORDER BY created_at DESC LIMIT 10'\" class='btn btn-success'>💬 Show Messages</button>";
}

echo "</div></body></html>";
?>
