<?php
session_start();
require_once '../includes/db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$alumni = [];
$error = '';

// Query to fetch medal recipients from the medals table
try {
    // First, check what columns exist in the users table
    $columns = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $user_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build the query with only existing columns
    $select_columns = ["m.*"];
    
    // Add user columns if they exist
    if (in_array('profile_picture', $user_columns)) {
        $select_columns[] = 'u.profile_picture as user_profile_image';
    }
    if (in_array('bio', $user_columns)) {
        $select_columns[] = 'u.bio';
    }
    if (in_array('linkedin_url', $user_columns)) {
        $select_columns[] = 'u.linkedin_url';
    }
    
    $query = "SELECT " . implode(", ", $select_columns) . " 
              FROM medals m
              LEFT JOIN users u ON m.user_id = u.id
              WHERE m.status = 'active'
              ORDER BY m.awarded_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process the data to match the expected format
    foreach ($alumni as &$alumnus) {
        // Map database fields to expected format
        $alumnus['first_name'] = $alumnus['user_name'];
        $alumnus['last_name'] = '';
        $alumnus['email'] = $alumnus['user_email'];
        
        // Handle profile image
        $profileImage = '';
        
        // Check medal-specific image first
        if (!empty($alumnus['profile_image'])) {
            $profileImage = $alumnus['profile_image'];
        } 
        // Then check user's profile image
        elseif (isset($alumnus['user_profile_image']) && !empty($alumnus['user_profile_image'])) {
            $profileImage = $alumnus['user_profile_image'];
        }
        
        // Set achievements/description
        $alumnus['achievements'] = $alumnus['description'];
        
        // Handle default image and check if image exists
        $defaultImage = 'https://ui-avatars.com/api/?name=' . urlencode($alumnus['user_name']) . '&background=random&color=fff&size=150';
        
        if (empty($profileImage)) {
            $alumnus['profile_image'] = $defaultImage;
        } else {
            // Check if it's a full URL
            if (filter_var($profileImage, FILTER_VALIDATE_URL)) {
                $alumnus['profile_image'] = $profileImage;
            } else {
                // It's a relative path, check if it exists
                $possiblePaths = [
                    $profileImage,  // Try the path as is
                    '/' . ltrim($profileImage, '/'),  // Try with leading slash
                    '/alumni/' . ltrim($profileImage, '/'),  // Try with /alumni/ prefix
                    str_replace('attachments/', 'alumni/attachments/', $profileImage),  // Fix attachments path
                    str_replace('attachments/', '', $profileImage)  // Try without attachments/
                ];
                
                $found = false;
                foreach ($possiblePaths as $path) {
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . ltrim($path, '/');
                    if (file_exists($fullPath)) {
                        $alumnus['profile_image'] = $path;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $alumnus['profile_image'] = $defaultImage;
                }
            }
        }
        
        // Ensure we have default values for optional fields
        $alumnus['current_city'] = $alumnus['current_city'] ?? '';
        $alumnus['current_country'] = $alumnus['current_country'] ?? '';
        $alumnus['bio'] = $alumnus['bio'] ?? '';
        $alumnus['linkedin_url'] = $alumnus['linkedin_url'] ?? '';
    }
    unset($alumnus); // Unset reference
    
} catch (PDOException $e) {
    $error = "Error fetching medal recipients: " . $e->getMessage();
    $alumni = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Medal Recipients - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #5b1f1f;  /* Dark red primary color */
            --primary-light: #7a2a2a;  /* Lighter shade of primary */
            --accent-gold: #d4af37;    /* Gold accent color */
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f8f5f0;       /* Light beige background */
            --card-bg: #ffffff;
        }
        
        body {
            background: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            padding: 4rem 2rem;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="rgba(255,255,255,0.05)"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.1;
        }
        
        .page-header-content {
            position: relative;
            z-index: 1;
            animation: fadeInDown 0.8s ease-out;
        }

        .page-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .page-header h1 i {
            color: var(--accent-gold);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .page-header p {
            font-size: 1.25rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem 3rem;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
        }

        .alumni-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(28, 6, 70, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            animation: fadeInUp 0.6s ease-out backwards;
        }

        .alumni-card:nth-child(1) { animation-delay: 0.1s; }
        .alumni-card:nth-child(2) { animation-delay: 0.2s; }
        .alumni-card:nth-child(3) { animation-delay: 0.3s; }
        .alumni-card:nth-child(4) { animation-delay: 0.4s; }
        .alumni-card:nth-child(5) { animation-delay: 0.5s; }
        .alumni-card:nth-child(6) { animation-delay: 0.6s; }

        .alumni-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-gold));
        }
        
        .alumni-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(28, 6, 70, 0.15);
        }

        .medal-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--accent-gold), #b38f00);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
            z-index: 2;
        }

        .medal-badge i {
            margin-right: 0.3rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .alumni-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .profile-image-container {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .profile-image-wrapper {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            padding: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-gold));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-image {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .alumni-info {
            width: 100%;
        }
        
        .alumni-name {
            color: var(--primary-color);
            font-weight: 700;
            margin: 0 0 0.75rem 0;
            font-size: 1.5rem;
        }

        .alumni-bio {
            color: var(--text-dark);
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: rgba(28, 6, 70, 0.05);
            border-radius: 10px;
        }
        
        .alumni-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .meta-item i {
            margin-right: 0.5rem;
            color: var(--primary-color);
            width: 16px;
        }
        
        .linkedin-button {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 119, 181, 0.3);
            margin-top: 0.5rem;
        }
        
        .linkedin-button:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 119, 181, 0.4);
        }
        
        .linkedin-button i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        .achievement-section {
            background: linear-gradient(135deg, rgba(91, 31, 31, 0.05), rgba(91, 31, 31, 0.1));
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .achievement-title {
            color: var(--primary-color);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }

        .achievement-title i {
            margin-right: 0.5rem;
            color: var(--accent-gold);
        }
        
        .achievement-text {
            color: var(--text-dark);
            font-size: 0.95rem;
            line-height: 1.7;
            margin: 0;
        }
        
        .no-recipients {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            grid-column: 1 / -1;
        }
        
        .no-recipients i {
            font-size: 4rem;
            color: var(--accent-gold);
            margin-bottom: 1.5rem;
            animation: swing 3s ease-in-out infinite;
        }
        
        .no-recipients h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        
        .no-recipients p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }


        @keyframes swing {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(10deg);
            }
            75% {
                transform: rotate(-10deg);
            }
        }

        @media (max-width: 768px) {
            .row {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .page-header p {
                font-size: 1rem;
            }

            .alumni-card {
                margin: 0;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem 2rem;
            }

            .page-header {
                padding: 3rem 1rem;
            }

            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <div class="page-header-content">
                <h1><i class="fas fa-medal"></i> Institute Medal Recipients</h1>
                <p>Celebrating the outstanding achievements of our distinguished alumni</p>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <?php if (count($alumni) > 0): ?>
                    <?php foreach ($alumni as $alumnus): 
                        $fullName = htmlspecialchars($alumnus['first_name'] . ' ' . $alumnus['last_name']);
                        $location = trim(implode(', ', array_filter([
                            $alumnus['current_city'],
                            $alumnus['current_country']
                        ])), ', ');
                        $achievements = !empty($alumnus['achievements']) ? 
                            htmlspecialchars($alumnus['achievements']) : 
                            'Awarded for outstanding contributions and achievements.';
                        // Get the base URL
                        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                        $profileImage = '';
                        
                        // Check for medal-specific image first
                        if (!empty($alumnus['profile_image']) && strpos($alumnus['profile_image'], 'http') !== 0) {
                            // Handle local file paths
                            $imagePath = ltrim($alumnus['profile_image'], '/');
                            $possiblePaths = [
                                'attachments/profile_pictures/' . $imagePath,
                                'attachments/' . $imagePath,
                                'uploads/' . $imagePath,
                                $imagePath
                            ];
                            
                            foreach ($possiblePaths as $path) {
                                $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/alumni/' . ltrim($path, '/');
                                if (file_exists($fullPath)) {
                                    $profileImage = $baseUrl . '/alumni/' . ltrim($path, '/');
                                    break;
                                }
                            }
                        } elseif (!empty($alumnus['profile_image'])) {
                            // If it's already a full URL
                            $profileImage = $alumnus['profile_image'];
                        }
                        
                        // If no valid image found, use default
                        if (empty($profileImage)) {
                            $profileImage = $baseUrl . '/alumni/assets/images/default-avatar.png';
                        }
                        $bio = !empty($alumnus['bio']) ? htmlspecialchars($alumnus['bio']) : '';
                    ?>
                        <div class="alumni-card">
                            <div class="card-body">
                                <div class="alumni-header">
                                    <div class="profile-image-container">
                                        <div class="profile-image-wrapper">
                                            <img src="<?php echo $profileImage; ?>" 
                                                class="profile-image" 
                                                alt="<?php echo $fullName; ?>">
                                        </div>
                                    </div>
                                    <div class="alumni-info">
                                        <h3 class="alumni-name"><?php echo $fullName; ?></h3>
                                        <?php if (!empty($bio)): ?>
                                            <div class="alumni-bio"><?php echo $bio; ?></div>
                                        <?php endif; ?>
                                        <div class="alumni-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($alumnus['email']); ?>
                                            </div>
                                            <?php if (!empty($location)): ?>
                                                <div class="meta-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo $location; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($alumnus['linkedin_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($alumnus['linkedin_url']); ?>" 
                                               target="_blank" 
                                               class="linkedin-button">
                                                <i class="fab fa-linkedin"></i> Connect on LinkedIn
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="achievement-section">
                                    <div class="achievement-title">
                                        <i class="fas fa-trophy"></i> Achievement
                                    </div>
                                    <p class="achievement-text"><?php echo $achievements; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-recipients">
                        <i class="fas fa-medal"></i>
                        <h3>No Medal Recipients Found</h3>
                        <p>There are currently no alumni who have received the Institute Medal. Please check back later.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>