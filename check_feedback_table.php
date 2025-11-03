<?php
require_once 'includes/db_config.php';

try {
    // Check table structure
    $stmt = $pdo->query("DESCRIBE feedback");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Feedback Table Structure</h2>";
    echo "<table border='1' cellpadding='5'>
            <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
                <th>Default</th>
                <th>Extra</th>
            </tr>";
    
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
    
    // Check foreign key constraint
    echo "<h3>Foreign Key Constraints</h3>";
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = 'alumni' 
            AND TABLE_NAME = 'feedback' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($fks) > 0) {
        echo "<table border='1' cellpadding='5'>
                <tr>
                    <th>Constraint Name</th>
                    <th>Column</th>
                    <th>References</th>
                </tr>";
        
        foreach ($fks as $fk) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fk['CONSTRAINT_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_TABLE_NAME'] . '.' . $fk['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No foreign key constraints found on the feedback table.";
    }
    
} catch (PDOException $e) {
    die("<div style='color:red;'>Error: " . $e->getMessage() . "</div>");
}
?>

<h3>Next Steps</h3>
<p>If you're still seeing the error, please check the following:</p>
<ol>
    <li>Make sure the 'users' table exists with an 'id' column</li>
    <li>Check that the 'user_id' in the feedback table matches the 'id' type in the users table</li>
    <li>Verify that the database user has proper permissions on both tables</li>
</ol>
<p><a href="pages/feedback.php">Try the Feedback Page Again</a></p>
