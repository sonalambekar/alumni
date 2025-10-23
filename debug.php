<?php
// Simple database connection and setup test
echo "<h1>Database Connection Test</h1>";

try {
    // Include database configuration
    require_once 'includes/db_config.php';
    echo "<p style='color: green;'>✅ Database configuration loaded successfully</p>";

    // Test connection
    echo "<p>Testing database connection...</p>";

    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Users table exists</p>";

        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "<p>Total users in database: <strong>" . $result['count'] . "</strong></p>";

        // Show sample users for login testing
        echo "<h3>Sample Users for Testing:</h3>";
        $stmt = $pdo->query("SELECT usn, name FROM users LIMIT 5");
        $users = $stmt->fetchAll();
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li><strong>USN:</strong> " . htmlspecialchars($user['usn']) . " | <strong>Name:</strong> " . htmlspecialchars($user['name']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Users table does not exist</p>";
        echo "<p>You need to run the database setup script first.</p>";
    }

} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Make sure XAMPP MySQL is running and the database configuration is correct.</p>";
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Quick Setup</h2>";
echo "<p><a href='run_admin_setup.php' style='background: #5b1f1f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Database Setup</a></p>";
echo "<p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login Page</a></p>";
?>
