<?php
session_start();
require_once '../includes/db_config.php';

// Require authentication and student role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle meeting request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_meeting'])) {
    $mentor_id = $_POST['mentor_id'];
    $requested_date = $_POST['requested_date'];
    $requested_time = $_POST['requested_time'];
    $purpose = $_POST['purpose'];

    try {
        // Check if the exact same request already exists
        $checkStmt = $pdo->prepare("SELECT id FROM mentorship_requests WHERE student_id = ? AND mentor_id = ? AND requested_date = ? AND requested_time = ?");
        $checkStmt->execute([$student_id, $mentor_id, $requested_date, $requested_time]);
        
        if ($checkStmt->rowCount() > 0) {
            $error_msg = "You have already requested a meeting with this mentor for this date and time.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO mentorship_requests (student_id, mentor_id, requested_date, requested_time, purpose) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $mentor_id, $requested_date, $requested_time, $purpose]);
            
            $success_msg = "Meeting request sent successfully!";
        }
    } catch (PDOException $e) {
        $error_msg = "Error sending request: " . $e->getMessage();
    }
}

// Fetch available alumni (mentors)
$alumni = [];
try {
    $stmt = $pdo->query("SELECT id, name, email_id, profile_picture, institute, branch, designation, year_of_graduation as graduation_year FROM users WHERE is_director = 0 AND is_spoc = 0 LIMIT 100");
    $alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
}

