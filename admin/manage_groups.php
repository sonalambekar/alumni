<?php
// Admin Interest Groups Management
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add_group') {
            // Add new interest group
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $category = $_POST['category'] ?? '';
            $group_image = $_POST['group_image'] ?? '';
            $max_members = !empty($_POST['max_members']) ? $_POST['max_members'] : null;

            if (!empty($name) && !empty($description)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO interest_groups (name, description, category, group_image, max_members, created_by)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $description, $category, $group_image, $max_members, $_SESSION['user_id']]);

                    $success = "Interest group created successfully!";
                } catch(PDOException $e) {
                    $error = "Error creating interest group: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'edit_group') {
            // Update interest group
            $id = $_POST['group_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $category = $_POST['category'] ?? '';
            $group_image = $_POST['group_image'] ?? '';
            $max_members = !empty($_POST['max_members']) ? $_POST['max_members'] : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($name) && !empty($description) && !empty($id)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE interest_groups
                        SET name = ?, description = ?, category = ?, group_image = ?, max_members = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $description, $category, $group_image, $max_members, $is_active, $id]);

                    $success = "Interest group updated successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating interest group: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'delete_group') {
            // Delete interest group
            $id = $_POST['group_id'] ?? 0;

            if (!empty($id)) {
                try {
                    $stmt = $pdo->delete("DELETE FROM interest_groups WHERE id = ?", [$id]);
                    $success = "Interest group deleted successfully!";
                } catch(PDOException $e) {
                    $error = "Error deleting interest group: " . $e->getMessage();
                }
            }
        }
    }
}

// Initialize variables
$groups = [];
$success = '';
$error = '';

// Define available categories
$categories = [
    'Career Development',
    'Entrepreneurship',
    'Technology & IT',
    'Research & Innovation',
    'Mentorship',
    'Global Network',
    'Volunteer & Community',
    'Sports & Recreation',
    'Arts & Culture',
    'Health & Wellness',
    'Finance & Investment',
    'Education',
    'Alumni Relations',
    'Professional Development',
    'Networking',
    'Startups',
    'Social Impact',
    'Women in Leadership',
    'Graduate Studies',
    'Industry Specific'
];

