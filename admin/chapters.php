<?php
session_start();
require_once '../includes/db_config.php';
require_once 'includes/admin_auth.php';

// Check for success message in session
$message = '';
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle form submission
// Handle form submission with PRG pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        // Validate and sanitize input
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $contact_person = filter_input(INPUT_POST, 'contact_person', FILTER_SANITIZE_STRING);
        $contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_VALIDATE_EMAIL);
        $contact_phone = filter_input(INPUT_POST, 'contact_phone', FILTER_SANITIZE_STRING);
        $established_date = !empty($_POST['established_date']) ? $_POST['established_date'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($_POST['action'] === 'add') {
            // Insert new chapter
            $stmt = $pdo->prepare("INSERT INTO chapters (name, location, description, contact_person, contact_email, contact_phone, established_date, is_active) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $location, $description, $contact_person, $contact_email, $contact_phone, $established_date, $is_active]);
            // Redirect to prevent form resubmission
            $_SESSION['success_message'] = 'Chapter added successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // Update existing chapter
            $id = filter_input(INPUT_POST, 'chapter_id', FILTER_VALIDATE_INT);
            $update_fields = [];
            $params = [];
            
            $update_fields[] = "name = ?";
            $params[] = $name;
            $update_fields[] = "location = ?";
            $params[] = $location;
            $update_fields[] = "description = ?";
            $params[] = $description;
            $update_fields[] = "contact_person = ?";
            $params[] = $contact_person;
            $update_fields[] = "contact_email = ?";
            $params[] = $contact_email;
            $update_fields[] = "contact_phone = ?";
            $params[] = $contact_phone;
            $update_fields[] = "established_date = ?";
            $params[] = $established_date;
            $update_fields[] = "is_active = ?";
            $params[] = $is_active;
            
            $params[] = $id;
            $sql = "UPDATE chapters SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            // Redirect to prevent form resubmission
            $_SESSION['success_message'] = 'Chapter updated successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['chapter_id'])) {
        $id = filter_input(INPUT_POST, 'chapter_id', FILTER_VALIDATE_INT);
        $stmt = $pdo->prepare("DELETE FROM chapters WHERE id = ?");
        $stmt->execute([$id]);
        // Set success message in session
        $_SESSION['success_message'] = 'Chapter deleted successfully!';
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// We'll fetch chapters right before displaying them in the table
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Chapters - Admin Dashboard</title>
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
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --border-radius: 8px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
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
            box-shadow: var(--shadow);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .admin-nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user-info {
            text-align: right;
        }

        .admin-user-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .admin-user-role {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .logout-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #4a1919;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        .form-container {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-container h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #4a1919;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-edit {
            background-color: var(--info);
            color: white;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .btn-delete {
            background-color: var(--danger);
            color: white;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 20px;
        }

        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            margin: 0;
            font-size: 1.25rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--bg-light);
            font-weight: 600;
            color: var(--text-color);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .status-active {
            color: var(--success);
            font-weight: 600;
        }

        .status-inactive {
            color: var(--danger);
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">Chapters Management</div>
            <div class="admin-user">
                <div class="admin-user-info">
                    <div class="admin-user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="admin-user-role">Admin</div>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php if ($message): ?>
            <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #a7f3d0;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Add New Chapter Card -->
        <div class="card" style="margin-bottom: 30px; background: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow);">
            <div class="card-header" style="padding: 20px 25px; border-bottom: 1px solid var(--border-color);">
                <h3 style="margin: 0; display: flex; align-items: center; color: var(--primary-color);">
                    <i class="fas fa-plus-circle" style="margin-right: 10px;"></i> Add New Chapter
                </h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form id="chapterForm" method="POST" action="" onsubmit="return validateForm()">
                    <input type="hidden" name="action" value="add">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">Chapter Name *</label>
                            <input type="text" id="name" name="name" required 
                                   style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;">
                        </div>
                        <div class="form-group">
                            <label for="location" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">Location *</label>
                            <input type="text" id="location" name="location" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;">
                        </div>
                        <div class="form-group">
                            <label for="contact_person" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">Contact Person *</label>
                            <input type="text" id="contact_person" name="contact_person" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;">
                        </div>
                        <div class="form-group">
                            <label for="contact_email" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">Contact Email *</label>
                            <input type="email" id="contact_email" name="contact_email" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">Description</label>
                        <textarea id="description" name="description" rows="3" 
                                 style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn" style="background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 500; transition: background-color 0.2s;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Save Chapter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Chapters List -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title"><i class="fas fa-list"></i> All Chapters</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $chapters = $pdo->query("SELECT * FROM chapters ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                        if (count($chapters) > 0):
                            foreach ($chapters as $chapter):
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($chapter['name']); ?></strong>
                                <?php if (!empty($chapter['description'])): ?>
                                    <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 4px;">
                                        <?php echo nl2br(htmlspecialchars(substr($chapter['description'], 0, 100) . (strlen($chapter['description']) > 100 ? '...' : ''))); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($chapter['location']); ?></td>
                            <td>
                                <?php if (!empty($chapter['contact_person'])): ?>
                                    <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($chapter['contact_person']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($chapter['contact_email'])): ?>
                                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($chapter['contact_email']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($chapter['contact_phone'])): ?>
                                    <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($chapter['contact_phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $chapter['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $chapter['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-view-members" data-chapter-id="<?php echo $chapter['id']; ?>" data-chapter-name="<?php echo htmlspecialchars($chapter['name']); ?>">
                                    <i class="fas fa-users"></i> View Members
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this chapter? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="chapter_id" value="<?php echo $chapter['id']; ?>">
                                    <button type="submit" class="btn btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">
                                No chapters found. Add your first chapter above.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View Members Modal -->
    <div id="membersModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Chapter Members</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div id="membersList" class="members-list">
                    <!-- Members will be loaded here -->
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i> Loading members...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s;
        }

        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .modal-header {
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }

        .close-modal {
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #e0e0e0;
        }

        .modal-body {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .members-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .member-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 6px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid var(--primary-color);
        }

        .member-info {
            flex: 1;
        }

        .member-name {
            font-weight: 600;
            margin: 0 0 5px 0;
            color: var(--text-dark);
        }

        .member-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .member-detail {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .member-detail i {
            color: var(--primary-color);
            width: 16px;
            text-align: center;
        }

        .no-members {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }

        .loading-spinner {
            text-align: center;
            padding: 30px;
            color: var(--primary-color);
        }

        .loading-spinner i {
            margin-right: 10px;
        }

        /* Button styles */
        .btn-view-members {
            background-color: #4a6fdc;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-right: 8px;
            transition: background-color 0.2s;
        }

        .btn-view-members:hover {
            background-color: #3a5bc7;
        }

        .btn-view-members i {
            margin-right: 5px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }

            .member-card {
                flex-direction: column;
                text-align: center;
            }

            .member-avatar {
                margin: 0 0 10px 0;
            }

            .member-details {
                justify-content: center;
            }
        }
    </style>
    
    <script>
        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get modal elements
            const modal = document.getElementById('membersModal');
            const modalTitle = document.getElementById('modalTitle');
            const membersList = document.getElementById('membersList');
            const closeBtn = document.querySelector('.close-modal');
            
            // View Members button click handler
            document.querySelectorAll('.btn-view-members').forEach(button => {
                button.addEventListener('click', function() {
                    const chapterId = this.getAttribute('data-chapter-id');
                    const chapterName = this.getAttribute('data-chapter-name');
                    showMembersModal(chapterId, chapterName);
                });
            });
            
            // Close modal when clicking the X button
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Function to show members modal
            function showMembersModal(chapterId, chapterName) {
                // Update modal title
                modalTitle.textContent = `Members - ${chapterName}`;
                
                // Show loading state
                membersList.innerHTML = `
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i> Loading members...
                    </div>
                `;
                
                // Show modal
                modal.style.display = 'block';
                
                // Fetch members
                fetch(`/alumni/api/get_chapter_members.php?chapter_id=${chapterId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.members && data.members.length > 0) {
                            // Display members
                            const membersHtml = data.members.map(member => `
                                <div class="member-card">
                                    <div class="member-avatar" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px solid #5b1f1f;">
                                        <img src="${member.profile_picture || '/alumni/assets/images/default-avatar.png'}" 
                                             alt="${member.name || 'Alumni Member'}" 
                                             style="width: 100%; height: 100%; object-fit: cover;"
                                             onerror="this.onerror=null; this.style.display='none'; this.parentNode.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background-color:#5b1f1f;color:white;font-weight:bold;font-size:24px;\'>' + '${member.name ? member.name.charAt(0).toUpperCase() : 'A'}' + '</div>'">
                                    </div>
                                    <div class="member-info">
                                        <h4 class="member-name">${member.name || 'Alumni Member'}</h4>
                                        <div class="member-details">
                                            ${member.usn ? `
                                                <div class="member-detail">
                                                    <i class="fas fa-id-card"></i>
                                                    <span>${member.usn}</span>
                                                </div>
                                            ` : ''}
                                            ${member.email ? `
                                                <div class="member-detail">
                                                    <i class="fas fa-envelope"></i>
                                                    <a href="mailto:${member.email}">${member.email}</a>
                                                </div>
                                            ` : ''}
                                            ${member.phone ? `
                                                <div class="member-detail">
                                                    <i class="fas fa-phone"></i>
                                                    <a href="tel:${member.phone}">${member.phone}</a>
                                                </div>
                                            ` : ''}
                                            ${member.year_of_graduation ? `
                                                <div class="member-detail">
                                                    <i class="fas fa-graduation-cap"></i>
                                                    <span>Class of ${member.year_of_graduation}</span>
                                                </div>
                                            ` : ''}
                                            ${member.branch ? `
                                                <div class="member-detail">
                                                    <i class="fas fa-code-branch"></i>
                                                    <span>${member.branch}</span>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                            
                            membersList.innerHTML = membersHtml;
                        } else {
                            // No members found
                            membersList.innerHTML = `
                                <div class="no-members">
                                    <i class="fas fa-users-slash" style="font-size: 2rem; margin-bottom: 10px; color: #9ca3af;"></i>
                                    <p>No members found for this chapter.</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching members:', error);
                        membersList.innerHTML = `
                            <div class="no-members">
                                <i class="fas fa-exclamation-triangle" style="color: #ef4444; font-size: 2rem; margin-bottom: 10px;"></i>
                                <p>Error loading members. Please try again later.</p>
                                <small>${error.message}</small>
                            </div>
                        `;
                    });
            }
        });

        // Simple function to confirm delete action
        function confirmDelete() {
            return confirm('Are you sure you want to delete this chapter? This action cannot be undone.');
        }

        // Reset form after successful submission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Form validation
        function validateForm() {
            const form = document.getElementById('chapterForm');
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#ef4444';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields.');
            } else {
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }

            return isValid;
        }

        // Clear form after successful submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('chapterForm');
            if (form) {
                form.reset();
            }
        });
    </script>
</body>
</html>
