<?php
session_start();
require_once '../includes/db_config.php';

// Require authentication and alumni role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'alumni') {
    header("Location: ../login.php");
    exit();
}

$alumni_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Status Toggle (Free/Busy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['availability_status'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET availability_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $alumni_id]);
        $success_msg = "Availability status updated to " . ucfirst($new_status) . "!";
    } catch (PDOException $e) {
        $error_msg = "Error updating status: " . $e->getMessage();
    }
}

// Handle Accept/Reject Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_request'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; // 'accepted' or 'rejected'
    $meeting_link = isset($_POST['meeting_link']) ? $_POST['meeting_link'] : null;

    try {
        // Fetch student_id to send notification
        $getStudent = $pdo->prepare("SELECT student_id FROM mentorship_requests WHERE id = ?");
        $getStudent->execute([$request_id]);
        $student_id = $getStudent->fetchColumn();
        $alumni_name = $_SESSION['user_name'] ?? 'An Alumni';
        $link = "/alumni/pages/student_dashboard.php";

        if ($action === 'accepted') {
            $stmt = $pdo->prepare("UPDATE mentorship_requests SET status = 'accepted', meeting_link = ? WHERE id = ? AND mentor_id = ?");
            $stmt->execute([$meeting_link, $request_id, $alumni_id]);
            $success_msg = "Meeting request accepted and link sent!";
        } else if ($action === 'rescheduled') {
            $new_date = $_POST['new_date'];
            $new_time = $_POST['new_time'];
            $stmt = $pdo->prepare("UPDATE mentorship_requests SET status = 'rescheduled', requested_date = ?, requested_time = ?, meeting_link = ? WHERE id = ? AND mentor_id = ?");
            $stmt->execute([$new_date, $new_time, $meeting_link, $request_id, $alumni_id]);
            $success_msg = "Meeting request rescheduled and link sent!";
        } else if ($action === 'rejected') {
            $stmt = $pdo->prepare("UPDATE mentorship_requests SET status = 'rejected' WHERE id = ? AND mentor_id = ?");
            $stmt->execute([$request_id, $alumni_id]);
            $success_msg = "Meeting request rejected.";
        }
    } catch (PDOException $e) {
        $error_msg = "Error updating request: " . $e->getMessage();
    }
}

