<?php
require_once '../includes/db_config.php';
require_once __DIR__ . '/includes/admin_auth.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Handle mentor status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $mentorId = (int)$_POST['mentor_id'];
    $adminId = $_SESSION['user_id'];
    
    try {
        if ($_POST['action'] === 'approve') {
            $stmt = $pdo->prepare("
                UPDATE mentors 
                SET is_approved = 1, 
                    status = 'approved',
                    approved_at = NOW(),
                    approved_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$adminId, $mentorId]);
            
            $_SESSION['success'] = 'Mentor application approved successfully.';
        } 
        elseif ($_POST['action'] === 'reject') {
            $rejectionReason = $_POST['rejection_reason'] ?? 'Application rejected by administrator.';
            
            $stmt = $pdo->prepare("
                UPDATE mentors 
                SET is_approved = 0, 
                    status = 'rejected',
                    rejection_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$rejectionReason, $mentorId]);
            
            $_SESSION['success'] = 'Mentor application rejected.';
        }
        
        header('Location: manage_mentors.php');
        exit();
        
    } catch (PDOException $e) {
        error_log("Error updating mentor status: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred while processing your request.';
    }
}

// Fetch all mentor applications
$statusFilter = $_GET['status'] ?? 'pending';
$validStatuses = ['pending', 'approved', 'rejected'];
$statusFilter = in_array($statusFilter, $validStatuses) ? $statusFilter : 'pending';

