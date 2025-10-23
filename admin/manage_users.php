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
        }
    }
}

// Get all users
try {
    $stmt = $pdo->query("
        SELECT * FROM users
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error loading users: " . $e->getMessage();
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
                                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                        <?php if ($user['username']): ?>
                                            <br>
                                            <small style="color: var(--text-light);">
                                                @<?php echo htmlspecialchars($user['username']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['graduation_year']): ?>
                                            <?php echo $user['graduation_year']; ?>
                                            <?php if ($user['degree']): ?>
                                                <br>
                                                <small style="color: var(--text-light);">
                                                    <?php echo htmlspecialchars($user['degree']); ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['current_company']): ?>
                                            <?php echo htmlspecialchars($user['current_company']); ?>
                                            <?php if ($user['current_position']): ?>
                                                <br>
                                                <small style="color: var(--text-light);">
                                                    <?php echo htmlspecialchars($user['current_position']); ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $user['location'] ? htmlspecialchars($user['location']) : '<span style="color: var(--text-light);">-</span>'; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="actions-cell">
                                        <?php if ($user['role'] !== 'admin'): ?>
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

    <script>
        function editUser(userId) {
            // Find the user data and populate the modal
            const users = <?php echo json_encode($users); ?>;
            const user = users.find(u => u.id == userId);

            if (user) {
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_full_name').value = user.full_name;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('edit_graduation_year').value = user.graduation_year || '';
                document.getElementById('edit_degree').value = user.degree || '';
                document.getElementById('edit_current_company').value = user.current_company || '';
                document.getElementById('edit_current_position').value = user.current_position || '';
                document.getElementById('edit_location').value = user.location || '';
                document.getElementById('edit_is_active').checked = user.is_active == 1;

                document.getElementById('editModal').classList.add('show');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
    </script>
</body>
</html>
