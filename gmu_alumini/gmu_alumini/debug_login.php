<?php
require_once "config/database.php";

echo "<html><head><title>Login Debug Tool</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
    .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .btn { padding: 10px 20px; background: #5B1F1F; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    .form-group { margin: 15px 0; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f8f9fa; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Login Debug Tool</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<div class='alert alert-danger'>❌ Database connection failed</div>";
        exit;
    }

    echo "<div class='alert alert-success'>✅ Database connected successfully</div>";

    // Check if users exist
    echo "<h2>👥 Available Users</h2>";
    
    $stmt = $conn->query("SELECT id, name, usn, email_id, is_director, is_active FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<div class='alert alert-warning'>⚠️ No users found in database. <a href='fix_database.php'>Run database fix</a></div>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>USN</th><th>Email</th><th>Director</th><th>Active</th></tr>";
        foreach ($users as $user) {
            $directorStatus = $user['is_director'] ? 'Yes' : 'No';
            $activeStatus = $user['is_active'] ? 'Yes' : 'No';
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td><strong>{$user['usn']}</strong></td>";
            echo "<td>{$user['email_id']}</td>";
            echo "<td>{$directorStatus}</td>";
            echo "<td>{$activeStatus}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Test login form
    echo "<h2>🧪 Test Login</h2>";
    
    if ($_POST['test_login'] ?? false) {
        $usn = $_POST['usn'] ?? '';
        $password = $_POST['password'] ?? '';
        
        echo "<h3>Testing Login for USN: $usn</h3>";
        
        // Step 1: Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE usn = :usn AND is_active = 1");
        $stmt->bindParam(":usn", $usn);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='alert alert-success'>✅ User found: {$user['name']}</div>";
            
            echo "<h4>User Details:</h4>";
            echo "<pre>" . print_r($user, true) . "</pre>";
            
            // Step 2: Test password
            echo "<h4>Password Check:</h4>";
            
            // Check demo password first
            if ($password === "password123") {
                echo "<div class='alert alert-success'>✅ Demo password 'password123' matches</div>";
                
                // Simulate successful login response
                $loginResponse = [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email_id'],
                        'usn' => $user['usn'],
                        'profile_picture' => $user['profile_picture'] ?: 'default.jpg',
                        'is_director' => (bool)$user['is_director'],
                        'bio' => $user['bio'] ?: ''
                    ]
                ];
                
                echo "<h4>Login Response:</h4>";
                echo "<pre>" . json_encode($loginResponse, JSON_PRETTY_PRINT) . "</pre>";
                
            } else if (password_verify($password, $user['password'])) {
                echo "<div class='alert alert-success'>✅ Hashed password matches</div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Password does not match</div>";
                echo "<p>Tried password: '$password'</p>";
                echo "<p>Demo password should be: 'password123'</p>";
            }
            
        } else {
            echo "<div class='alert alert-danger'>❌ User not found or inactive</div>";
            echo "<p>USN '$usn' not found in database or user is inactive</p>";
        }
    }
    
    echo "<form method='post'>";
    echo "<div class='form-group'>";
    echo "<label>USN:</label>";
    echo "<input type='text' name='usn' value='" . ($_POST['usn'] ?? 'DEMO001') . "' placeholder='Enter USN (e.g., DEMO001)'>";
    echo "</div>";
    echo "<div class='form-group'>";
    echo "<label>Password:</label>";
    echo "<input type='password' name='password' value='" . ($_POST['password'] ?? 'password123') . "' placeholder='Enter password'>";
    echo "</div>";
    echo "<button type='submit' name='test_login' value='1' class='btn'>🧪 Test Login</button>";
    echo "</form>";

    // Test API endpoint
    echo "<h2>🔗 Test API Endpoint</h2>";
    
    if ($_POST['test_api'] ?? false) {
        $usn = $_POST['api_usn'] ?? '';
        $password = $_POST['api_password'] ?? '';
        
        echo "<h3>Testing API: auth.php</h3>";
        
        $postData = json_encode([
            'action' => 'login',
            'usn' => $usn,
            'password' => $password
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/gmu_alumini/api/auth.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<h4>API Request:</h4>";
        echo "<pre>POST /gmu_alumini/api/auth.php
Content-Type: application/json

$postData</pre>";
        
        echo "<h4>API Response (HTTP $httpCode):</h4>";
        echo "<pre>$response</pre>";
        
        if ($response) {
            $jsonResponse = json_decode($response, true);
            if ($jsonResponse) {
                echo "<h4>Parsed Response:</h4>";
                echo "<pre>" . json_encode($jsonResponse, JSON_PRETTY_PRINT) . "</pre>";
            }
        }
    }
    
    echo "<form method='post'>";
    echo "<div class='form-group'>";
    echo "<label>API USN:</label>";
    echo "<input type='text' name='api_usn' value='" . ($_POST['api_usn'] ?? 'DEMO001') . "' placeholder='Enter USN'>";
    echo "</div>";
    echo "<div class='form-group'>";
    echo "<label>API Password:</label>";
    echo "<input type='password' name='api_password' value='" . ($_POST['api_password'] ?? 'password123') . "' placeholder='Enter password'>";
    echo "</div>";
    echo "<button type='submit' name='test_api' value='1' class='btn'>🔗 Test API</button>";
    echo "</form>";

    // Quick fixes
    echo "<h2>🔧 Quick Fixes</h2>";
    echo "<div class='alert alert-info'>";
    echo "<h4>Common Issues & Solutions:</h4>";
    echo "<ul>";
    echo "<li><strong>No users in database:</strong> <a href='fix_database.php'>Run database fix</a></li>";
    echo "<li><strong>Wrong credentials:</strong> Use USN: DEMO001, Password: password123</li>";
    echo "<li><strong>API not responding:</strong> Check if XAMPP Apache is running</li>";
    echo "<li><strong>Flutter app issues:</strong> Check console for network errors</li>";
    echo "</ul>";
    echo "</div>";

} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Database error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
