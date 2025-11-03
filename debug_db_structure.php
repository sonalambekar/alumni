<?php
require_once 'includes/db_config.php';

echo "<h1>Database Structure</h1>";

try {
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "<h2>Table: $table</h2>";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' style='margin-bottom: 20px;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Show sample data (first 3 rows)
        try {
            $sample = $pdo->query("SELECT * FROM `$table` LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            if (count($sample) > 0) {
                echo "<h3>Sample Data (First 3 Rows)</h3>";
                echo "<table border='1' cellpadding='5' style='margin-bottom: 30px;'>";
                
                // Headers
                echo "<tr>";
                foreach (array_keys($sample[0]) as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";
                
                // Rows
                foreach ($sample as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : '') . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<p>Could not fetch sample data: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
} catch (PDOException $e) {
    die("<div style='color:red;'>Error: " . $e->getMessage() . "</div>");
}
?>

<h2>Session Data</h2>
<pre><?php print_r($_SESSION); ?></pre>
