<?php
session_start();
require_once '../includes/db_config.php';

// Check if user is admin (you may need to adjust this based on your auth system)
// For now, we'll allow access

$pageTitle = 'Manage Registration Requests';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $userId = $_POST['user_id'] ?? null;
    
    if ($requestId && $action && $userId) {
        try {
            if ($action === 'approve') {
                // Update user to active
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                $stmt->execute([$userId]);
                
                // Update request status
                $stmt = $pdo->prepare("UPDATE registration_requests SET status = 'approved', approved_at = NOW() WHERE id = ?");
                $stmt->execute([$requestId]);
                
                $message = "Registration approved successfully!";
                $messageType = "success";
            } elseif ($action === 'reject') {
                $reason = $_POST['reason'] ?? 'No reason provided';
                
                // Update request status
                $stmt = $pdo->prepare("UPDATE registration_requests SET status = 'rejected', rejection_reason = ? WHERE id = ?");
                $stmt->execute([$reason, $requestId]);
                
                // Optionally delete the user or keep them inactive
                // $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                // $stmt->execute([$userId]);
                
                $message = "Registration rejected successfully!";
                $messageType = "warning";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Fetch pending registration requests
$sql = "SELECT 
            rr.id as request_id,
            rr.user_id,
            rr.branch,
            rr.status,
            rr.created_at,
            u.name,
            u.email_id,
            u.usn,
            u.year_of_graduation
        FROM registration_requests rr
        JOIN users u ON rr.user_id = u.id
        WHERE rr.status = 'pending'
        ORDER BY rr.created_at DESC";

$stmt = $pdo->query($sql);
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Alumni Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-header {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .requests-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .requests-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .requests-table th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .requests-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .requests-table tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .no-requests {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-requests i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-user-check"></i> Manage Registration Requests</h1>
                <p>Review and approve/reject pending registration requests</p>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="requests-table">
                <?php if (count($pendingRequests) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>USN</th>
                                <th>Branch</th>
                                <th>Batch</th>
                                <th>Status</th>
                                <th>Requested On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingRequests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['email_id']); ?></td>
                                    <td><?php echo htmlspecialchars($request['usn']); ?></td>
                                    <td><?php echo htmlspecialchars($request['branch']); ?></td>
                                    <td><?php echo htmlspecialchars($request['year_of_graduation'] ?? 'N/A'); ?></td>
                                    <td><span class="badge badge-pending"><?php echo ucfirst($request['status']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $request['user_id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-approve" onclick="return confirm('Approve this registration?')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $request['user_id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="reason" value="Rejected by admin">
                                            <button type="submit" class="btn btn-reject" onclick="return confirm('Reject this registration?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-requests">
                        <i class="fas fa-inbox"></i>
                        <h3>No Pending Requests</h3>
                        <p>All registration requests have been processed.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
