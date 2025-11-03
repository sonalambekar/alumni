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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // 1. Handle profile picture upload if a new one was provided
        $profilePicturePath = '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../attachments/profile_pictures/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            // Generate a safe filename
            $originalName = preg_replace('/[^\w\-\.]/', '_', $_FILES['profile_picture']['name']);
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $fileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            // Verify it's an actual image
            $check = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($check !== false) {
                // Delete old profile picture if it exists
                if (!empty($user['profile_picture'])) {
                    $oldImagePath = '../' . ltrim($user['profile_picture'], '/');
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                
                // Move the uploaded file
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                    $profilePicturePath = 'attachments/profile_pictures/' . $fileName;
                    // Debug output
                    error_log("Profile picture uploaded to: " . $targetPath);
                    error_log("Profile picture path to save: " . $profilePicturePath);
                }
            }
        }
        
        // 2. Update user data in database
        $updateFields = [
            'name' => $name,
            'bio' => $bio,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Add profile picture to update if we have one
        if (!empty($profilePicturePath)) {
            $updateFields['profile_picture'] = $profilePicturePath;
        } elseif (isset($_POST['remove_profile_picture'])) {
            $updateFields['profile_picture'] = '';
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
        
        // Debug output
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
        
        // Commit the transaction
        $pdo->commit();
        
        // Refresh user data
        $user = getCurrentUser();
        $success = "Profile updated successfully!";
        
        $success = "Profile updated successfully!";
        // Refresh user data
        $user = getCurrentUser();
    } catch (PDOException $e) {
        // Rollback the transaction on error
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f5f5f5;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin: 10px 0 5px;
        }
        
        .profile-email {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .profile-bio {
            color: #555;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        .profile-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #5b1f1f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #5b1f1f;
            outline: none;
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            background: #5b1f1f;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #4a1919;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-upload-label {
            display: inline-block;
            padding: 10px 20px;
            background: #f5f5f5;
            border: 1px dashed #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-label:hover {
            background: #eee;
            border-color: #ccc;
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="profile-container">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-header">
                <div class="file-upload">
                    <img src="<?php 
                        if (!empty($user['profile_picture'])) {
                            $profilePic = $user['profile_picture'];
                            // If it's already a full URL
                            if (strpos($profilePic, 'http') === 0) {
                                echo htmlspecialchars($profilePic);
                            } 
                            // If it's a relative path
                            else {
                                // Make sure the path is correct and URL encoded
                                $relativePath = ltrim($profilePic, '/');
                                $imagePath = '../' . $relativePath;
                                // Check if file exists
                                if (file_exists($imagePath)) {
                                    // Encode each part of the URL
                                    $pathParts = explode('/', $relativePath);
                                    $encodedParts = array_map('rawurlencode', $pathParts);
                                    $encodedPath = implode('/', $encodedParts);
                                    echo '../' . $encodedPath . '?t=' . time(); // Add timestamp to prevent caching
                                } else {
                                    error_log("Profile image not found: " . $imagePath);
                                    echo 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&size=200&background=5b1f1f&color=fff';
                                }
                            }
                        } else {
                            echo 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&size=200&background=5b1f1f&color=fff';
                        }
                    ?>" 
                         alt="Profile Picture" 
                         class="profile-picture" 
                         id="profilePicturePreview">
                    <input type="file" 
                           id="profilePicture" 
                           name="profile_picture" 
                           class="file-upload-input" 
                           accept="image/*">
                    <label for="profilePicture" class="file-upload-label">
                        <i class="fas fa-camera"></i> Change Photo
                    </label>
                </div>
                
                <h1 class="profile-name"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h1>
                <p class="profile-email">
                    <i class="fas fa-envelope"></i> 
                    <?php 
                    // First try to get email from email_id, fall back to email if not available
                    $userEmail = $user['email_id'] ?? $user['email'] ?? 'No email provided';
                    echo htmlspecialchars($userEmail); 
                    ?>
                </p>
                <?php if (!empty($user['bio'] ?? '')): ?>
                    <p class="profile-bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="profile-section">
                    <h3 class="section-title">Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" 
                                  name="bio" 
                                  class="form-control" 
                                  placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <button type="submit" name="update_profile" class="btn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
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
    </script>
</body>
</html>
