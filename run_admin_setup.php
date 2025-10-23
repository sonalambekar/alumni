<?php
// Database setup script - run this to add admin tables
require_once 'includes/db_config.php';

try {
    echo "Connected to database successfully!<br>";

    // Read and execute the SQL file
    $sql = file_get_contents('add_admin_tables.sql');

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...<br>";
            } catch(PDOException $e) {
                echo "⚠️ Skipped (might already exist): " . substr($statement, 0, 50) . "...<br>";
            }
        }
    }

    echo "<br>✅ Admin tables setup complete!<br>";
    echo "🎉 You can now use the admin dashboard!<br>";
    echo "📧 Admin login: admin@gmu.ac.in<br>";
    echo "🔑 Password: admin123<br>";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Please make sure XAMPP MySQL is running and try again.";
}
?>
