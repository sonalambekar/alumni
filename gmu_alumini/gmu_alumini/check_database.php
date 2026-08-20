<?php
// Check existing database and users
require_once "config/database.php";

echo "<h2>GMU Alumni Database Check</h2>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if users table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'users'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Users table exists</p>";
        
        // Get all users
        $stmt = $conn->prepare("SELECT id, name, email_id, usn, is_director, is_active FROM users ORDER BY id");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Existing Users:</h3>";
        if (count($users) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>USN</th><th>Director</th><th>Active</th></tr>";
            
            foreach ($users as $user) {
                $directorStatus = $user['is_director'] ? 'Yes' : 'No';
                $activeStatus = $user['is_active'] ? 'Yes' : 'No';
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['name']}</td>";
                echo "<td>{$user['email_id']}</td>";
                echo "<td><strong>{$user['usn']}</strong></td>";
                echo "<td>{$directorStatus}</td>";
                echo "<td>{$activeStatus}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Check for demo users specifically
            $stmt = $conn->prepare("SELECT usn, name FROM users WHERE usn LIKE 'DEMO%' OR usn LIKE 'DIR%' OR usn LIKE 'GMU%'");
            $stmt->execute();
            $demoUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($demoUsers) > 0) {
                echo "<h3>Demo/Test Users Found:</h3>";
                echo "<ul>";
                foreach ($demoUsers as $user) {
                    echo "<li><strong>{$user['usn']}</strong> - {$user['name']}</li>";
                }
                echo "</ul>";
                echo "<p><strong>Try logging in with password: 'password123'</strong></p>";
            } else {
                echo "<h3 style='color: orange;'>⚠️ No obvious demo users found</h3>";
                echo "<p>You can try logging in with any of the USNs above using password 'password123'</p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ No users found in database</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Users table does not exist</p>";
    }
    
    // Check password verification method
    echo "<h3>Password Check Info:</h3>";
    echo "<p>The login system accepts:</p>";
    echo "<ul>";
    echo "<li>Demo password: <strong>password123</strong> (for any user)</li>";
    echo "<li>Hashed passwords stored in database</li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>