// Fetch my meetings
$my_meetings = [];
try {
    $stmt = $pdo->prepare("
        SELECT mr.*, a.name as mentor_name, a.profile_picture, a.email_id as mentor_email, a.phone_number as mentor_phone 
        FROM mentorship_requests mr 
        JOIN users a ON mr.mentor_id = a.id 
        WHERE mr.student_id = ? 
        ORDER BY mr.requested_date DESC, mr.created_at DESC
    ");
    $stmt->execute([$student_id]);
    $my_meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect with Alumni</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .section-title { font-size: 26px; margin-bottom: 25px; color: #5b1f1f; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .filter-controls { display: flex; gap: 10px; flex-wrap: wrap; background: #f8f9fa; padding: 8px; border-radius: 30px; border: 1px solid #eee; }
        .filter-btn { background: transparent; color: #555; border: none; padding: 8px 18px; border-radius: 20px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .filter-btn:hover { color: #222; }
        .filter-btn.active { background: #5b1f1f; color: white; box-shadow: 0 2px 8px rgba(91,31,31,0.2); }

        .meetings-list { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 50px; }
        .meeting-item { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; display: flex; flex-direction: column; transition: transform 0.2s; position: relative; }
        .meeting-item:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
        .meeting-item[data-status="pending"] { border-top: 4px solid #ecc35c; }
        .meeting-item[data-status="accepted"] { border-top: 4px solid #28a745; }
        .meeting-item[data-status="rejected"] { border-top: 4px solid #dc3545; }
        .meeting-item[data-status="rescheduled"] { border-top: 4px solid #0056b3; }
        
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-accepted { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-rescheduled { background: #cce5ff; color: #004085; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 20px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; justify-content: center; }
        .btn-primary { background: #5b1f1f; color: white; width: 100%; }
        .btn-primary:hover { background: #7a2a2a; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(91,31,31,0.2); }
        .btn-outline { background: white; border: 1px solid #ddd; color: #333; border-radius: 6px; }
        .btn-outline:hover { background: #f8f9fa; border-color: #bbb; }
        
        .alumni-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .alumni-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; border: 1px solid #f0f0f0; transition: transform 0.3s, box-shadow 0.3s; }
        .alumni-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .card-header { padding: 25px; background: #ffffff; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 15px; }
        .profile-img { width: 65px; height: 65px; border-radius: 50%; object-fit: cover; border: 3px solid #f8f9fa; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .availability-badge { font-size: 12px; padding: 4px 10px; border-radius: 12px; margin-top: 5px; display: inline-flex; align-items: center; gap: 5px; font-weight: 600; }
        .avail-free { background: #e8f5e9; color: #2e7d32; }
        .avail-busy { background: #ffebee; color: #c62828; }
        .card-body { padding: 25px; flex-grow: 1; background: #fafafa; }
        .card-footer { padding: 20px 25px; border-top: 1px solid #f0f0f0; background: #ffffff; }
        
        /* Modal styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; backdrop-filter: blur(3px); }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 35px; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; font-family: 'Inter', sans-serif; }
        .form-control:focus { border-color: #5b1f1f; outline: none; box-shadow: 0 0 0 3px rgba(91,31,31,0.1); }
        .close-modal { float: right; cursor: pointer; font-size: 24px; color: #999; transition: color 0.2s; line-height: 1; }
        .close-modal:hover { color: #333; }
        
        .search-filter-container {
            display: flex; gap: 15px; align-items: center;
        }
        .search-box, .filter-box {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid #eaeaea;
            transition: all 0.3s ease;
        }
        .search-box:focus-within, .filter-box:focus-within {
            border-color: #5b1f1f;
            box-shadow: 0 4px 15px rgba(91, 31, 31, 0.1);
        }
        .search-box i.fa-search, .filter-box i.fa-filter {
            position: absolute;
            left: 15px;
            color: #888;
            font-size: 14px;
            z-index: 1;
        }
        .search-box input, .filter-box select {
            border: none;
            background: transparent;
            padding: 12px 20px 12px 40px;
            width: 220px;
            font-size: 14px;
            color: #333;
            outline: none;
            border-radius: 30px;
            font-family: 'Inter', sans-serif;
            position: relative;
            z-index: 2;
        }
        .filter-box select {
            appearance: none;
            cursor: pointer;
            padding-right: 35px;
        }
        .filter-box::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            color: #888;
            pointer-events: none;
            z-index: 1;
        }
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
                <h2 class="section-title" style="margin-bottom: 0;"><i class="fas fa-handshake"></i> My Meeting Requests</h2>
                <div class="filter-controls">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="accepted">Approved</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                </div>
            </div>
            <div class="meetings-list">
                <?php if (empty($my_meetings)): ?>
                    <p style="color: #666; text-align: center; padding: 20px;">You haven't requested any meetings yet.</p>
                <?php else: ?>
                    <?php foreach ($my_meetings as $meeting): ?>
                        <div class="meeting-item" data-status="<?= htmlspecialchars($meeting['status']) ?>">
                            <div style="padding: 20px; display: flex; flex-direction: column; height: 100%;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                    <h4 style="margin:0; font-size: 16px; color: #222;"><?= htmlspecialchars($meeting['mentor_name']) ?></h4>
                                    <span class="status-badge status-<?= $meeting['status'] ?>" style="font-size: 10px; padding: 4px 8px;"><?= ucfirst($meeting['status']) ?></span>
                                </div>
                                
                                <p style="margin:0 0 8px; font-size: 13px; color: #666; display: flex; align-items: center; gap: 8px;">
                                    <i class="far fa-calendar-check" style="color: #5b1f1f; width: 14px; text-align: center;"></i> <?= date('M d, Y', strtotime($meeting['requested_date'])) ?>
                                </p>
                                <p style="margin:0 0 15px; font-size: 13px; color: #666; display: flex; align-items: center; gap: 8px;">
                                    <i class="far fa-clock" style="color: #5b1f1f; width: 14px; text-align: center;"></i> <?= htmlspecialchars($meeting['requested_time']) ?>
                                </p>
                                
                                <?php if (($meeting['status'] === 'accepted' || $meeting['status'] === 'rescheduled') && !empty($meeting['meeting_link'])): ?>
                                    <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: auto;">
                                        <a href="<?= htmlspecialchars($meeting['meeting_link']) ?>" target="_blank" class="btn btn-outline" style="padding: 8px 12px; font-size: 12px; width: 100%; margin-bottom: 12px;">
                                            <i class="fas fa-video"></i> Join Meet
                                        </a>
                                        <div style="font-size: 11px; color: #555; background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px solid #eee; word-break: break-word;">
                                            <strong style="display:block; margin-bottom: 5px; color: #333;">Alumni Contact:</strong>
                                            <div style="margin-bottom: 4px; display: flex; gap: 6px; align-items: flex-start;"><i class="fas fa-envelope" style="margin-top: 2px; color: #777;"></i> <?= htmlspecialchars($meeting['mentor_email'] ?? 'N/A') ?></div>
                                            <div style="display: flex; gap: 6px; align-items: flex-start;"><i class="fas fa-phone" style="margin-top: 2px; color: #777;"></i> <?= htmlspecialchars(!empty($meeting['mentor_phone']) ? $meeting['mentor_phone'] : 'N/A') ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php
                // Extract unique branches
                $departments = [];
                foreach ($alumni as $a) {
                    $branch = trim($a['branch'] ?? '');
                    if (!empty($branch)) {
                        $branch = strtoupper($branch);
                        if (!in_array($branch, $departments)) {
                            $departments[] = $branch;
                        }
                    }
                }
                sort($departments);
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <h2 class="section-title" style="margin: 0;"><i class="fas fa-users"></i> Available Mentors (Alumni)</h2>
                <div class="search-filter-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="mentorSearch" placeholder="Search by mentor name...">
                    </div>
                    <div class="filter-box">
                        <i class="fas fa-filter"></i>
                        <select id="departmentFilter">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="alumni-grid" id="mentorsGrid">
                <?php foreach ($alumni as $alumnus): ?>
                    <div class="alumni-card">
                        <div class="card-header">
                            <?php 
                                $displayName = !empty(trim($alumnus['name'] ?? '')) ? htmlspecialchars(trim($alumnus['name'] ?? '')) : 'Unknown User';
                                $profilePic = !empty(trim($alumnus['profile_picture'] ?? '')) ? '../assets/images/profiles/' . htmlspecialchars(trim($alumnus['profile_picture'] ?? '')) : 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&size=60&background=5b1f1f&color=fff&bold=true';
                            ?>
                            <img src="<?= $profilePic ?>" class="profile-img" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($displayName) ?>&size=60&background=5b1f1f&color=fff&bold=true'">
                            <div>
                                <h3 class="mentor-name" style="font-size:18px; margin:0;"><?= $displayName ?></h3>
                                <div class="availability-badge <?= ($alumnus['availability_status'] ?? '') === 'busy' ? 'avail-busy' : 'avail-free' ?>">
                                    <i class="fas fa-circle" style="font-size:8px;"></i> <?= ucfirst($alumnus['availability_status'] ?? 'Free') ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <p style="font-size:14px; color:#444; margin-bottom:10px; display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-briefcase" style="color:#5b1f1f; margin-top: 3px;"></i> 
                                <?php 
                                    $designation = trim($alumnus['designation'] ?? '');
                                    if (empty($designation) || strtoupper($designation) === 'NA') {
                                        $designation = trim($alumnus['current_job'] ?? '');
                                        if (empty($designation) || strtoupper($designation) === 'NA') {
                                            $designation = 'Not specified';
                                        }
                                    }
                                ?>
                                <span style="line-height: 1.4;"><?= htmlspecialchars($designation) ?></span>
                            </p>
                            <p style="font-size:14px; color:#444; margin-bottom:10px; display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-university" style="color:#5b1f1f; margin-top: 3px;"></i> 
                                <span style="line-height: 1.4;"><?= !empty(trim($alumnus['institute'] ?? '')) ? htmlspecialchars(trim($alumnus['institute'])) : 'Not specified' ?></span>
                            </p>
                            <p style="font-size:14px; color:#444; margin-bottom:15px; display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-graduation-cap" style="color:#5b1f1f; margin-top: 3px;"></i> 
                                <span class="mentor-branch" style="line-height: 1.4;">
                                    <?= !empty(trim($alumnus['branch'] ?? '')) ? htmlspecialchars(trim($alumnus['branch'])) : 'Branch not specified' ?> 
                                    <?= !empty($alumnus['graduation_year']) ? '(' . htmlspecialchars($alumnus['graduation_year']) . ')' : '' ?>
                                </span>
                            </p>
                        </div>
                        <div class="card-footer">
                            <?php if (($alumnus['availability_status'] ?? '') === 'busy'): ?>
                                <button class="btn btn-primary" style="background:#ccc; cursor:not-allowed;" disabled>Currently Busy</button>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="openRequestModal(<?= $alumnus['id'] ?>, '<?= addslashes($displayName) ?>')">
                                    <i class="far fa-calendar-plus"></i> Request Slot
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Request Modal -->
    <div class="modal" id="requestModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeRequestModal()">&times;</span>
            <h3 style="margin-top:0; color:#5b1f1f;">Request Meeting with <span id="mentorName"></span></h3>
            <form method="POST" action="">
                <input type="hidden" name="mentor_id" id="mentorId">
                
                <div class="form-group">
                    <label>Preferred Date</label>
                    <input type="date" name="requested_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="form-group">
                    <label>Preferred Time</label>
                    <input type="time" name="requested_time" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Purpose of Meeting</label>
                    <textarea name="purpose" class="form-control" rows="4" required placeholder="Briefly describe what you'd like to discuss..."></textarea>
                </div>
                
                <button type="submit" name="request_meeting" class="btn btn-primary">Send Request</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        function openRequestModal(mentorId, mentorName) {
            document.getElementById('mentorId').value = mentorId;
            document.getElementById('mentorName').textContent = mentorName;
            document.getElementById('requestModal').classList.add('active');
        }
        
        function closeRequestModal() {
            document.getElementById('requestModal').classList.remove('active');
        }
        
        function closeAcceptModal() {
            document.getElementById('acceptModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('requestModal')) {
                closeRequestModal();
            }
        }
        
        // Search and Filter Mentors
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById('mentorSearch');
            const deptFilter = document.getElementById('departmentFilter');
            const mentorCards = document.querySelectorAll('.alumni-card');

            function filterMentors() {
                if (!searchInput || !deptFilter) return;
                
                const searchTerm = searchInput.value.toLowerCase();
                const deptValue = deptFilter.value.toLowerCase();

                mentorCards.forEach(card => {
                    const nameElem = card.querySelector('.mentor-name');
                    const branchElem = card.querySelector('.mentor-branch');
                    
                    const name = nameElem ? nameElem.innerText.toLowerCase() : '';
                    const branch = branchElem ? branchElem.innerText.toLowerCase() : '';

                    const matchesSearch = name.includes(searchTerm);
                    const matchesDept = deptValue === '' || branch.includes(deptValue);

                    if (matchesSearch && matchesDept) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            if (searchInput) searchInput.addEventListener('input', filterMentors);
            if (deptFilter) deptFilter.addEventListener('change', filterMentors);
        });

        // Meeting Request Filter Logic
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active button state
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const items = document.querySelectorAll('.meeting-item');
                let visibleCount = 0;
                
                items.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-status') === filter) {
                        item.style.display = 'flex';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Handle empty state text
                let emptyMsg = document.getElementById('no-meetings-msg');
                if (!emptyMsg && visibleCount === 0) {
                    const list = document.querySelector('.meetings-list');
                    list.insertAdjacentHTML('beforeend', '<p id="no-meetings-msg" style="color: #666; text-align: center; padding: 20px;">No requests found for this filter.</p>');
                } else if (emptyMsg && visibleCount > 0) {
                    emptyMsg.remove();
                } else if (emptyMsg && visibleCount === 0) {
                    emptyMsg.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>


