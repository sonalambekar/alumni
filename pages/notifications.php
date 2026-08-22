<?php
session_start();
require_once '../includes/db_config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle Mark as Read
if (isset($_GET['mark_read'])) {
    if ($_GET['mark_read'] === 'all') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $notif_id = (int)$_GET['mark_read'];
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notif_id, $user_id]);
    }
    header("Location: notifications.php");
    exit();
}

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Gems of GM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-container { padding: 40px; max-width: 900px; margin: 0 auto; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .section-title { font-size: 26px; color: #5b1f1f; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
        
        .btn-mark-read { background: white; border: 1px solid #ddd; padding: 8px 18px; border-radius: 20px; cursor: pointer; text-decoration: none; color: #333; font-size: 14px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-mark-read:hover { background: #f8f9fa; border-color: #bbb; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        
        .notif-list { display: flex; flex-direction: column; gap: 15px; }
        
        .notif-card { background: white; border-radius: 12px; padding: 20px 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid transparent; display: flex; justify-content: space-between; align-items: center; border-right: 1px solid #f0f0f0; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; transition: transform 0.2s; gap: 20px; }
        .notif-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
        .notif-card.unread { border-left-color: #e74c3c; background: #fffaf9; }
        .notif-card.read { border-left-color: #ddd; }
        
        .notif-content { flex-grow: 1; }
        .notif-message { color: #222; font-size: 16px; font-weight: 500; line-height: 1.5; }
        .notif-time { color: #666; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 6px; }
        
        .notif-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .btn-view { background: #5b1f1f; color: white; border: none; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s; }
        .btn-view:hover { background: #7a2a2a; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(91,31,31,0.2); color: white; }
        .btn-check { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: white; border: 1px solid #ddd; color: #666; text-decoration: none; transition: all 0.2s; }
        .btn-check:hover { background: #e74c3c; color: white; border-color: #e74c3c; }
        
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .empty-icon { font-size: 48px; color: #ddd; margin-bottom: 15px; }
        .empty-title { font-size: 20px; color: #444; margin: 0 0 10px 0; font-weight: 600; }
        .empty-desc { color: #777; margin: 0; }
    </style>
</head>
<body>

<?php include '../sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-container">
        <div class="header-actions">
            <h2 class="section-title"><i class="fas fa-bell"></i> Your Notifications</h2>
            <?php if (count($notifications) > 0): ?>
                <a href="?mark_read=all" class="btn-mark-read"><i class="fas fa-check-double"></i> Mark all as read</a>
            <?php endif; ?>
        </div>

        <div class="notif-list">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-card <?= $n['is_read'] ? 'read' : 'unread' ?>">
                        <div class="notif-content">
                            <div class="notif-message">
                                <?= htmlspecialchars($n['message']) ?>
                            </div>
                            <div class="notif-time">
                                <i class="far fa-clock"></i> <?= date('M d, Y - h:i A', strtotime($n['created_at'])) ?>
                            </div>
                        </div>
                        <div class="notif-actions">
                            <?php if (!empty($n['link'])): ?>
                                <a href="<?= htmlspecialchars($n['link']) ?>" class="btn-view">View</a>
                            <?php endif; ?>
                            <?php if (!$n['is_read']): ?>
                                <a href="?mark_read=<?= $n['id'] ?>" class="btn-check" title="Mark as Read"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-bell-slash empty-icon"></i>
                    <h3 class="empty-title">No notifications yet</h3>
                    <p class="empty-desc">You're all caught up!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