// Get all interest groups with member counts
try {
    // Check if tables exist before querying
    $tablesExist = true;
    try {
        $pdo->query("SELECT 1 FROM interest_groups LIMIT 1");
        $pdo->query("SELECT 1 FROM users LIMIT 1");
        $pdo->query("SELECT 1 FROM group_members LIMIT 1");
    } catch(PDOException $e) {
        $tablesExist = false;
    }

    if ($tablesExist) {
        try {
            // Try to get creator name, fallback to created_by if full_name doesn't exist
            $stmt = $pdo->query("
                SELECT ig.*,
                       COALESCE(u.full_name, u.name, CONCAT('User #', ig.created_by)) as creator_name,
                       COALESCE(COUNT(gm.id), 0) as current_members
                FROM interest_groups ig
                LEFT JOIN users u ON ig.created_by = u.id
                LEFT JOIN group_members gm ON ig.id = gm.group_id AND gm.is_active = 1
                GROUP BY ig.id
                ORDER BY ig.created_at DESC
            ");
            $groups = $stmt->fetchAll();

        } catch(PDOException $e) {
            // If the join fails, try without the creator name
            $stmt = $pdo->query("
                SELECT ig.*,
                       CONCAT('User #', ig.created_by) as creator_name,
                       COALESCE(COUNT(gm.id), 0) as current_members
                FROM interest_groups ig
                LEFT JOIN group_members gm ON ig.id = gm.group_id AND gm.is_active = 1
                GROUP BY ig.id
                ORDER BY ig.created_at DESC
            ");
            $groups = $stmt->fetchAll();
        }
    } else {
        $error = "Database tables not found. Please run the database setup first.";
    }
} catch(PDOException $e) {
    $error = "Error loading interest groups: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Interest Groups - Admin Dashboard</title>
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
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
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
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .admin-nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .admin-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .back-btn {
            background: var(--text-light);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #4b5563;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
        }

        .add-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .add-btn:hover {
            background: #4a1919;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .form-container h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #4a1919;
        }

        .btn-secondary {
            background: var(--text-light);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #dc2626;
            color: var(--white);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .group-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .group-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .group-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .group-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .group-category {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            background: rgba(91, 31, 31, 0.1);
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .group-description {
            color: var(--text-color);
            font-size: 14px;
            line-height: 1.5;
        }

        .group-footer {
            padding: 15px 20px;
            background: var(--bg-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .members-info {
            font-size: 12px;
            color: var(--text-light);
        }

        .group-actions {
            display: flex;
            gap: 8px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .members-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-top: 30px;
        }

        .members-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .members-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
        }

        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }

        .member-card {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 15px;
            background: var(--white);
        }

        .member-name {
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 4px;
        }

        .member-info {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .groups-grid {
                grid-template-columns: 1fr;
            }

            .members-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">Interest Groups Management</div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">

        <!-- Add Group Form -->
        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Create New Interest Group</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_group">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Group Name *</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="category">Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">Description *</label>
                    <textarea id="description" name="description" class="form-textarea" rows="4" required placeholder="Describe the group's purpose, activities, and what members can expect..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="group_image">Group Image URL</label>
                        <input type="url" id="group_image" name="group_image" class="form-input" placeholder="https://example.com/group-image.jpg">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_members">Max Members (Optional)</label>
                        <input type="number" id="max_members" name="max_members" class="form-input" min="1" placeholder="Leave empty for unlimited">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Group
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Groups Grid -->
        <div class="groups-grid">
            <?php if (isset($groups) && !empty($groups)): ?>
                <?php foreach ($groups as $group): ?>
                    <div class="group-card">
                        <div class="group-header">
                            <div class="group-title">
                                <i class="fas fa-users"></i>
                                <?php echo htmlspecialchars($group['name']); ?>
                            </div>
                            <?php if (isset($group['category']) && !empty($group['category'])): ?>
                                <div class="group-category"><?php echo htmlspecialchars($group['category']); ?></div>
                            <?php endif; ?>
                            <div class="group-description">
                                <?php echo htmlspecialchars(substr($group['description'], 0, 120)); ?>
                                <?php if (strlen($group['description']) > 120): ?>...<?php endif; ?>
                            </div>
                        </div>
                        <div class="group-footer">
                            <div class="members-info">
                                <i class="fas fa-user-friends"></i>
                                <?php echo isset($group['current_members']) ? $group['current_members'] : 0; ?> members
                                <?php if (isset($group['max_members']) && !empty($group['max_members'])): ?>
                                    (max: <?php echo $group['max_members']; ?>)
                                <?php endif; ?>
                            </div>
                            <div class="group-actions">
                                <span class="status-badge <?php echo (isset($group['is_active']) && $group['is_active']) ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo (isset($group['is_active']) && $group['is_active']) ? 'Active' : 'Inactive'; ?>
                                </span>
                                <button class="btn btn-secondary" onclick="viewMembers(<?php echo $group['id']; ?>)">
                                    <i class="fas fa-users"></i> Members
                                </button>
                                <button class="btn btn-secondary" onclick="editGroup(<?php echo $group['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this group?');">
                                    <input type="hidden" name="action" value="delete_group">
                                    <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-light);">
                    <i class="fas fa-users-cog" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                    No interest groups found. Create your first group above.
                </div>
            <?php endif; ?>
        </div>

        <!-- Members Section (Hidden by default) -->
        <div class="members-section" id="membersSection" style="display: none;">
            <div class="members-header">
                <h3 class="members-title" id="membersGroupTitle">Group Members</h3>
                <button class="btn btn-secondary" onclick="closeMembersView()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>

            <div class="members-grid" id="membersGrid">
                <!-- Members will be populated here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function editGroup(groupId) {
            // Find the group data and populate the modal
            const groups = <?php echo json_encode($groups); ?>;
            const group = groups.find(g => g.id == groupId);

            if (group) {
                document.getElementById('edit_group_id').value = group.id;
                document.getElementById('edit_name').value = group.name || '';
                document.getElementById('edit_category').value = group.category || '';
                document.getElementById('edit_description').value = group.description || '';
                document.getElementById('edit_group_image').value = group.group_image || '';
                document.getElementById('edit_max_members').value = group.max_members || '';
                document.getElementById('edit_is_active').checked = (group.is_active == 1);

                document.getElementById('editModal').classList.add('show');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        function viewMembers(groupId) {
            // Find the group data
            const groups = <?php echo json_encode($groups); ?>;
            const group = groups.find(g => g.id == groupId);

            if (group) {
                document.getElementById('membersGroupTitle').textContent = group.name + ' - Members (' + (group.current_members || 0) + ')';
                document.getElementById('membersSection').style.display = 'block';

                // Here you would typically make an AJAX call to get members
                // For now, we'll show a placeholder
                document.getElementById('membersGrid').innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-light);">
                        <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        Member management functionality would be implemented here.<br>
                        This would show a list of all members in the group with options to manage memberships.
                    </div>
                `;
            }
        }

        function closeMembersView() {
            document.getElementById('membersSection').style.display = 'none';
        }
    </script>
</body>
</html>
