<?php
require_once "config/database.php";

echo "<html><head><title>Test Users API</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f8f9fa; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>👥 Users API Test</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<div class='error'>❌ Database connection failed</div>";
        exit;
    }

    echo "<div class='success'>✅ Database connected successfully</div>";

    // Get all users
    echo "<h2>All Users in Database</h2>";
    $stmt = $conn->query("SELECT * FROM users ORDER BY name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p>No users found in database.</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>USN</th><th>Email</th><th>Designation</th><th>Active</th></tr>";
        foreach ($users as $user) {
            $active = $user['is_active'] ? 'Yes' : 'No';
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['usn']}</td>";
            echo "<td>{$user['email_id']}</td>";
            echo "<td>" . ($user['designation'] ?: 'N/A') . "</td>";
            echo "<td>$active</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Test API endpoint
    echo "<h2>Test API Response</h2>";
    echo "<p><a href='api/users.php?action=get_users&limit=10' target='_blank'>Test API: api/users.php?action=get_users&limit=10</a></p>";
    
    // Test with search
    echo "<p><a href='api/users.php?action=get_users&limit=10&search=a' target='_blank'>Test Search API: api/users.php?action=get_users&limit=10&search=a</a></p>";

} catch(PDOException $e) {
    echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
