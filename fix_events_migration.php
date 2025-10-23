<?php
// Database Migration Script - Add missing columns to events table
require_once 'includes/db_config.php';

echo "<h1>Database Migration - Fix Events Table</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .warning { color: orange; }</style>";

try {
    // Check current events table structure
    echo "<h2>Checking Events Table Structure</h2>";
    $columns = $pdo->query("DESCRIBE events")->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";

    // Check if author_id column exists
    $hasAuthorId = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'author_id') {
            $hasAuthorId = true;
            break;
        }
    }

    if (!$hasAuthorId) {
        echo "<h2 class='warning'>Missing author_id column detected</h2>";
        echo "<p>The events table is missing the author_id column. This means events were created without author tracking.</p>";
        echo "<p><strong>Current Status:</strong> ✅ Events system will work with 'System Event' as author</p>";
        echo "<p><strong>To add author tracking:</strong> Run the database setup script again or manually add the column.</p>";
    } else {
        echo "<h2 class='success'>✅ author_id column exists</h2>";
        echo "<p>Events table has proper author tracking.</p>";
    }

    // Check if is_active column exists
    $hasIsActive = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'is_active') {
            $hasIsActive = true;
            break;
        }
    }

    if (!$hasIsActive) {
        echo "<h2 class='error'>❌ Missing is_active column</h2>";
        echo "<p>Events table is missing the is_active column. Adding it now...</p>";
        try {
            $pdo->exec("ALTER TABLE events ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
            echo "<p class='success'>✅ Successfully added is_active column</p>";
        } catch(PDOException $e) {
            echo "<p class='error'>❌ Error adding is_active column: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<h2 class='success'>✅ is_active column exists</h2>";
    }

    // Count total events
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events");
    $result = $stmt->fetch();
    echo "<h2>Events Count: {$result['total']}</h2>";

    if ($result['total'] > 0) {
        // Count active events
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM events WHERE is_active = 1");
        $activeResult = $stmt->fetch();
        echo "<p>Active Events: {$activeResult['active']}</p>";
        echo "<p>Inactive Events: " . ($result['total'] - $activeResult['active']) . "</p>";
    }

    echo "<hr>";
    echo "<h2>✅ Migration Complete!</h2>";
    echo "<p>The events system should now work properly.</p>";
    echo "<p><a href='admin/manage_events.php'>Go to Events Management</a></p>";

} catch(PDOException $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure the database is properly set up.</p>";
}
?>
