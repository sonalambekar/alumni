<?php
// Admin Medals Management
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_config.php';

$pageTitle = 'Manage Medals';
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_medal'])) {
            // Add new medal
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
            
            if (!$user_id || empty($description)) {
                throw new Exception('Please fill in all required fields.');
            }
            
            // Get user details
            $stmt = $pdo->prepare("SELECT name, email_id, profile_picture FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            // Insert medal
            $stmt = $pdo->prepare("INSERT INTO medals (user_id, user_name, user_email, profile_image, description) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $user['name'],
                $user['email_id'],
                $user['profile_picture'],
                $description
            ]);
            
            $success = 'Medal awarded successfully!';
        } 
        elseif (isset($_POST['delete_medal'])) {
            // Delete medal
            $medal_id = filter_input(INPUT_POST, 'medal_id', FILTER_VALIDATE_INT);
            if ($medal_id) {
                $stmt = $pdo->prepare("DELETE FROM medals WHERE id = ?");
                $stmt->execute([$medal_id]);
                $success = 'Medal removed successfully!';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch all medals with basic user details
$stmt = $pdo->query("SELECT m.*, u.profile_picture 
                     FROM medals m 
                     LEFT JOIN users u ON m.user_id = u.id 
                     ORDER BY m.awarded_date DESC");
$medals = $stmt->fetchAll();

// Fetch users for dropdown
$users = $pdo->query("SELECT id, name, email_id FROM users ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medals - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --border-color: #e9ecef;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --border-radius: 8px;
            --box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .admin-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .admin-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header h5 i {
            color: var(--secondary-color);
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(91, 31, 31, 0.15);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #4a1919;
            border-color: #4a1919;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: var(--text-light);
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: var(--border-color);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(91, 31, 31, 0.03);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color);
        }

        .alert {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .empty-state h4 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        /* Custom styles for medals */
        .medal-card {
            border-left: 4px solid var(--secondary-color);
            transition: var(--transition);
        }

        .medal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .medal-icon {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-right: 1rem;
        }

        .medal-date {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .medal-description {
            margin: 0.5rem 0;
            color: var(--text-dark);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                border: none;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <a href="index.php" class="admin-brand">
                <i class="fas fa-trophy me-2"></i>Manage Medals
            </a>
            <div>
                <a href="admin_dashboard.php" class="btn btn-sm btn-outline-light me-2">
                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                </a>
                <a href="logout.php" class="btn btn-sm btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Add Medal Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Award New Medal</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Select User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">-- Select User --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name'] . ' (' . $user['email_id'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">Achievement Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_medal" class="btn btn-primary">
                            <i class="fas fa-trophy me-2"></i>Award Medal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Awarded Medals List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-award me-2"></i> Awarded Medals</h5>
            </div>
            <div class="card-body">
                <?php if (empty($medals)): ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy fa-4x"></i>
                        <h4>No medals awarded yet</h4>
                        <p>Start by awarding a medal to a user</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Achievement</th>
                                    <th>Date Awarded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medals as $medal): ?>
                                    <tr class="medal-card">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo !empty($medal['profile_image']) ? '../' . htmlspecialchars($medal['profile_image']) : 'https://via.placeholder.com/40'; ?>" 
                                                     class="user-avatar me-3" 
                                                     alt="<?php echo htmlspecialchars($medal['user_name']); ?>"
                                                     onerror="this.src='https://via.placeholder.com/40?text=User'">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($medal['user_name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($medal['user_email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-0 fw-medium"><?php echo htmlspecialchars($medal['description']); ?></p>
                                        </td>
                                        <td>
                                            <span class="medal-date">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo date('M j, Y', strtotime($medal['awarded_date'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this medal?');">
                                                <input type="hidden" name="medal_id" value="<?php echo $medal['id']; ?>">
                                                <button type="submit" name="delete_medal" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash-alt me-1"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
