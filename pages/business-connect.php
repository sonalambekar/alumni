<?php
require_once '../includes/db_config.php';

// First, let's check the actual columns in the users table
try {
    // Debug: Show all tables in the database
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    error_log("Available tables: " . print_r($tables, true));
    
    // Get the actual columns from the users table
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $firstUser = $stmt->fetch(PDO::FETCH_ASSOC);
    $columns = array_keys($firstUser);
    $stmt->closeCursor();
    
    // Debug: Show columns and first user data
    error_log("Columns in users table: " . print_r($columns, true));
    error_log("First user data: " . print_r($firstUser, true));
    
    // Debug: Get sample of users with their roles/director status
    // First, get all available columns that we can use for identification
    $idColumns = [];
    $possibleIdColumns = ['id', 'user_id', 'email', 'name', 'full_name'];
    foreach ($possibleIdColumns as $col) {
        if (in_array($col, $columns)) {
            $idColumns[] = $col;
        }
    }
    
    // If no ID columns found, just use the first few columns
    if (empty($idColumns)) {
        $idColumns = array_slice($columns, 0, 3);
    }
    
    // Build debug query with available columns
    $debugColumns = array_merge($idColumns, ['is_active']);
    
    // Add role-related columns if they exist
    $roleColumns = ['role', 'user_type', 'is_admin', 'is_director', 'type', 'user_role'];
    foreach ($roleColumns as $col) {
        if (in_array($col, $columns) && !in_array($col, $debugColumns)) {
            $debugColumns[] = $col;
        }
    }
    
    try {
        $debugQuery = "SELECT " . implode(", ", $debugColumns) . " FROM users LIMIT 10";
        $debugStmt = $pdo->query($debugQuery);
        $sampleUsers = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Sample users with role info: " . print_r($sampleUsers, true));
    } catch (PDOException $e) {
        error_log("Debug query failed: " . $e->getMessage());
        // Continue execution even if debug query fails
    }
    
    // Define the columns we'd like to display
    $desiredColumns = [
        'id',
        'username',
        'email',
        'name',
        'first_name',
        'last_name',
        'full_name',
        'graduation_year',
        'passout_year',
        'institute',
        'current_company',
        'current_position',
        'location',
        'profile_image'
    ];
    
    // Only include columns that exist in the database
    $selectedColumns = array_intersect($desiredColumns, $columns);
    
    if (empty($selectedColumns)) {
        // If no desired columns are found, get all columns
        $stmt = $pdo->query("SHOW COLUMNS FROM users");
        $selectedColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
    }
    
    // Ensure we have at least some basic columns
    if (empty($selectedColumns)) {
        throw new Exception("Could not determine columns in users table");
    }
    
    // Debug: Check if user_groups or similar table exists
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    error_log("Available tables: " . print_r($tables, true));
    
    // Check if we have a groups table and get its structure
    $groupTables = array_filter($tables, function($table) {
        return stripos($table, 'group') !== false || stripos($table, 'interest') !== false;
    });
    
    // If we have group tables, log their structure
    foreach ($groupTables as $table) {
        try {
            $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
            error_log("Table $table columns: " . print_r($columns, true));
            
            // Log sample data from the group table
            $sampleData = $pdo->query("SELECT * FROM $table LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            error_log("Sample data from $table: " . print_r($sampleData, true));
        } catch (Exception $e) {
            error_log("Error checking table $table: " . $e->getMessage());
        }
    }
    
    // Build the query - only show active non-admin, non-director users
    $query = "SELECT " . implode(", ", $selectedColumns) . " FROM users WHERE is_active = 1 ";
    
    // Check if we have interest_groups and group_members tables
    if (in_array('interest_groups', $tables) && in_array('group_members', $tables)) {
        // Query to exclude users who are members of the Sports and Recreation group
        $query = "SELECT DISTINCT u.* FROM users u ";
        $query .= "WHERE u.is_active = 1 ";
        $query .= "AND u.id NOT IN (";
        $query .= "    SELECT gm.user_id FROM group_members gm ";
        $query .= "    JOIN interest_groups ig ON gm.group_id = ig.id ";
        $query .= "    WHERE ig.name LIKE '%sports%' OR ig.name LIKE '%recreation%' ";
        $query .= ") ";
    }
    
    // Get all possible admin/director indicators from the database
    $conditions = [];
    
    // Check for director indicators
    $directorColumns = ['is_director', 'director', 'user_type', 'role'];
    foreach ($directorColumns as $col) {
        if (in_array($col, $selectedColumns)) {
            if ($col === 'is_director' || $col === 'director') {
                $conditions[] = "($col != 1 AND $col IS NOT NULL)";
            } else {
                $conditions[] = "($col NOT LIKE '%director%' OR $col IS NULL)";
            }
        }
    }
    
    // Check for admin indicators
    $adminColumns = ['is_admin', 'admin', 'user_type', 'role'];
    foreach ($adminColumns as $col) {
        if (in_array($col, $selectedColumns)) {
            if ($col === 'is_admin' || $col === 'admin') {
                $conditions[] = "($col != 1 AND $col IS NOT NULL)";
            } else {
                $conditions[] = "($col NOT LIKE '%admin%' OR $col IS NULL)";
            }
        }
    }
    
    // Add all conditions to the query
    if (!empty($conditions)) {
        $query .= " AND (" . implode(" AND ", $conditions) . ")";
    }
    
    // Add ORDER BY with fallbacks
    if (in_array('full_name', $selectedColumns)) {
        $query .= " ORDER BY full_name";
    } elseif (in_array('name', $selectedColumns)) {
        $query .= " ORDER BY name";
    } elseif (in_array('username', $selectedColumns)) {
        $query .= " ORDER BY username";
    } elseif (in_array('email', $selectedColumns)) {
        $query .= " ORDER BY email";
    } elseif (in_array('id', $selectedColumns)) {
        $query .= " ORDER BY id";
    } else {
        // If no suitable column is found, use the first available column
        $firstColumn = !empty($selectedColumns) ? reset($selectedColumns) : 'id';
        $query .= " ORDER BY " . $firstColumn;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Show the actual query and number of users found
    error_log("Query: $query");
    error_log("Number of users found: " . count($users));
    if (!empty($users)) {
        error_log("First user in results: " . print_r($users[0], true));
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Connect - Alumni Network</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .business-connect {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
        }
        
        .page-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .users-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: #555;
        }
        
        .users-table tr:last-child td {
            border-bottom: none;
        }
        
        .users-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .user-name {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            margin: 0.5rem 0;
            color: #555;
        }
        
        .empty-state p {
            color: #777;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .business-connect {
                padding: 1rem;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .page-header {
                padding: 1rem;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1>Business Connect</h1>
            <p>Connect with fellow alumni and expand your professional network</p>
        </div>

        <div class="business-connect">
            <?php if (!empty($users)): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Alumni</th>
                            <th>Email</th>
                            <th>Graduation Year</th>
                            <th>Company/Institute</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-name">
                                        <?php
                                        // Determine display name
                                        $displayName = '';
                                        $initials = '??';
                                        
                                        if (isset($user['full_name']) && !empty($user['full_name'])) {
                                            $displayName = $user['full_name'];
                                        } elseif (isset($user['name']) && !empty($user['name'])) {
                                            $displayName = $user['name'];
                                        } elseif (isset($user['first_name']) && isset($user['last_name'])) {
                                            $displayName = trim($user['first_name'] . ' ' . $user['last_name']);
                                        } elseif (isset($user['username'])) {
                                            $displayName = $user['username'];
                                        } else {
                                            $displayName = 'Alumni User';
                                        }
                                        
                                        // Generate initials
                                        if (!empty($displayName)) {
                                            $nameParts = explode(' ', $displayName);
                                            $initials = '';
                                            foreach ($nameParts as $part) {
                                                if (!empty($part)) {
                                                    $initials .= strtoupper(substr($part, 0, 1));
                                                    if (strlen($initials) >= 2) break;
                                                }
                                            }
                                            if (empty($initials)) $initials = '??';
                                        }
                                        ?>
                                        
                                        <?php if (isset($user['profile_image']) && !empty($user['profile_image'])): ?>
                                            <div class="user-avatar">
                                                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($displayName); ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="user-avatar" style="background-color: #<?php echo substr(md5($user['id'] ?? 'user'), 0, 6); ?>">
                                                <?php echo $initials; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <span><?php echo htmlspecialchars($displayName); ?></span>
                                    </div>
                                </td>
                                <td><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'N/A'; ?></td>
                                <td>
                                    <?php
                                    $year = '';
                                    if (isset($user['graduation_year']) && !empty($user['graduation_year'])) {
                                        $year = $user['graduation_year'];
                                    } elseif (isset($user['passout_year']) && !empty($user['passout_year'])) {
                                        $year = $user['passout_year'];
                                    }
                                    echo !empty($year) ? htmlspecialchars($year) : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $info = '';
                                    if (isset($user['current_company']) && !empty($user['current_company'])) {
                                        $info = $user['current_company'];
                                    } elseif (isset($user['institute']) && !empty($user['institute'])) {
                                        $info = $user['institute'];
                                    } elseif (isset($user['location']) && !empty($user['location'])) {
                                        $info = $user['location'];
                                    }
                                    echo !empty($info) ? htmlspecialchars($info) : 'N/A';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No users found</h3>
                    <p>There are currently no alumni in the network.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Add any additional JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Make table rows clickable if needed
            const rows = document.querySelectorAll('.users-table tbody tr');
            rows.forEach(row => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function() {
                    // Add click handler if needed
                    console.log('Row clicked:', this);
                });
            });
        });
    </script>
</body>
</html>