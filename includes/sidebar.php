<?php
// includes/sidebar.php
// A simple navigation header for the mentorship dashboards

$is_student = isset($_SESSION['role']) && $_SESSION['role'] === 'student';
$is_alumni = isset($_SESSION['role']) && $_SESSION['role'] === 'alumni';

// Fetch unread notifications count
$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $notifStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $notifStmt->execute([$_SESSION['user_id']]);
        $unread_count = $notifStmt->fetchColumn();
    } catch (PDOException $e) {
        // Handle error silently
    }
}
?>
<style>
.dashboard-nav {
    background: #5b1f1f;
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.nav-links {
    display: flex;
    gap: 20px;
    align-items: center;
}
.nav-links a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    padding: 8px 12px;
    border-radius: 4px;
    transition: background 0.3s;
    position: relative;
}
.nav-links a:hover {
    background: rgba(255,255,255,0.1);
}
.dashboard-logo {
    font-size: 20px;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    color: white;
}
.notif-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
<div class="dashboard-nav">
    <a href="../index.php" class="dashboard-logo">
        <i class="fas fa-graduation-cap"></i> Alumni Connect
    </a>
    <div class="nav-links">
        <a href="../index.php"><i class="fas fa-home"></i> Home</a>
        <a href="../pages/directory.php"><i class="fas fa-users"></i> Directory</a>
        
        <?php if ($is_student): ?>
            <a href="../pages/student_dashboard.php" style="background: rgba(255,255,255,0.2);"><i class="fas fa-calendar-alt"></i> Connect with Alumni</a>
        <?php endif; ?>
        
        <?php if ($is_alumni): ?>
            <a href="../pages/meeting_requests.php" style="background: rgba(255,255,255,0.2);"><i class="fas fa-inbox"></i> Meeting Requests</a>
        <?php endif; ?>
        
        <a href="../pages/notifications.php">
            <i class="fas fa-bell"></i> Notifications
            <?php if ($unread_count > 0): ?>
                <span class="notif-badge"><?= $unread_count ?></span>
            <?php endif; ?>
        </a>
        
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