try {
    $query = "
        SELECT m.*, u.name as admin_name 
        FROM mentors m
        LEFT JOIN users u ON m.approved_by = u.id
        WHERE m.status = ?
        ORDER BY m.created_at DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$statusFilter]);
    $mentors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching mentors: " . $e->getMessage());
    $mentors = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Mentors - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header h1 {
            font-size: 32px;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header h1 i {
            color: #5b1f1f;
        }
        
        .filter-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .filter-section label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .filter-select {
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            color: #2d3748;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            outline: none;
        }
        
        .filter-select:hover {
            border-color: #5b1f1f;
        }
        
        .filter-select:focus {
            border-color: #5b1f1f;
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 80px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            font-size: 18px;
            color: #718096;
        }
        
        .mentor-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .mentor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            border-color: #5b1f1f;
        }
        
        .mentor-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 20px;
        }
        
        .mentor-info h3 {
            font-size: 24px;
            color: #1a202c;
            margin-bottom: 12px;
        }
        
        .mentor-info p {
            color: #4a5568;
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .mentor-info i {
            color: #5b1f1f;
            width: 18px;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #000;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #f44336 0%, #ef5350 100%);
            color: white;
        }
        
        .expertise-section {
            margin: 20px 0;
        }
        
        .expertise-section h4 {
            font-size: 14px;
            color: #718096;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .expertise-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .expertise-tag {
            display: inline-block;
            background: linear-gradient(135deg, #5b1f1f 0%, #8b3a3a 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .bio-section {
            margin: 20px 0;
            padding: 20px;
            background: #f5f5f5;
            border-left: 4px solid #5b1f1f;
            border-radius: 4px;
        }
        
        .bio-section h4 {
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        .bio-section p {
            color: #4a5568;
            line-height: 1.6;
        }
        
        .rejection-reason {
            margin: 20px 0;
            padding: 20px;
            background: #fff5f5;
            border-radius: 12px;
            border-left: 4px solid #f44336;
        }
        
        .rejection-reason strong {
            color: #c53030;
            display: block;
            margin-bottom: 8px;
        }
        
        .rejection-reason p {
            color: #742a2a;
            line-height: 1.6;
        }
        
        .approval-details {
            margin-top: 20px;
            padding: 15px;
            background: #f0fff4;
            border-radius: 10px;
            border-left: 4px solid #4caf50;
        }
        
        .approval-details p {
            color: #2f855a;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #5b1f1f 0%, #8b3a3a 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(91, 31, 31, 0.2);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f44336 0%, #ef5350 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
        }
        
        .rejection-form {
            display: none;
            margin-top: 15px;
            width: 100%;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 200px;
            }
        }
        
        .rejection-form form {
            display: flex;
            gap: 10px;
            width: 100%;
            flex-wrap: wrap;
        }
        
        .rejection-form input[type="text"] {
            flex: 1;
            min-width: 250px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: all 0.3s;
        }
        
        .rejection-form input[type="text"]:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .mentor-header {
                flex-direction: column;
            }
            
            .filter-section {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-user-graduate"></i>
                Manage Mentor Applications
            </h1>
            <div class="filter-section">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter" class="filter-select" onchange="window.location.href='?status='+this.value">
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>⏳ Pending Review</option>
                    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>✅ Approved Mentors</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>❌ Rejected Applications</option>
                </select>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="mentor-list">
            <?php if (empty($mentors)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-friends"></i>
                    <p>No <?= $statusFilter ?> mentor applications found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($mentors as $mentor): ?>
                    <div class="mentor-card">
                        <div class="mentor-header">
                            <div class="mentor-info">
                                <h3><?= htmlspecialchars($mentor['name']) ?></h3>
                                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($mentor['email']) ?></p>
                                <p>
                                    <i class="fas fa-briefcase"></i> 
                                    <?= htmlspecialchars($mentor['profession']) ?>
                                    <?php if (!empty($mentor['company'])): ?>
                                        at <?= htmlspecialchars($mentor['company']) ?>
                                    <?php endif; ?>
                                </p>
                                <p><i class="fas fa-clock"></i> <?= $mentor['experience'] ?> years of experience</p>
                            </div>
                            <span class="status-badge status-<?= $mentor['status'] ?>">
                                <?= ucfirst($mentor['status']) ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($mentor['expertise'])): ?>
                            <div class="expertise-section">
                                <h4>Expertise</h4>
                                <div class="expertise-tags">
                                    <?php 
                                    $expertise = array_map('trim', explode(',', $mentor['expertise']));
                                    foreach ($expertise as $skill): 
                                    ?>
                                        <span class="expertise-tag"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($mentor['bio'])): ?>
                            <div class="bio-section">
                                <h4>About</h4>
                                <p><?= nl2br(htmlspecialchars($mentor['bio'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mentor['status'] === 'rejected' && !empty($mentor['rejection_reason'])): ?>
                            <div class="rejection-reason">
                                <strong>Rejection Reason:</strong>
                                <p><?= nl2br(htmlspecialchars($mentor['rejection_reason'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mentor['status'] === 'approved'): ?>
                            <div class="approval-details">
                                <p><strong>Approved by:</strong> <?= htmlspecialchars($mentor['admin_name'] ?? 'System') ?></p>
                                <p><strong>Approved on:</strong> <?= date('M j, Y g:i A', strtotime($mentor['approved_at'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mentor['status'] === 'pending'): ?>
                            <div class="action-buttons">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="mentor_id" value="<?= $mentor['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-danger" 
                                        onclick="showRejectionForm(<?= $mentor['id'] ?>)">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                            
                            <div id="rejectForm_<?= $mentor['id'] ?>" class="rejection-form">
                                <form method="post">
                                    <input type="hidden" name="mentor_id" value="<?= $mentor['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="text" name="rejection_reason" 
                                           placeholder="Enter reason for rejection (optional)">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-paper-plane"></i> Submit Rejection
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function showRejectionForm(mentorId) {
            const form = document.getElementById('rejectForm_' + mentorId);
            if (form) {
                // Hide all other rejection forms
                document.querySelectorAll('[id^="rejectForm_"]').forEach(el => {
                    if (el.id !== 'rejectForm_' + mentorId) {
                        el.style.display = 'none';
                    }
                });
                // Toggle current form
                form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
                // Focus on input if showing
                if (form.style.display === 'block') {
                    const input = form.querySelector('input[name="rejection_reason"]');
                    if (input) setTimeout(() => input.focus(), 100);
                }
            }
        }
    </script>
</body>
</html>