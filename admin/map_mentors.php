<?php
require_once '../includes/db_config.php';
require_once 'includes/admin_auth.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mentor_id']) && isset($_POST['student_ids'])) {
    $mentorId = (int)$_POST['mentor_id'];
    $studentIds = is_array($_POST['student_ids']) ? $_POST['student_ids'] : [];
    
    try {
        $pdo->beginTransaction();
        
        // Verify mentor exists
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
        $stmt->execute([$mentorId]);
        $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$mentor) {
            throw new Exception("Selected mentor not found");
        }
        
        $inserted = 0;
        $stmt = $pdo->prepare("INSERT INTO mentor_student_mapping (mentor_id, student_id, status) VALUES (?, ?, 'approved')");
        
        foreach ($studentIds as $studentId) {
            $studentId = trim($studentId);
            if (empty($studentId)) continue;
            
            try {
                // Verify student exists and fetch details for notification
                $studentStmt = $pdo->prepare("SELECT id, name, usn FROM users WHERE id = ? AND designation = 'student'");
                $studentStmt->execute([$studentId]);
                $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$student) {
                    error_log("Student not found: $studentId");
                    continue;
                }
                
                // Check if user exists in users table
                $userStmt = $pdo->prepare("SELECT id FROM users WHERE id = ? OR USER_NAME = ?");
                $userStmt->execute([$studentId, $student['usn']]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    error_log("User not found for student: {$student['name']} (ID: $studentId)");
                    continue;
                }
                
                // Check if mapping already exists
                $checkStmt = $pdo->prepare("SELECT id FROM mentor_student_mapping WHERE mentor_id = ? AND student_id = ?");
                $checkStmt->execute([$mentorId, $user['id']]);
                
                if (!$checkStmt->fetch()) {
                    $stmt->execute([$mentorId, $user['id']]);
                    $inserted++;
                }
                
            } catch (PDOException $e) {
                error_log("Error processing student $studentId: " . $e->getMessage());
                continue;
            }
        }
        
        $pdo->commit();
        $message = "Successfully mapped $inserted students to mentor {$mentor['name']}";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
    
    // Redirect to avoid form resubmission
    header("Location: map_mentors.php?mentor_id=$mentorId");
    exit();
}