// Fetch my current availability status
$current_status = 'free';
try {
    $stmt = $pdo->prepare("SELECT availability_status FROM users WHERE id = ?");
    $stmt->execute([$alumni_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $current_status = $res['availability_status'] ?? 'free';
    }
} catch (PDOException $e) {}

// Fetch incoming requests
$incoming_requests = [];
try {
    $stmt = $pdo->prepare("
        SELECT mr.*, 
               u.name as student_name, 
               u.email_id as student_email, 
               u.usn as student_usn,
               u.institute, 
               u.branch 
        FROM mentorship_requests mr 
        LEFT JOIN users u ON mr.student_id = u.id
        WHERE mr.mentor_id = ? 
        ORDER BY mr.requested_date ASC, mr.created_at DESC
    ");
    $stmt->execute([$alumni_id]);
    $incoming_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Requests - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-container { padding: 40px; max-width: 1000px; margin: 0 auto; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .section-title { font-size: 26px; color: #5b1f1f; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
        
        .status-form { display: flex; align-items: center; gap: 12px; background: #f8f9fa; padding: 10px 15px; border-radius: 30px; border: 1px solid #eee; }
        .status-select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 20px; font-weight: 500; font-size: 14px; outline: none; background: white; cursor: pointer; }
        .status-select:focus { border-color: #5b1f1f; }
        
        .filter-controls { display: flex; gap: 10px; flex-wrap: wrap; background: #f8f9fa; padding: 8px; border-radius: 30px; border: 1px solid #eee; margin-bottom: 20px; }
        .filter-btn { background: transparent; color: #555; border: none; padding: 8px 18px; border-radius: 20px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .filter-btn:hover { color: #222; }
        .filter-btn.active { background: #5b1f1f; color: white; box-shadow: 0 2px 8px rgba(91,31,31,0.2); }
        
        .requests-list { display: flex; flex-direction: column; gap: 20px; }
        .request-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 0; overflow: hidden; border: 1px solid #f0f0f0; transition: transform 0.2s, box-shadow 0.2s; }
        .request-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        
        .req-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 25px; border-bottom: 1px solid #f0f0f0; background: #fafafa; border-left: 5px solid #ecc35c; }
        .request-card.status-accepted .req-header { border-left-color: #28a745; }
        .request-card.status-rejected .req-header { border-left-color: #dc3545; }
        
        .req-name { font-size: 18px; font-weight: 700; margin: 0 0 8px 0; color: #222; }
        .req-meta { font-size: 13px; color: #666; display: flex; gap: 15px; align-items: center; }
        .req-meta span { display: flex; align-items: center; gap: 6px; }
        .req-meta i { color: #5b1f1f; opacity: 0.8; }
        
        .req-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #444; }
        .req-purpose { background: #fdfdfd; padding: 15px; border-radius: 8px; border: 1px dashed #ddd; margin-top: 10px; font-style: italic; }
        
        .req-footer { padding: 15px 25px; display: flex; gap: 12px; align-items: center; background: #fafafa; border-top: 1px solid #f0f0f0; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-accept { background: #155724; color: white; }
        .btn-accept:hover { background: #0f3d19; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(21,87,36,0.2); }
        .btn-reject { background: white; color: #dc3545; border: 1px solid #dc3545; }
        .btn-reject:hover { background: #fff5f5; }
        .btn-reschedule { background: white; color: #0056b3; border: 1px solid #0056b3; }
        .btn-reschedule:hover { background: #e6f2ff; }
        .btn-primary { background: #5b1f1f; color: white; border-radius: 20px; padding: 8px 18px; }
        .btn-primary:hover { background: #7a2a2a; transform: translateY(-1px); }

        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-accepted { background: #d4edda; color: #155724; }
        .badge-rejected { background: #f8d7da; color: #721c24; }
        .badge-rescheduled { background: #cce5ff; color: #004085; }

        /* Modal styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .close-modal { float: right; cursor: pointer; font-size: 20px; color: #999; }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-container">
            <?php if ($success_msg): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;"><?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <div class="header-actions">
                <h2 class="section-title"><i class="fas fa-handshake"></i> Incoming Meeting Requests</h2>
                <div class="status-card" style="margin-bottom:0; padding:0; box-shadow:none;">
                    <form method="POST" class="status-form">
                        <label style="font-weight: 600; color: #444;">Status:</label>
                        <select name="availability_status" class="status-select">
                            <option value="free" <?= $current_status === 'free' ? 'selected' : '' ?>>🟢 Accepting requests</option>
                            <option value="busy" <?= $current_status === 'busy' ? 'selected' : '' ?>>🔴 Not accepting requests</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>

            <div class="filter-controls">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="pending">Pending</button>
                <button class="filter-btn" data-filter="accepted">Accepted/Rescheduled</button>
                <button class="filter-btn" data-filter="rejected">Rejected</button>
            </div>

            <div class="requests-list">
                <?php if (empty($incoming_requests)): ?>
                    <p style="color: #666; text-align: center; padding: 40px; background: white; border-radius: 8px;">No meeting requests yet.</p>
                <?php else: ?>
                    <?php foreach ($incoming_requests as $req): ?>
                        <div class="request-card status-<?= $req['status'] ?>" data-status="<?= htmlspecialchars($req['status']) ?>">
                            <div class="req-header">
                                <div>
                                    <h3 class="req-name"><?= htmlspecialchars($req['student_name'] ?? 'Unknown Student') ?></h3>
                                    <div class="req-meta">
                                        <?php if (!empty($req['institute']) || !empty($req['branch'])): ?>
                                            <span><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars(trim(($req['institute'] ?? '') . ' ' . ($req['branch'] ?? ''))) ?></span>
                                        <?php endif; ?>
                                        <span><i class="far fa-envelope"></i> <?= htmlspecialchars($req['student_email'] ?? '') ?></span>
                                    </div>
                                </div>
                                <span class="status-badge badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span>
                            </div>
                            
                            <div class="req-body">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; color: #333;">
                                    <i class="far fa-calendar-check" style="font-size: 18px; color: #5b1f1f;"></i>
                                    <strong>Requested Date & Time:</strong> 
                                    <?= date('l, M d, Y', strtotime($req['requested_date'])) ?> at <?= htmlspecialchars($req['requested_time']) ?>
                                </div>
                                <strong><i class="far fa-comment-alt" style="color: #5b1f1f;"></i> Purpose:</strong>
                                <div class="req-purpose">
                                    <?= nl2br(htmlspecialchars($req['purpose'] ?? 'No purpose provided.')) ?>
                                </div>
                            </div>

                            <div class="req-footer">
                                <?php if ($req['status'] === 'pending'): ?>
                                    <button class="btn btn-accept" onclick="openAcceptModal(<?= $req['id'] ?>)">
                                        <i class="fas fa-check"></i> Accept Request
                                    </button>
                                    <button class="btn btn-reschedule" onclick="openRescheduleModal(<?= $req['id'] ?>, '<?= $req['requested_date'] ?>', '<?= $req['requested_time'] ?>')">
                                        <i class="far fa-calendar-alt"></i> Reschedule
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="action" value="rejected">
                                        <button type="submit" name="action_request" class="btn btn-reject">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                <?php elseif ($req['status'] === 'accepted' || $req['status'] === 'rescheduled'): ?>
                                    <span style="color: #666; font-size: 14px;">
                                        <strong>Meeting Link:</strong> <a href="<?= htmlspecialchars($req['meeting_link']) ?>" target="_blank"><?= htmlspecialchars($req['meeting_link']) ?></a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Accept Modal -->
    <div class="modal" id="acceptModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeAcceptModal()">&times;</span>
            <h3 style="margin-top:0; color:#155724;">Accept Meeting Request</h3>
            <p style="font-size: 14px; color: #666;">Please provide a Google Meet or Zoom link for the student to join at the requested time.</p>
            <form method="POST" action="">
                <input type="hidden" name="request_id" id="acceptRequestId">
                <input type="hidden" name="action" value="accepted">
                
                <div class="form-group">
                    <label>Meeting Link (Google Meet, Zoom, etc.)</label>
                    <div style="margin-bottom: 10px;">
                        <a href="https://meet.google.com/new" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 13px; text-decoration: none; display: inline-block; border: 1px solid #ddd; color: #333; background: #f8f9fa; border-radius: 4px;">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/9b/Google_Meet_icon_%282020%29.svg" alt="Meet" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 5px;">
                            Create New Google Meet
                        </a>
                        <span style="font-size: 12px; color: #666; margin-left: 5px;">(Click to generate, then paste the link below)</span>
                    </div>
                    <input type="url" name="meeting_link" class="form-control" required placeholder="https://meet.google.com/xyz-abcd-efg">
                </div>
                
                <button type="submit" name="action_request" class="btn btn-accept" style="width: 100%;">Confirm & Send Link</button>
            </form>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div class="modal" id="rescheduleModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeRescheduleModal()">&times;</span>
            <h3 style="margin-top:0; color:#004085;">Reschedule Meeting</h3>
            <p style="font-size: 14px; color: #666;">Propose a new date and time for the meeting and provide a link.</p>
            <form method="POST" action="">
                <input type="hidden" name="request_id" id="rescheduleRequestId">
                <input type="hidden" name="action" value="rescheduled">
                
                <div class="form-group" style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label>New Date</label>
                        <input type="date" name="new_date" id="rescheduleDate" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div style="flex: 1;">
                        <label>New Time</label>
                        <input type="time" name="new_time" id="rescheduleTime" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Meeting Link (Google Meet, Zoom, etc.)</label>
                    <div style="margin-bottom: 10px;">
                        <a href="https://meet.google.com/new" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 13px; text-decoration: none; display: inline-block; border: 1px solid #ddd; color: #333; background: #f8f9fa; border-radius: 4px;">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/9b/Google_Meet_icon_%282020%29.svg" alt="Meet" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 5px;">
                            Create New Google Meet
                        </a>
                    </div>
                    <input type="url" name="meeting_link" class="form-control" required placeholder="https://meet.google.com/xyz-abcd-efg">
                </div>
                
                <button type="submit" name="action_request" class="btn btn-primary" style="width: 100%; background: #0056b3;">Confirm Reschedule</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        function openAcceptModal(requestId) {
            document.getElementById('acceptRequestId').value = requestId;
            document.getElementById('acceptModal').classList.add('active');
        }
        
        function closeAcceptModal() {
            document.getElementById('acceptModal').classList.remove('active');
        }

        function openRescheduleModal(requestId, oldDate, oldTime) {
            document.getElementById('rescheduleRequestId').value = requestId;
            document.getElementById('rescheduleDate').value = oldDate;
            document.getElementById('rescheduleTime').value = oldTime;
            document.getElementById('rescheduleModal').classList.add('active');
        }

        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').classList.remove('active');
        }

        window.onclick = function(event) {
            const acceptModal = document.getElementById('acceptModal');
            const rescheduleModal = document.getElementById('rescheduleModal');
            if (event.target === acceptModal) {
                closeAcceptModal();
            }
            if (event.target === rescheduleModal) {
                closeRescheduleModal();
            }
        }

        // Meeting Request Filter Logic
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Update active button state
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    const items = document.querySelectorAll('.request-card');
                    let visibleCount = 0;
                    
                    items.forEach(item => {
                        const status = item.getAttribute('data-status');
                        let shouldShow = false;
                        
                        if (filter === 'all') {
                            shouldShow = true;
                        } else if (filter === 'accepted' && (status === 'accepted' || status === 'rescheduled')) {
                            shouldShow = true;
                        } else if (filter === status) {
                            shouldShow = true;
                        }
                        
                        if (shouldShow) {
                            item.style.display = 'block';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    
                    // Handle empty state text
                    let emptyMsg = document.getElementById('no-requests-msg');
                    if (!emptyMsg && visibleCount === 0) {
                        const list = document.querySelector('.requests-list');
                        list.insertAdjacentHTML('beforeend', '<p id="no-requests-msg" style="color: #666; text-align: center; padding: 40px; background: white; border-radius: 8px;">No requests found for this filter.</p>');
                    } else if (emptyMsg && visibleCount > 0) {
                        emptyMsg.remove();
                    } else if (emptyMsg && visibleCount === 0) {
                        emptyMsg.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>

