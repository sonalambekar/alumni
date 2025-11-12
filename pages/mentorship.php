<?php
// Start session and include database configuration
session_start();
require_once '../includes/db_config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;
$userName = '';
$userEmail = '';
$userImage = '';

// Get user data if logged in
if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("SELECT name, email_id as email, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $userName = $user['name'];
            $userEmail = $user['email'];
            
            // Handle profile picture path
            if (!empty($user['profile_picture'])) {
                $profilePic = $user['profile_picture'];
                
                // Check if it's already a full URL
                if (filter_var($profilePic, FILTER_VALIDATE_URL)) {
                    $userImage = $profilePic;
                } 
                // Check if it's an absolute path
                else if (strpos($profilePic, '/') === 0) {
                    $userImage = $profilePic;
                }
                // Handle relative paths
                else {
                    // Remove any 'alumni/' prefix if it exists to avoid duplication
                    $profilePic = str_replace('alumni/', '', $profilePic);
                    // Remove any leading slashes
                    $profilePic = ltrim($profilePic, '/');
                    // Construct the path
                    $userImage = '/alumni/' . $profilePic;
                }
            } else {
                // Default avatar if no profile picture
                $userImage = 'https://via.placeholder.com/150';
                
                // Generate initials for the avatar
                $nameParts = array_filter(explode(' ', $userName));
                $initials = '';
                if (count($nameParts) > 0) {
                    $initials .= strtoupper(substr($nameParts[0], 0, 1));
                    if (count($nameParts) > 1) {
                        $initials .= strtoupper(substr(end($nameParts), 0, 1));
                    }
                }
                $userImage = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=5b1f1f&color=fff&size=150';
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        $userImage = 'https://via.placeholder.com/150';
    }
}

// Handle form submission
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    try {
        $profession = $_POST['profession'] ?? '';
        $company = $_POST['company'] ?? '';
        $experience = (int)($_POST['experience'] ?? 0);
        $expertise = $_POST['expertise'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $availability = $_POST['availability'] ?? 'Flexible';
        $linkedin = $_POST['linkedin'] ?? '';
        $twitter = $_POST['twitter'] ?? '';
        
        // Validate required fields
        if (empty($profession) || empty($expertise) || empty($bio)) {
            throw new Exception("Please fill in all required fields");
        }
        
        // Insert into mentors table
        $stmt = $pdo->prepare("
            INSERT INTO mentors (
                user_id, name, email, profile_image, profession, company, 
                experience, expertise, bio, availability, linkedin, twitter
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $userName,
            $userEmail,
            $userImage,
            $profession,
            $company,
            $experience,
            $expertise,
            $bio,
            $availability,
            $linkedin,
            $twitter
        ]);
        
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
        
    } catch (Exception $e) {
        $formError = $e->getMessage();
        error_log("Mentor registration error: " . $formError);
    }
}

    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Check for success message from redirect
    $formSuccess = false;
    
    // If this is an AJAX request, we don't want to render the full page
    if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
        return;
    }

