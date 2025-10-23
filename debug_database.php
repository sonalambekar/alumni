<?php
// Debug script to check database status
require_once 'includes/db_config.php';

echo "<h1>Database Debug Information</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } .error { color: red; } .success { color: green; }</style>";

try {
    echo "<h2>Database Connection</h2>";
    echo "<p class='success'>✅ Connected to database successfully!</p>";

    echo "<h2>Available Tables</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "<p class='error'>❌ No tables found! Please run the database setup.</p>";
        echo "<p><a href='setup_database.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Database Setup</a></p>";
    } else {
        echo "<table><tr><th>Table Name</th></tr>";
        foreach ($tables as $table) {
            echo "<tr><td>$table</td></tr>";
        }
        echo "</table>";
    }

    echo "<h2>Noticeboard Table Structure</h2>";
    try {
        $stmt = $pdo->query("DESCRIBE noticeboard");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
        }
        echo "</table>";
    } catch(PDOException $e) {
        echo "<p class='error'>❌ Error describing noticeboard table: " . $e->getMessage() . "</p>";
    }

    echo "<h2>Users Table Structure</h2>";
    try {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
        }
        echo "</table>";
    } catch(PDOException $e) {
        echo "<p class='error'>❌ Error describing users table: " . $e->getMessage() . "</p>";
    }

    echo "<h2>Noticeboard Data</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM noticeboard");
        $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($notices)) {
            echo "<p class='error'>❌ No data in noticeboard table!</p>";
        } else {
            echo "<table><tr><th>ID</th><th>Title</th><th>Author ID</th><th>Is Active</th><th>Created</th></tr>";
            foreach ($notices as $notice) {
                echo "<tr><td>{$notice['id']}</td><td>{$notice['title']}</td><td>{$notice['author_id']}</td><td>" . ($notice['is_active'] ? '✅' : '❌') . "</td><td>{$notice['created_at']}</td></tr>";
            }
            echo "</table>";
        }
    } catch(PDOException $e) {
        echo "<p class='error'>❌ Error querying noticeboard: " . $e->getMessage() . "</p>";
    }

    echo "<h2>Users Data</h2>";
    try {
        $stmt = $pdo->query("SELECT id, username, email, full_name, role FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($users)) {
            echo "<p class='error'>❌ No users in database!</p>";
        } else {
            echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th></tr>";
            foreach ($users as $user) {
                echo "<tr><td>{$user['id']}</td><td>{$user['username']}</td><td>{$user['email']}</td><td>{$user['full_name']}</td><td>{$user['role']}</td></tr>";
            }
            echo "</table>";
        }
    } catch(PDOException $e) {
        echo "<p class='error'>❌ Error querying users: " . $e->getMessage() . "</p>";
    }

    echo "<h2>Testing Noticeboard Query</h2>";
    try {
        $stmt = $pdo->query("
            SELECT n.*, u.full_name as author_name
            FROM noticeboard n
            LEFT JOIN users u ON n.author_id = u.id
            WHERE n.is_active = 1
            ORDER BY n.created_at DESC
        ");
        $testNotices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($testNotices)) {
            echo "<p class='error'>❌ Query returned no results! Check if notices are active.</p>";
        } else {
            echo "<p class='success'>✅ Query successful! Found " . count($testNotices) . " notices.</p>";
            echo "<table><tr><th>Title</th><th>Author Name</th><th>Status</th></tr>";
            foreach ($testNotices as $notice) {
                echo "<tr><td>{$notice['title']}</td><td>{$notice['author_name']}</td><td>" . ($notice['is_active'] ? 'Active' : 'Inactive') . "</td></tr>";
            }
            echo "</table>";
        }
    } catch(PDOException $e) {
        echo "<p class='error'>❌ Query failed: " . $e->getMessage() . "</p>";
    }

} catch(PDOException $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in includes/db_config.php</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If no tables exist, <a href='setup_database.php'>run the database setup</a></li>";
echo "<li>If tables exist but notices aren't showing, check if they are marked as 'active'</li>";
echo "<li>If author names are missing, check if user records exist with matching IDs</li>";
echo "</ol>";
?>
