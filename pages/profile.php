<?php
session_start();
require_once '../includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=profile");
    exit();
}

// Get user data
$user = getCurrentUser();

// If user not found, log out and redirect
if (!$user) {
    session_destroy();
    header("Location: ../login.php?error=session_expired");
    exit();
}

// Get user's groups
$userGroups = [];
try {
    $stmt = $pdo->prepare("
        SELECT g.id, g.name, g.description, g.group_image 
        FROM interest_groups g
        JOIN group_members gm ON g.id = gm.group_id
        WHERE gm.user_id = ? AND gm.is_active = 1 AND g.is_active = 1
        ORDER BY g.name
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $userGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error but don't break the page
    error_log("Error fetching user groups: " . $e->getMessage());
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // 1. Handle profile picture upload if a new one was provided
        $profilePicturePath = $user['profile_picture'] ?? ''; // Keep existing picture by default
        
        // Check if a new file was uploaded
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../attachments/profile_pictures/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            // Generate a safe filename
            $originalName = preg_replace('/[^\w\-\.]/', '_', $_FILES['profile_picture']['name']);
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                // Verify it's an actual image
                $check = @getimagesize($_FILES['profile_picture']['tmp_name']);
                if ($check !== false) {
                    // Delete old profile picture if it exists and is different from the new one
                    if (!empty($profilePicturePath) && file_exists('../' . $profilePicturePath)) {
                        @unlink('../' . $profilePicturePath);
                    }
                    
                    // Move the uploaded file
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                        $profilePicturePath = 'attachments/profile_pictures/' . $fileName;
                    } else {
                        throw new Exception('Failed to move uploaded file.');
                    }
                } else {
                    throw new Exception('File is not a valid image.');
                }
            } else {
                throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed.');
            }
        }
        
        // 2. Update user data in database
        $updateFields = [
            'name' => $name,
            'bio' => $bio,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Handle profile picture removal or update
        if (isset($_POST['remove_profile_picture']) && $_POST['remove_profile_picture'] === 'on') {
            // Remove profile picture if the checkbox is checked
            if (!empty($user['profile_picture']) && file_exists('../' . $user['profile_picture'])) {
                @unlink('../' . $user['profile_picture']);
            }
            $updateFields['profile_picture'] = '';
        } elseif (!empty($profilePicturePath)) {
            // Update with new profile picture path if we have one
            $updateFields['profile_picture'] = $profilePicturePath;
        }
        
        // Build and execute the update query
        $setClause = [];
        $params = [];
        foreach ($updateFields as $field => $value) {
            $setClause[] = "`$field` = ?";
            $params[] = $value;
        }
        $params[] = $_SESSION['user_id'];
        
        $sql = "UPDATE `users` SET " . implode(', ', $setClause) . " WHERE `id` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Commit the transaction
        $pdo->commit();
        
        // Update session data and refresh user data
        $_SESSION['user_avatar'] = !empty($updateFields['profile_picture']) 
            ? '../' . $updateFields['profile_picture'] 
            : '../assets/images/default-avatar.png';
            
        // Refresh user data
        $user = getCurrentUser();
        $success = "Profile updated successfully!";
    } catch (PDOException $e) {
        // Rollback the transaction on error
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Error updating profile: " . $e->getMessage();
    }
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        /* Group Members Modal */
        .group-members-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }
        
        .group-members-content {
            background: white;
            margin: 50px auto;
            max-width: 800px;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
        }
        
        .group-members-header {
            background: #5b1f1f;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .group-members-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px 10px;
        }
        
        .group-members-body {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .member-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .member-card {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .member-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }
        
        .member-info h4 {
            margin: 0;
            font-size: 0.95rem;
            color: #333;
        }
        
        .member-info p {
            margin: 3px 0 0;
            font-size: 0.8rem;
            color: #777;
        }
        
        .loading-members {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .no-members {
            text-align: center;
            padding: 20px;
            color: #666;
            grid-column: 1 / -1;
        }
        
        .group-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .group-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        
        .profile-details {
            flex: 1;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        .profile-groups {
            width: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        .group-card {
            background: #fff;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            border: 1px solid #eee;
        }
        
        .group-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .group-card h4 {
            color: #333;
            margin: 15px 15px 10px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .group-card p {
            color: #666;
            font-size: 0.9rem;
            margin: 0 15px 15px;
            line-height: 1.5;
            min-height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        .group-image-container {
            width: 100%;
            height: 160px;
            overflow: hidden;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #eee;
        }
        
        .group-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .group-card:hover .group-image {
            transform: scale(1.05);
        }
        
        .group-card .btn {
            display: block;
            margin: 0 15px 15px;
            text-align: center;
            background: #5b1f1f !important;
            color: white !important;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }
        
        .group-card .btn:hover {
            background: #4a1919 !important;
        }
        
        .no-groups {
            text-align: center;
            padding: 30px 0;
            color: #6c757d;
        }
        
        .profile-picture-container {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .profile-picture-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .file-upload-label {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #5b1f1f;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .file-upload-input {
            display: none;
        }
        
        .form-actions {
            text-align: right;
        }
        
        .btn {
            background: #5b1f1f;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #4a1919;
        }
        
        .btn i {
            font-size: 16px;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .alert i {
            font-size: 20px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #b8ddc4;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1c6cb 100%);
            color: #721c24;
            border: 1px solid #eab3ba;
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="profile-container">
            <!-- Left Column: Profile Details -->
            <div class="profile-details">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <h2 class="section-title" style="margin-bottom: 20px; color: #5b1f1f; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="fas fa-user"></i> My Profile
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="profile-section">
                        <div class="profile-picture-container" style="text-align: center; margin-bottom: 25px;">
                            <div class="profile-picture-wrapper" style="position: relative; display: inline-block;">
                                <img 
                                    src="<?php echo !empty($user['profile_picture']) ? '../' . htmlspecialchars($user['profile_picture']) : '../assets/images/default-avatar.png'; ?>" 
                                    alt="Profile Picture" 
                                    id="profilePicturePreview"
                                    style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"
                                >
                                <label for="profilePicture" style="position: absolute; bottom: 10px; right: 10px; background: #5b1f1f; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input 
                                    type="file" 
                                    id="profilePicture" 
                                    name="profile_picture" 
                                    accept="image/*"
                                    style="display: none;"
                                >
                            </div>
                            <?php if (!empty($user['profile_picture'])): ?>
                                <div style="margin-top: 10px;">
                                    <label style="color: #dc3545; cursor: pointer; font-size: 0.9rem;">
                                        <input type="checkbox" name="remove_profile_picture" style="margin-right: 5px;">
                                        <i class="fas fa-trash"></i> Remove Photo
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">Full Name</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?php echo htmlspecialchars($user['name']); ?>" 
                                style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                                required
                            >
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">Email</label>
                            <div style="padding: 10px 15px; background: #f8f9fa; border-radius: 6px; color: #6c757d;">
                                <?php echo htmlspecialchars($user['email_id'] ?? 'Not provided'); ?>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="bio" style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">Bio</label>
                            <textarea 
                                id="bio" 
                                name="bio" 
                                style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; min-height: 120px;"
                                placeholder="Tell us about yourself..."
                            ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions" style="text-align: right;">
                            <button type="submit" name="update_profile" style="background: #5b1f1f; color: white; border: none; padding: 10px 25px; border-radius: 6px; font-size: 1rem; cursor: pointer; transition: background 0.2s;">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Right Column: Groups -->
            <div class="profile-groups">
                <h2 class="section-title" style="margin-bottom: 20px; color: #5b1f1f; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="fas fa-users"></i> My Groups
                </h2>
                
                <?php if (!empty($userGroups)): ?>
                    <?php foreach ($userGroups as $group): ?>
                        <div class="group-card" onclick="showGroupMembers(<?php echo $group['id']; ?>, '<?php echo htmlspecialchars(addslashes($group['name'])); ?>')">
                            <div class="group-image-container">
                                <?php 
                                $imageUrl = '';
                                $hasImage = false;
                                
                                // Check if we have a valid image
                                if (!empty($group['group_image'])) {
                                    if (filter_var($group['group_image'], FILTER_VALIDATE_URL)) {
                                        // External URL
                                        $imageUrl = $group['group_image'];
                                        $hasImage = true;
                                    } else {
                                        // Local path
                                        $localPath = __DIR__ . '/../' . ltrim($group['group_image'], '/');
                                        if (file_exists($localPath)) {
                                            $imageUrl = '../' . ltrim($group['group_image'], '/');
                                            $hasImage = true;
                                        }
                                    }
                                }
                                
                                // Use default image if no valid image found
                                if (!$hasImage) {
                                    $imageUrl = '../assets/images/group-default.svg';
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     alt="<?php echo htmlspecialchars($group['name']); ?>" 
                                     class="group-image"
                                     onerror="this.onerror=null; this.src='../assets/images/group-default.svg';">
                            </div>
                            <h4><?php echo htmlspecialchars($group['name']); ?></h4>
                            <?php if (!empty($group['description'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($group['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-groups">
                        <i class="fas fa-users" style="font-size: 2rem; color: #dee2e6; margin-bottom: 15px; display: block;"></i>
                        <p>You haven't joined any groups yet.</p>
                        <a href="../groups.php" class="btn" style="margin-top: 15px; background: #5b1f1f; color: white; text-decoration: none; padding: 8px 20px; border-radius: 4px; display: inline-block;">
                            Browse Groups <i class="fas fa-search" style="margin-left: 5px;"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>

    <!-- Group Members Modal -->
    <div id="groupMembersModal" class="group-members-modal">
        <div class="group-members-content">
            <div class="group-members-header">
                <h3 id="groupModalTitle">Group Members</h3>
                <button class="close-modal" onclick="closeGroupModal()">&times;</button>
            </div>
            <div class="group-members-body">
                <div id="membersLoading" class="loading-members">
                    <p>Loading members...</p>
                </div>
                <div id="membersList" class="member-list" style="display: none;">
                    <!-- Members will be inserted here by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update profile picture preview when a new image is selected
        document.getElementById('profilePicture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePicturePreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Group members modal functionality
        function showGroupMembers(groupId, groupName) {
            const modal = document.getElementById('groupMembersModal');
            const modalTitle = document.getElementById('groupModalTitle');
            const membersList = document.getElementById('membersList');
            const membersLoading = document.getElementById('membersLoading');
            
            // Set group name in modal title
            modalTitle.textContent = groupName + ' - Members';
            
            // Show loading state
            membersLoading.style.display = 'block';
            membersList.style.display = 'none';
            
            // Clear previous members
            membersList.innerHTML = '';
            
            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Fetch group members
            fetch(`/alumni/api/get_group_members.php?group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide loading
                        membersLoading.style.display = 'none';
                        
                        if (data.members.length > 0) {
                            // Add members to the list
                            data.members.forEach(member => {
                                const memberCard = document.createElement('div');
                                memberCard.className = 'member-card';
                                memberCard.innerHTML = `
                                    <div class="member-avatar" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px solid #5b1f1f;">
                                        <img src="${member.profile_picture}" 
                                             alt="${member.name || 'Alumni Member'}" 
                                             style="width: 100%; height: 100%; object-fit: cover;"
                                             onerror="this.onerror=null; this.style.display='none'; this.parentNode.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background-color:#5b1f1f;color:white;font-weight:bold;font-size:24px;\'>' + '${member.name ? member.name.charAt(0).toUpperCase() : 'A'}' + '</div>'">
                                    </div>
                                    <div class="member-info" style="flex: 1; margin-left: 15px;">
                                        <h4 style="margin: 0 0 5px 0; color: #333;">${member.name || 'Alumni Member'}</h4>
                                        ${member.usn ? `<p style="margin: 0; color: #666; font-size: 0.9em;">${member.usn}</p>` : ''}
                                        ${member.email ? `<p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;"><i class="fas fa-envelope" style="margin-right: 5px; color: #5b1f1f;"></i>${member.email}</p>` : ''}
                                    </div>
                                `;
                                membersList.appendChild(memberCard);
                            });
                        } else {
                            membersList.innerHTML = `
                                <div class="no-members">
                                    <p>No members found in this group.</p>
                                </div>
                            `;
                        }
                        
                        // Show members list
                        membersList.style.display = 'grid';
                    } else {
                        throw new Error(data.error || 'Failed to load group members');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    membersLoading.innerHTML = `
                        <p style="color: #dc3545;">Error loading members: ${error.message}</p>
                        <button onclick="showGroupMembers(${groupId}, '${groupName.replace(/'/g, "\\'")}')" 
                                style="margin-top: 10px; padding: 5px 10px; background: #5b1f1f; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Retry
                        </button>
                    `;
                });
        }
        
        function closeGroupModal() {
            const modal = document.getElementById('groupMembersModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside the content
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('groupMembersModal');
            if (event.target === modal) {
                closeGroupModal();
            }
        });
    </script>
</body>
</html>