// Fetch all mentors (users who are directors)
$mentors = $pdo->query("
    SELECT id, name, email_id 
    FROM users 
    WHERE is_director = 0 AND is_active = 1
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$currentMentorId = isset($_GET['mentor_id']) ? (int)$_GET['mentor_id'] : 0;
$students = [];
$mappedStudents = [];

// If mentor is selected, fetch their students
if ($currentMentorId > 0) {
    // Fetch all students
    $students = $pdo->query("
        SELECT id, name, usn 
        FROM users 
        WHERE designation = 'student'
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch already mapped students
    $mappedStudents = $pdo->prepare("
        SELECT s.id, s.name, s.usn 
        FROM users s
        JOIN mentor_student_mapping m ON s.id = m.student_id
        WHERE m.mentor_id = ? AND s.designation = 'student'
        ORDER BY s.name
    ");
    $mappedStudents->execute([$currentMentorId]);
    $mappedStudents = $mappedStudents->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Students to Mentor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 15px;
            font-weight: 600;
        }
        .student-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .student-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .student-item:last-child {
            border-bottom: none;
        }
        .form-check {
            margin: 0;
            width: 100%;
        }
        .action-buttons {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4"><i class="fas fa-user-graduate"></i> Map Students to Mentor</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Select Mentor
                    </div>
                    <div class="card-body">
                        <form method="get" class="mb-0" id="mentorForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <select name="mentor_id" id="mentorSelect" class="form-select">
                                        <option value="">-- Select a Mentor --</option>
                                        <?php foreach ($mentors as $mentor): ?>
                                            <option value="<?php echo $mentor['id']; ?>" 
                                                <?php echo ($mentor['id'] == $currentMentorId) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($mentor['name'] . ' (' . $mentor['email_id'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($currentMentorId > 0): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Available Students
                            <span class="badge bg-primary float-end"><?php echo count($students); ?></span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($students)): ?>
                                <div class="p-3 text-muted text-center">No students available to map</div>
                            <?php else: ?>
                                <form id="mapForm" method="post" action="map_mentors.php">
                                    <input type="hidden" name="mentor_id" value="<?php echo $currentMentorId; ?>">
                                    <div class="student-list">
                                        <?php foreach ($students as $student): ?>
                                            <div class="student-item">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="student_ids[]" 
                                                           value="<?php echo htmlspecialchars($student['id']); ?>" 
                                                           id="student_<?php echo $student['id']; ?>">
                                                    <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                                        <?php echo htmlspecialchars($student['name'] . ' (' . $student['usn'] . ')'); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (!empty($students)): ?>
                                        <div class="action-buttons p-3 border-top">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-link"></i> Map Selected Students
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Mapped Students
                            <span class="badge bg-success float-end"><?php echo count($mappedStudents); ?></span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($mappedStudents)): ?>
                                <div class="p-3 text-muted text-center">No students mapped yet</div>
                            <?php else: ?>
                                <div class="student-list">
                                    <?php foreach ($mappedStudents as $student): ?>
                                        <div class="student-item">
                                            <div>
                                                <?php echo htmlspecialchars($student['name'] . ' (' . $student['usn'] . ')'); ?>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="unmap_student.php?mentor_id=<?php echo $currentMentorId; ?>&student_id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to unmap this student?')">
                                                    <i class="fas fa-unlink"></i> Unmap
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script>
    // Handle mentor selection
    document.getElementById('mentorSelect').addEventListener('change', function() {
        const mentorId = this.value;
        if (mentorId) {
            // Update URL without page reload
            const url = new URL(window.location.href);
            url.searchParams.set('mentor_id', mentorId);
            window.history.pushState({}, '', url);
            
            // Load students for the selected mentor
            loadStudents(mentorId);
        }
    });

    function loadStudents(mentorId) {
        if (!mentorId) return;
        
        const tbody = document.getElementById('studentsTableBody');
        if (!tbody) return;
        
        // Show loading state
        tbody.innerHTML = `
            <tr id="loadingRow">
                <td colspan="3" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>`;
            
        // Use the correct API endpoint
        fetch(`../api/get_students.php?mentor_id=${mentorId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const tbody = document.getElementById('studentsTableBody');
                const loadingRow = document.getElementById('loadingRow');
                
                // Remove loading row
                if (loadingRow) loadingRow.remove();

                if (data.status === 'success' && data.data && data.data.length > 0) {
                    // Clear existing rows
                    tbody.innerHTML = '';
                    
                    // Add students to the table
                    data.data.forEach((student, index) => {
                        const row = document.createElement('tr');
                        const isMapped = <?php echo json_encode(isset($mappedStudents) ? array_column($mappedStudents, 'id') : []); ?>.includes(parseInt(student.id));
                        
                        // Determine profile picture path
                        const profilePic = student.profile_picture || student.user_profile_picture || 'assets/images/default-avatar.png';
                        
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../${profilePic}" alt="${student.name || 'Student'}" 
                                         class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <div class="fw-medium">${student.name || 'N/A'}</div>
                                        ${student.usn ? `<small class="text-muted">${student.usn}</small>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm ${isMapped ? 'btn-success' : 'btn-primary'} map-btn" 
                                        data-student-id="${student.id}" 
                                        ${isMapped ? 'disabled' : ''}>
                                    <i class="fas ${isMapped ? 'fa-check' : 'fa-link'}"></i> ${isMapped ? 'Mapped' : 'Map'}
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });

                    // Add event listeners to map buttons
                    document.querySelectorAll('.map-btn:not(:disabled)').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const studentId = this.getAttribute('data-student-id');
                            const mentorId = document.getElementById('mentorSelect').value;
                            
                            if (!mentorId) {
                                showAlert('Please select a mentor first', 'warning');
                                return;
                            }
                            
                            // Add loading state
                            const originalText = this.innerHTML;
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mapping...';
                            
                            // Send mapping request
                            fetch('map_student.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `mentor_id=${mentorId}&student_id=${studentId}`
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(result => {
                                if (result.status === 'success') {
                                    // Show success message
                                    showAlert('Student mapped successfully!', 'success');
                                    
                                    // Reload the page to update the mapped students list
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    throw new Error(result.message || 'Failed to map student');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showAlert(error.message || 'An error occurred. Please try again.', 'danger');
                                this.innerHTML = originalText;
                                this.disabled = false;
                            });
                        });
                    });
                } else {
                    // No students found
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td colspan="3" class="text-center py-4">
                            No students found
                        </td>
                    `;
                    tbody.appendChild(row);
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                const tbody = document.getElementById('studentsTableBody');
                const loadingRow = document.getElementById('loadingRow');
                
                if (loadingRow) loadingRow.remove();
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="3" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Failed to load students. Please try again later.
                        <div class="small">${error.message}</div>
                    </td>
                `;
                tbody.appendChild(row);
            });
    }
    
    // Function to show alert messages
    function showAlert(message, type = 'success') {
        // Remove any existing alerts first
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remove alert after 5 seconds
        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
            alert.close();
        }, 5000);
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Load students if mentor is already selected
        const mentorId = new URLSearchParams(window.location.search).get('mentor_id');
        if (mentorId) {
            document.getElementById('mentorSelect').value = mentorId;
            loadStudents(mentorId);
        }
    });
    </script>
</body>
</html>