// Fetch all approved mentors with creator information
try {
    $stmt = $pdo->query("
        SELECT m.*, 
               u.name as creator_name, 
               u.profile_picture as creator_image,
               u.usn as creator_usn
        FROM mentors m
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.is_approved = 1 
        ORDER BY m.created_at DESC
    ");
    $mentors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mentors = [];
    error_log("Error fetching mentors: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentorship Program - Alumni Connect</title>
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

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .mentorship-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            box-shadow: var(--shadow-md);
        }

        .mentorship-header h1 {
            margin: 0 0 15px 0;
            font-size: 2.5em;
            font-weight: 700;
        }

        .mentorship-header p {
            margin: 0 0 25px 0;
            font-size: 1.1em;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Counter Section */
        .counter-section {
            background-color: var(--white);
            padding: 40px 0;
            margin: -20px 0 40px 0;
            box-shadow: var(--shadow-md);
        }
        
        .counter-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .counter-item {
            text-align: center;
            padding: 20px;
            flex: 1;
            min-width: 200px;
        }
        
        .counter-number {
            font-size: 3em;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            line-height: 1.2;
        }
        
        .counter-label {
            color: var(--text-light);
            font-size: 1.1em;
            margin: 5px 0 0 0;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }
        }

        .mentors-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .mentors-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            padding: 30px 0 20px 0;
        }

        .mentors-title {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0;
            font-weight: 700;
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 15px;
        }

        .btn:hover {
            background-color: #4a1919;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }

        .btn-secondary:hover {
            background-color: #d9b24a;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 10px 20px;
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .mentors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .mentor-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .mentor-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .mentor-header {
            padding: 30px 20px 20px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
        }

        .mentor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--white);
            margin: 0 auto 15px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .mentor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .mentor-avatar .avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #5b1f1f;
            color: white;
            font-size: 36px;
            font-weight: bold;
            border-radius: 50%;
        }

        .mentor-info h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 5px 0;
        }

        .mentor-title {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .mentor-company {
            font-size: 13px;
            opacity: 0.85;
            margin: 5px 0 0 0;
        }

        .mentor-body {
            padding: 20px;
        }

        .mentor-expertise {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .mentor-expertise span {
            background: #f3f4f6;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            color: var(--text-color);
            font-weight: 500;
        }

        .mentor-bio {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .mentor-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }

        .mentor-actions .btn,
        .mentor-actions .btn-outline {
            width: 100%;
            justify-content: center;
            text-align: center;
            padding: 10px 15px;
            font-size: 13px;
        }

        /* Modal Styles - FIXED */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 9998;
            backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: block;
            opacity: 1;
        }

        /* FIXED: Removed transform from animation to prevent conflict with centering */
        @keyframes slideIn {
            from {
                opacity: 0;
                top: 40%;
            }
            to {
                opacity: 1;
                top: 50%;
            }
        }

        .mentor-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            opacity: 0;
        }

        .mentor-modal.active {
            display: block;
            animation: slideIn 0.4s ease forwards;
        }

        .modal-header {
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .alert {
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            text-align: center;
            font-weight: 500;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .profile-preview {
            display: flex;
            gap: 20px;
            align-items: center;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }

        .profile-preview-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary-color);
            box-shadow: var(--shadow);
        }

        .profile-preview-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-preview-info h3 {
            margin: 0 0 5px 0;
            color: var(--primary-color);
            font-size: 20px;
            font-weight: 600;
        }

        .profile-preview-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 14px;
        }

        .form-group label .required {
            color: #e53e3e;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: var(--white);
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: var(--text-light);
            font-size: 13px;
        }

        .input-group {
            display: flex;
            align-items: stretch;
        }

        .input-group-prefix {
            background: #f3f4f6;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-right: none;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            color: var(--text-light);
            font-weight: 500;
            font-size: 14px;
        }

        .input-group input {
            border-radius: 0 var(--border-radius) var(--border-radius) 0 !important;
            border-left: none !important;
        }

        .form-actions {
            text-align: center;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
        }

        .form-actions .btn {
            padding: 14px 40px;
            font-size: 16px;
        }

        .login-prompt {
            text-align: center;
            padding: 60px 30px;
        }

        .login-prompt i {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .login-prompt p {
            font-size: 16px;
            color: var(--text-light);
            margin-bottom: 25px;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            border: 2px dashed var(--border-color);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .empty-state p {
            color: var(--text-light);
            font-size: 16px;
            margin: 0;
        }

        @media (max-width: 768px) {
            .mentorship-header {
                padding: 40px 20px;
            }

            .mentorship-header h1 {
                font-size: 2em;
            }

            .mentors-header {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .modal-body {
                padding: 20px;
            }

            .profile-preview {
                flex-direction: column;
                text-align: center;
            }

            .mentor-actions {
                grid-template-columns: 1fr;
            }

            .mentors-grid {
                grid-template-columns: 1fr;
            }

            .mentor-modal {
                width: 95%;
                max-height: 95vh;
            }

            .modal-header {
                padding: 20px;
            }

            .modal-header h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="mentorship-header">
            <h1><i class="fas fa-graduation-cap"></i> Mentorship Program</h1>
            <p>Connect with experienced alumni mentors or share your knowledge by becoming a mentor yourself</p>
        </div>
        

        <div class="mentors-container">
            <div class="mentors-header">
                <h2 class="mentors-title"><i class="fas fa-users"></i>Mentors</h2>
                <div class="mentors-actions">
                    <button class="btn" id="openMentorModal">
                        <i class="fas fa-user-plus"></i> Become a Mentor
                    </button>
                </div>
            </div>

            <div class="mentors-grid">
                <?php if (!empty($mentors)): ?>
                    <?php foreach ($mentors as $mentor): ?>
                        <div class="mentor-card">
                            <div style="padding: 10px 15px; background: #f8f9fa; border-bottom: 1px solid #eee; font-size: 0.85rem; color: #666;">
                                <i class="fas fa-user-edit"></i> Added by <?php echo htmlspecialchars($mentor['creator_name'] ?? 'Alumni'); ?>
                                <?php if (!empty($mentor['creator_usn'])): ?>
                                    <span style="color: #999;">(<?php echo htmlspecialchars($mentor['creator_usn']); ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="mentor-header">
                                <?php 
// Debug: Print the image path for testing
// echo '<!-- Creator Image Path: ' . htmlspecialchars($mentor['creator_image'] ?? 'No image') . ' -->';
?>
<div class="mentor-avatar" style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; border: 2px solid #5b1f1f; margin-right: 15px; background-color: #f0f0f0;">
    <?php 
    $creatorImage = '';
    if (!empty($mentor['creator_image'])) {
        // If it's already a full URL, use as is
        if (filter_var($mentor['creator_image'], FILTER_VALIDATE_URL)) {
            $creatorImage = $mentor['creator_image'];
        } 
        // If it's a relative path, make sure it has the correct base path
        else {
            // Remove any leading slashes to prevent double slashes
            $imagePath = ltrim($mentor['creator_image'], '/');
            // Check if the path already contains 'alumni' to avoid duplication
            if (strpos($imagePath, 'alumni/') === 0) {
                $creatorImage = '/' . $imagePath;
            } else {
                $creatorImage = '/alumni/' . $imagePath;
            }
        }
    }
    
    if (!empty($creatorImage)): 
    ?>
        <img src="<?php echo htmlspecialchars($creatorImage); ?>" 
             alt="<?php echo htmlspecialchars($mentor['creator_name'] ?? 'Creator'); ?>"
             style="width: 100%; height: 100%; object-fit: cover;"
             onerror="this.onerror=null; this.style.display='none'; this.parentNode.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:16px;color:#5b1f1f;font-weight:bold;\'><?php 
                $initials = '';
                $creatorName = $mentor['creator_name'] ?? 'C';
                $nameParts = explode(' ', trim($creatorName));
                $initials .= strtoupper(substr($nameParts[0], 0, 1));
                if (count($nameParts) > 1) {
                    $initials .= strtoupper(substr(end($nameParts), 0, 1));
                }
                echo $initials;
             ?></div>'">
    <?php else: ?>
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #5b1f1f; font-weight: bold;">
                                            <?php 
                                            $initials = '';
                                            $creatorName = $mentor['creator_name'] ?? 'C';
                                            $nameParts = array_filter(explode(' ', trim($creatorName)));
                                            if (!empty($nameParts)) {
                                                $initials .= strtoupper(substr($nameParts[0], 0, 1));
                                                if (count($nameParts) > 1) {
                                                    $initials .= strtoupper(substr(end($nameParts), 0, 1));
                                                }
                                            } else {
                                                $initials = 'C';
                                            }
                                            echo $initials;
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mentor-info">
                                    <h3><?php echo htmlspecialchars($mentor['name']); ?></h3>
                                    <p class="mentor-title"><?php echo htmlspecialchars($mentor['profession']); ?></p>
                                    <?php if (!empty($mentor['company'])): ?>
                                        <p class="mentor-company"><i class="fas fa-building"></i> <?php echo htmlspecialchars($mentor['company']); ?></p>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                            <div class="mentor-body">
                                <?php if (!empty($mentor['expertise'])): ?>
                                    <div class="mentor-expertise">
                                        <?php 
                                        $expertise = array_map('trim', explode(',', $mentor['expertise']));
                                        foreach (array_slice($expertise, 0, 3) as $skill): 
                                        ?>
                                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($expertise) > 3): ?>
                                            <span><i class="fas fa-plus"></i> <?php echo (count($expertise) - 3); ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($mentor['bio'])): ?>
                                    <p class="mentor-bio"><?php echo nl2br(htmlspecialchars($mentor['bio'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-friends"></i>
                        <p>No mentors available at the moment. Check back soon or be the first to apply!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div class="modal-overlay" id="modalOverlay"></div>

    <!-- Mentor Application Modal -->
    <div class="mentor-modal" id="mentorModal">
        <div class="modal-header">
            <h2>
                <?php echo $formSuccess ? '<i class="fas fa-check-circle"></i> Application Submitted!' : '<i class="fas fa-chalkboard-teacher"></i> Become a Mentor'; ?>
            </h2>
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <?php if ($formSuccess): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p style="margin: 10px 0;">Your mentor application has been received! Our team will review your information and get back to you soon.</p>
                    <button class="btn" style="margin-top: 15px;" onclick="window.location.href='mentorship.php'">
                        <i class="fas fa-arrow-left"></i> Back to Mentors
                    </button>
                </div>
            <?php else: ?>
                <?php if (!empty($formError)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle" style="font-size: 20px; margin-right: 10px;"></i>
                        <?php echo htmlspecialchars($formError); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$isLoggedIn): ?>
                    <div class="login-prompt">
                        <i class="fas fa-lock"></i>
                        <p>Please log in to apply as a mentor and start making a difference.</p>
                        <a href="../login.php?redirect=mentorship.php" class="btn">
                            <i class="fas fa-sign-in-alt"></i> Login to Continue
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" id="mentorForm" onsubmit="return submitMentorForm(event)">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">
                        <div class="profile-preview">
                            <div class="profile-preview-avatar" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px solid #5b1f1f;">
                                <img 
                                    src="<?php echo htmlspecialchars($userImage); ?>" 
                                    alt="<?php echo htmlspecialchars($userName); ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                    onerror="this.onerror=null; this.style.display='none'; this.parentNode.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:24px;color:#5b1f1f;font-weight:bold;\'>' + '<?php 
                                        $initials = '';
                                        $nameParts = array_filter(explode(' ', $userName));
                                        if (!empty($nameParts)) {
                                            $initials .= strtoupper(substr($nameParts[0], 0, 1));
                                            if (count($nameParts) > 1) {
                                                $initials .= strtoupper(substr(end($nameParts), 0, 1));
                                            }
                                        } else {
                                            $initials = 'U';
                                        }
                                        echo $initials;
                                    ?>' + '</div>'">
                            </div>
                            <div class="profile-preview-info">
                                <h3><?php echo htmlspecialchars($userName); ?></h3>
                                <?php if (!empty($userEmail)): ?>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userEmail); ?></p>
                                <?php else: ?>
                                    <p><i class="fas fa-exclamation-triangle" style="color: #e53e3e;"></i> Email not found</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="profession">Profession <span class="required">*</span></label>
                                <input type="text" id="profession" name="profession" required 
                                       placeholder="e.g., Software Engineer">
                            </div>
                            <div class="form-group">
                                <label for="company">Company/Organization</label>
                                <input type="text" id="company" name="company" 
                                       placeholder="e.g., Google, Microsoft">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="experience">Years of Experience <span class="required">*</span></label>
                                <input type="number" id="experience" name="experience" min="0" required 
                                       placeholder="0">
                            </div>
                            <div class="form-group">
                                <label for="availability">Availability</label>
                                <select id="availability" name="availability">
                                    <option value="Flexible">Flexible</option>
                                    <option value="Weekdays">Weekdays</option>
                                    <option value="Weekends">Weekends</option>
                                    <option value="By Appointment">By Appointment</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="expertise">Areas of Expertise <span class="required">*</span></label>
                            <input type="text" id="expertise" name="expertise" required 
                                   placeholder="e.g., Career Development, Leadership, Web Development">
                            <small><i class="fas fa-info-circle"></i> Separate multiple areas with commas</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">Biography <span class="required">*</span></label>
                            <textarea id="bio" name="bio" rows="5" required 
                                      placeholder="Tell us about your background, achievements, and why you want to be a mentor..."></textarea>
                            <small><i class="fas fa-info-circle"></i> Share your journey and what you can offer to mentees</small>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="linkedin">LinkedIn Profile</label>
                                <div class="input-group">
                                    <span class="input-group-prefix">linkedin.com/in/</span>
                                    <input type="text" id="linkedin" name="linkedin" placeholder="username">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="twitter">Twitter Handle</label>
                                <div class="input-group">
                                    <span class="input-group-prefix">@</span>
                                    <input type="text" id="twitter" name="twitter" placeholder="username">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('mentorModal');
            const overlay = document.getElementById('modalOverlay');
            const openBtn = document.getElementById('openMentorModal');
            const closeBtn = document.getElementById('closeModal');

            // Open modal
            function openModal() {
                modal.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            // Close modal
            function closeModal() {
                modal.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
                
                // FIXED: Remove success parameter from URL when closing modal
                if (window.location.search.includes('success=1')) {
                    window.history.replaceState({}, document.title, 'mentorship.php');
                }
            }

            // Event listeners
            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', closeModal);

            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });

            // Auto-open modal if form was submitted or has errors
            <?php if ($formSuccess || !empty($formError)): ?>
                openModal();
            <?php endif; ?>

            // Auto-expand textarea as user types
            document.querySelectorAll('textarea').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });

            // Form validation
            const form = document.getElementById('mentorForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    let firstInvalidField = null;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.style.borderColor = '#e53e3e';
                            field.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.1)';
                            
                            if (!firstInvalidField) {
                                firstInvalidField = field;
                            }
                        } else {
                            field.style.borderColor = '#e5e7eb';
                            field.style.boxShadow = 'none';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        
                        // Scroll to first invalid field
                        if (firstInvalidField) {
                            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstInvalidField.focus();
                        }
                        
                        // Show error message
                        const existingError = document.querySelector('.validation-error');
                        if (existingError) {
                            existingError.remove();
                        }
                        
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-error validation-error';
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please fill in all required fields marked with *';
                        form.insertBefore(errorDiv, form.firstChild);
                        
                        setTimeout(() => {
                            errorDiv.remove();
                        }, 5000);
                    }
                });
                
                // Remove error styling on input
                form.querySelectorAll('input, textarea, select').forEach(field => {
                    field.addEventListener('input', function() {
                        if (this.value.trim()) {
                            this.style.borderColor = '#e5e7eb';
                            this.style.boxShadow = 'none';
                        }
                    });
                });
            }
            
            // Character counter for bio
            const bioField = document.getElementById('bio');
            if (bioField) {
                const maxChars = 500;
                const counter = document.createElement('small');
                counter.style.float = 'right';
                counter.style.color = 'var(--text-light)';
                bioField.parentElement.appendChild(counter);
                
                function updateCounter() {
                    const remaining = maxChars - bioField.value.length;
                    counter.textContent = `${bioField.value.length}/${maxChars} characters`;
                    
                    if (remaining < 50) {
                        counter.style.color = '#e53e3e';
                    } else if (remaining < 100) {
                        counter.style.color = '#f59e0b';
                    } else {
                        counter.style.color = 'var(--text-light)';
                    }
                }
                
                bioField.addEventListener('input', updateCounter);
                updateCounter();
            }
            
            // Handle form submission with AJAX
            window.submitMentorForm = function(e) {
                e.preventDefault();
                const form = document.getElementById('mentorForm');
                const submitBtn = form.querySelector('button[type="submit"]');
                const formData = new FormData(form);
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                
                // Submit form via AJAX
                fetch('mentorship.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const successDiv = document.createElement('div');
                        successDiv.className = 'alert alert-success';
                        successDiv.innerHTML = `
                            <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p style="margin: 10px 0;">Your mentor application has been received! Our team will review your information and get back to you soon.</p>
                            <button class="btn" style="margin-top: 15px;" onclick="closeModal()">
                                <i class="fas fa-check"></i> Close
                            </button>
                        `;
                        
                        // Clear form and show success message
                        form.reset();
                        form.parentNode.replaceChild(successDiv, form);
                        
                        // Reload the page after 3 seconds to show the new mentor in the list
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
                    
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-error';
                    errorDiv.innerHTML = 'An error occurred. Please try again.';
                    form.insertBefore(errorDiv, form.firstChild);
                    
                    // Remove error message after 5 seconds
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);
                });
                
                return false;
            };
        });
    </script>
</body>
</html>