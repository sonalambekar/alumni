<?php
session_start();
require_once __DIR__ . '/includes/db_config.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if User is SPOC
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !isset($user['is_spoc']) || $user['is_spoc'] != 1) {
    // Not a SPOC
    header("Location: index.php");
    exit();
}

$spoc_branch = $user['branch'];
$message = '';
$error = '';

// Handle Actions (Accept/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if ($student_id && $action) {
        if ($action === 'accept') {
            try {
                $update = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ? AND branch = ?");
                $update->execute([$student_id, $spoc_branch]);
                $message = "Student accepted successfully.";
            } catch (PDOException $e) {
                $error = "Error accepting student: " . $e->getMessage();
            }
        } elseif ($action === 'reject') {
            try {
                $delete = $pdo->prepare("DELETE FROM users WHERE id = ? AND branch = ?");
                $delete->execute([$student_id, $spoc_branch]);
                $message = "Student request rejected.";
            } catch (PDOException $e) {
                $error = "Error rejecting student: " . $e->getMessage();
            }
        }
    }
}

// Handle Password Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            try {
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hashed_password, $_SESSION['user_id']]);
                $message = "Password updated successfully.";
            } catch (PDOException $e) {
                $error = "Error updating password: " . $e->getMessage();
            }
        } else {
            $error = "Incorrect current password.";
        }
    }
}

// Fetch Pending Requests for this Branch
try {
    // Check if is_active column exists (sanity check)
    // Assuming it does as per register.php
    $stmt = $pdo->prepare("SELECT * FROM users WHERE branch = ? AND is_active = 0 AND is_director = 0 ORDER BY id DESC");
    $stmt->execute([$spoc_branch]);
    $pending_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
    $pending_students = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPOC Dashboard - <?php echo htmlspecialchars($spoc_branch); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --white: #ffffff;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
        }

        .navbar {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-brief h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .dashboard-brief p {
            color: #666;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), #802e2e);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            min-width: 200px;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .request-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .request-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .request-header h2 {
            font-size: 1.25rem;
            color: var(--primary-color);
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th {
            text-align: left;
            padding: 1rem 1.5rem;
            background-color: #f8fafc;
            font-weight: 600;
            color: #64748b;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .student-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .student-table tr:last-child td {
            border-bottom: none;
        }

        .student-info {
            display: flex;
            flex-direction: column;
        }

        .student-name {
            font-weight: 600;
            color: var(--primary-color);
        }

        .student-email {
            font-size: 0.85rem;
            color: #64748b;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #fce7f3;
            color: #be185d;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: transform 0.1s;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-accept {
            background-color: var(--success);
            color: white;
        }

        .btn-accept:hover {
            background-color: #059669;
        }

        .btn-reject {
            background-color: var(--danger);
            color: white;
        }

        .btn-reject:hover {
            background-color: #dc2626;
        }

        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #64748b;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }

        /* Profile & Modal Styles */
        .profile-trigger {
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-trigger:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            filter: brightness(110%);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-university"></i>
            GMU Alumni SPOC
        </div>
        <div class="navbar-user">
            <div class="profile-trigger" onclick="openModal()">
                <i class="fas fa-user-circle" style="font-size: 1.2rem;"></i> 
                <span>
                    <?php echo htmlspecialchars($user['name']); ?> 
                    <span style="opacity:0.7">| <?php echo htmlspecialchars($spoc_branch); ?></span>
                </span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="dashboard-header">
            <div class="dashboard-brief">
                <h1>Dashboard</h1>
                <p>Manage pending student registrations for <strong><?php echo htmlspecialchars($spoc_branch); ?></strong></p>
            </div>
            <div class="stats-card">
                <div class="stats-number"><?php echo count($pending_students); ?></div>
                <div class="stats-label">Pending Requests</div>
            </div>
        </div>

        <div class="request-list">
            <div class="request-header">
                <h2>Member Requests</h2>
            </div>
            
            <?php if (empty($pending_students)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>All Caught Up!</h3>
                    <p>No pending registration requests for <?php echo htmlspecialchars($spoc_branch); ?> branch.</p>
                </div>
            <?php else: ?>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student Details</th>
                            <th>USN</th>
                            <th>Year</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_students as $student): ?>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <span class="student-name"><?php echo htmlspecialchars($student['name']); ?></span>
                                        <span class="student-email"><?php echo htmlspecialchars($student['email_id']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size: 1.1em;">
                                        <?php echo htmlspecialchars($student['usn']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($student['year_of_graduation']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone_number']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="action" value="accept" class="btn btn-accept">
                                                <i class="fas fa-check"></i> Accept
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-reject">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Password</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" name="update_password" class="btn-submit">Update Password</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('passwordModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            const modal = document.getElementById('passwordModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>