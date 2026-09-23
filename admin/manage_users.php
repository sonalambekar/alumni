<?php
// Admin User Management
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'edit_user') {
            // Update user
            $id = $_POST['user_id'] ?? 0;
            $email = $_POST['email'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $role = $_POST['role'] ?? 'alumni';
            $graduation_year = !empty($_POST['graduation_year']) ? $_POST['graduation_year'] : null;
            $degree = $_POST['degree'] ?? '';
            $current_company = $_POST['current_company'] ?? '';
            $current_position = $_POST['current_position'] ?? '';
            $location = $_POST['location'] ?? '';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($email) && !empty($full_name) && !empty($id)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET email = ?, full_name = ?, role = ?, graduation_year = ?, degree = ?, current_company = ?, current_position = ?, location = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$email, $full_name, $role, $graduation_year, $degree, $current_company, $current_position, $location, $is_active, $id]);

                    $success = "User updated successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating user: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'delete_user') {
            // Delete user (but not admin users)
            $id = $_POST['user_id'] ?? 0;

            if (!empty($id)) {
                try {
                    // Check if user is admin
                    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();

                    if ($user && $user['role'] !== 'admin') {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$id]);
                        $success = "User deleted successfully!";
                    } else {
                        $error = "Cannot delete admin users";
                    }
                } catch(PDOException $e) {
                    $error = "Error deleting user: " . $e->getMessage();
                }
            }
        } elseif ($action === 'add_user') {
            try {
                $name = $_POST['name'] ?? '';
                $email_id = $_POST['email_id'] ?? '';
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $usn = $_POST['usn'] ?? null;
                $year_of_graduation = !empty($_POST['year_of_graduation']) ? (int)$_POST['year_of_graduation'] : null;
                $phone_number = $_POST['phone_number'] ?? null;
                $institute = $_POST['institute'] ?? null;
                $branch = $_POST['branch'] ?? null;
                $designation = $_POST['designation'] ?? null;
                $is_director = isset($_POST['is_director']) ? 1 : 0;
                $is_active = 1; // New users are active by default
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        name, email_id, password, usn, year_of_graduation, 
                        phone_number, institute, branch, designation, 
                        is_director, is_active, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                    )
                ");
                
                $stmt->execute([
                    $name, $email_id, $password, $usn, $year_of_graduation,
                    $phone_number, $institute, $branch, $designation,
                    $is_director, $is_active
                ]);
                
                $success = "User added successfully!";
                // Refresh the page to show the new user
                header("Location: manage_users.php?success=user_added");
                exit();
                
            } catch(PDOException $e) {
                if ($e->getCode() == '23000') {
                    $error = "A user with this email already exists.";
                } else {
                    $error = "Error adding user: " . $e->getMessage();
                }
                error_log($error);
            }
        }
    }
}

// Get all users
try {
    // First, get the column names from the users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Debug: Output the column names
    error_log("Columns in users table: " . print_r($columns, true));
    
    // Get the user data
    $stmt = $pdo->query("
        SELECT * FROM users
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll();
    
    // Debug: Output the first user's data
    if (!empty($users)) {
        error_log("First user data: " . print_r($users[0], true));
    }
} catch(PDOException $e) {
    $error = "Error loading users: " . $e->getMessage();
    error_log($error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--primary-color);
        }
        
        .close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }
        
        .close:hover {
            color: #000;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-row .form-group {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #4a1a1a;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 999;
        }
        
        .fab:hover {
            background-color: #4a1a1a;
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }
        
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .admin-header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .admin-nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .admin-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .back-btn {
            background: var(--text-light);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #4b5563;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .users-table {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .table-header {
            background: var(--bg-light);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-color);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .role-admin {
            background: #dc2626;
            color: #ffffff;
        }

        .role-alumni {
            background: #059669;
            color: #ffffff;
        }

        .actions-cell {
            white-space: nowrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary {
            background: var(--text-light);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #dc2626;
            color: var(--white);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">User Management</div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Users List -->
        <div class="users-table">
            <div class="table-header">
                <h3 class="table-title"><i class="fas fa-users"></i> All Users</h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Graduation</th>
                            <th>Company</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($users) && !empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo !empty($user['name']) ? htmlspecialchars($user['name']) : 'N/A'; ?></strong>
                                        <?php if (!empty($user['usn'])): ?>
                                            <br>
                                            <small style="color: var(--text-light);">
                                                <?php echo htmlspecialchars($user['usn']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo !empty($user['email_id']) ? htmlspecialchars($user['email_id']) : 'N/A'; ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo htmlspecialchars($user['role'] ?? 'alumni'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'] ?? 'alumni')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($user['year_of_graduation'])): ?>
                                            <?php echo htmlspecialchars($user['year_of_graduation']); ?>
                                            <?php if (!empty($user['branch'])): ?>
                                                <br>
                                                <small style="color: var(--text-light);">
                                                    <?php echo htmlspecialchars($user['branch']); ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($user['institute'])): ?>
                                            <?php echo htmlspecialchars($user['institute']); ?>
                                            <?php if (!empty($user['designation'])): ?>
                                                <br>
                                                <small style="color: var(--text-light);">
                                                    <?php echo htmlspecialchars($user['designation']); ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($user['location'] ?? '') ? htmlspecialchars($user['location']) : '<span style="color: var(--text-light);">-</span>'; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo !empty($user['is_active']) ? 'status-active' : 'status-inactive'; ?>">
                                            <?php 
                                            if (isset($user['is_director']) && $user['is_director']) {
                                                echo 'Director';
                                            } else {
                                                echo !empty($user['is_active']) ? 'Active' : 'Inactive';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo !empty($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : 'N/A'; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <?php if (empty($user['is_director'])): ?>
                                            <button class="btn btn-secondary" onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: var(--text-light); font-size: 12px;">Admin User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No users found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="fab" onclick="document.getElementById('addUserModal').classList.add('show')">
        <i class="fas fa-plus"></i>
    </button>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <span class="close" onclick="closeModal('addUserModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="POST" action="">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email_id">Email *</label>
                        <input type="email" id="email_id" name="email_id" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="usn">USN</label>
                        <input type="text" id="usn" name="usn" class="form-control">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="year_of_graduation">Graduation Year</label>
                            <input type="number" id="year_of_graduation" name="year_of_graduation" class="form-control" min="1900" max="2099">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="branch">Branch</label>
                            <input type="text" id="branch" name="branch" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="institute">Institute/Company</label>
                        <input type="text" id="institute" name="institute" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="designation">Designation</label>
                        <input type="text" id="designation" name="designation" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="is_director">User Type</label>
                        <select id="is_director" name="is_director" class="form-control">
                            <option value="0">Regular User</option>
                            <option value="1">Director</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Add User</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editUser(userId) {
            // Find the user data and populate the modal
            const users = <?php echo json_encode($users ?? []); ?>;
            const user = users.find(u => u.id == userId);

            if (user) {
                document.getElementById('edit_user_id').value = user.id || '';
                document.getElementById('edit_email').value = user.email || '';
                document.getElementById('edit_full_name').value = user.full_name || '';
                document.getElementById('edit_role').value = user.role || 'alumni';
                document.getElementById('edit_graduation_year').value = user.graduation_year || '';
                document.getElementById('edit_degree').value = user.degree || '';
                document.getElementById('edit_current_company').value = user.current_company || '';
                document.getElementById('edit_current_position').value = user.current_position || '';
                document.getElementById('edit_location').value = user.location || '';
                document.getElementById('edit_is_active').checked = (user.is_active == 1);

                document.getElementById('editModal').classList.add('show');
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
</body>
</html>
