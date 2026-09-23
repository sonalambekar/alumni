<?php
session_start();
require_once '../includes/db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to view this page.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $user_id = $_SESSION['user_id'];
    $feedback = trim($_POST['feedback']);
    
    if (!empty($feedback)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, feedback_text, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $feedback]);
            
            // Redirect to prevent form resubmission
            header("Location: feedback.php");
            exit();
        } catch (PDOException $e) {
            die("Error submitting feedback: " . $e->getMessage());
        }
    }
}

// Get all feedback with user details
try {
    // First, let's check what tables exist in the database
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Debug: Show available tables
    if (in_array('user', $tables) && !in_array('users', $tables)) {
        // If 'user' table exists but not 'users', use that
        $userTable = 'user';
    } else {
        $userTable = 'users';
    }
    
    // Check if feedback table exists
    if (!in_array('feedback', $tables)) {
        die("Error: The 'feedback' table does not exist in the database.");
    }
    
    // First, let's check the structure of the users table
    $userColumns = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM $userTable");
    while ($column = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userColumns[] = $column['Field'];
    }
    
    // Build the query based on available columns
    $selectFields = ['f.*'];
    
    // Add user fields if they exist
    if (in_array('name', $userColumns)) {
        $selectFields[] = 'u.name as user_name';
    } else {
        // Try to get first_name and last_name if they exist
        $nameFields = [];
        if (in_array('first_name', $userColumns)) $nameFields[] = 'u.first_name';
        if (in_array('last_name', $userColumns)) $nameFields[] = 'u.last_name';
        
        if (!empty($nameFields)) {
            $selectFields[] = implode(', ', $nameFields);
        } else if (in_array('username', $userColumns)) {
            $selectFields[] = 'u.username as user_name';
        } else if (in_array('email', $userColumns)) {
            $selectFields[] = 'u.email as user_name';
        } else {
            $selectFields[] = 'NULL as user_name';
        }
    }
    
    // Add profile image if it exists
    if (in_array('profile_image', $userColumns)) {
        $selectFields[] = 'u.profile_image';
    } else if (in_array('avatar', $userColumns)) {
        $selectFields[] = 'u.avatar as profile_image';
    } else {
        $selectFields[] = 'NULL as profile_image';
    }
    
    // Build and execute the query
    $query = "
        SELECT " . implode(", ", $selectFields) . " 
        FROM feedback f 
        LEFT JOIN $userTable u ON f.user_id = u.id 
        ORDER BY f.created_at DESC
    ";
    
    $stmt = $pdo->query($query);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "<br>Query: " . ($query ?? 'N/A'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Your Experience - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --text-color: #2d3748;
            --text-light: #718096;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .main-content {
            padding: 2rem 1.5rem 2rem calc(250px + 2rem);
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
            animation: fadeIn 0.6s ease-out;
            min-height: 100vh;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .page-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--primary-color), #8b2e2e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .page-header p {
            color: var(--text-light);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .feedback-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            margin: 0 auto 3rem;
            max-width: 1100px;
            padding: 0 1rem;
        }
        
        @media (min-width: 1024px) {
            .feedback-container {
                grid-template-columns: 1.5fr 1fr;
            }
        }
        
        .feedback-form-container {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
            animation: fadeIn 0.6s ease-out 0.2s both;
        }
        
        .feedback-form-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }

        .feedback-form {
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .feedback-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .feedback-form h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            padding-bottom: 0.75rem;
        }
        
        .feedback-form h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 3px;
        }

        .feedback-form .subtitle {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 1.75rem;
            line-height: 1.7;
            max-width: 90%;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
            transition: var(--transition);
            font-size: 15px;
        }

        .form-group textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            min-height: 150px;
            transition: var(--transition);
            background-color: var(--white);
            color: var(--text-color);
            line-height: 1.6;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(91, 31, 31, 0.2);
        }
        
        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .submit-btn:hover::before {
            left: 100%;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(91, 31, 31, 0.25);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }

        .section-title {
            margin: 3.5rem 0 2rem;
            position: relative;
            padding: 0 1rem;
        }

        .section-title h2 {
            color: var(--primary-color);
            font-size: 1.75rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
        }

        .marquee-container {
            display: flex;
            animation: marquee 60s linear infinite;
            will-change: transform;
        }

        .marquee-container:hover {
            animation-play-state: paused;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        .feedback-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.75rem;
            margin: 1rem;
            box-shadow: var(--shadow-sm);
            min-width: 300px;
            max-width: 350px;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .feedback-card::before {
            content: '"';
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            font-family: Georgia, serif;
            font-size: 5rem;
            color: rgba(91, 31, 31, 0.1);
            line-height: 1;
            z-index: 0;
        }
        
        .feedback-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
            position: relative;
            z-index: 1;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--white);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: var(--transition);
        }
        
        .feedback-card:hover .user-avatar {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .user-details h4 {
            margin: 0 0 0.25rem;
            color: var(--primary-color);
            font-size: 1.05rem;
            font-weight: 700;
        }

        .feedback-date {
            color: var(--text-light);
            font-size: 0.8rem;
            text-align: right;
            position: relative;
            z-index: 1;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1023px) {
            .feedback-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem 1.5rem 1.5rem calc(220px + 1.5rem);
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .page-header p {
                font-size: 1rem;
            }
            
            .feedback-form {
                padding: 1.5rem;
            }
            
            .section-title h2 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem 1rem 1.5rem 1rem;
                margin-left: 0;
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.75rem;
            }
            
            .feedback-form h2 {
                font-size: 1.5rem;
            }
            
            .feedback-card {
                min-width: 280px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1><i class="fas fa-comments"></i> Share Your Experience</h1>
            <p>Connect with fellow alumni and inspire the next generation</p>
        </div>

        <!-- Feedback Form -->
        <div class="feedback-form-container">
            <div class="feedback-form">
                <h2><i class="fas fa-pen-fancy"></i> Your Story</h2>
                <p class="subtitle">Share your journey, insights, and experiences with the community</p>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="feedback"><i class="fas fa-quote-left"></i> Your Experience/Feedback</label>
                        <textarea 
                            id="feedback" 
                            name="feedback" 
                            placeholder="Share your career journey, experiences, or advice for fellow alumni and students..."
                            required
                            maxlength="2000"
                        ></textarea>
                        <div class="char-count">
                            <span id="charCount">0</span> / 2000 characters
                        </div>
                    </div>
                    <button type="submit" name="submit_feedback" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        Share Your Story
                    </button>
                </form>
            </div>
        </div>

        <!-- Alumni Stories Marquee -->
        <div class="marquee-section">
            <div class="section-title">
                <h2><i class="fas fa-book-open"></i> Alumni Stories</h2>
            </div>

            <?php if (count($feedbacks) > 0): ?>
                <div class="marquee-container">
                    <?php 
                    // Duplicate the feedbacks array for seamless loop
                    $duplicatedFeedbacks = array_merge($feedbacks, $feedbacks);
                    
                    foreach ($duplicatedFeedbacks as $feedback): 
                        // Determine the display name
                        $displayName = '';
                        if (isset($feedback['first_name']) || isset($feedback['last_name'])) {
                            $displayName = trim(($feedback['first_name'] ?? '') . ' ' . ($feedback['last_name'] ?? ''));
                        } elseif (isset($feedback['user_name'])) {
                            $displayName = $feedback['user_name'];
                        } elseif (isset($feedback['name'])) {
                            $displayName = $feedback['name'];
                        } else {
                            $displayName = 'Anonymous User';
                        }
                        
                        // Get avatar or generate initials
                        $avatar = $feedback['profile_image'] ?? null;
                        $initials = '';
                        if (empty($avatar) && !empty($displayName)) {
                            $nameParts = explode(' ', $displayName);
                            $initials = '';
                            foreach ($nameParts as $part) {
                                $initials .= strtoupper(substr(trim($part), 0, 1));
                                if (strlen($initials) >= 2) break;
                            }
                            $initials = substr($initials, 0, 2);
                        }
                    ?>
                        <div class="feedback-card">
                            <div class="feedback-header">
                                <div class="user-avatar">
                                    <?php if (!empty($avatar)): ?>
                                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile">
                                    <?php else: ?>
                                        <div class="initials">
                                            <?php echo !empty($initials) ? $initials : 'U'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <h4><?php echo htmlspecialchars($displayName); ?></h4>
                                    <span class="date">
                                        <i class="far fa-clock"></i>
                                        <?php 
                                        $date = new DateTime($feedback['created_at']);
                                        echo $date->format('M j, Y');
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="feedback-content">
                                <?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-feedback">
                    <i class="fas fa-comment-slash"></i>
                    <p>No experiences shared yet. Be the first to share your story!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Character counter
            const textarea = document.getElementById('feedback');
            const charCount = document.getElementById('charCount');
            
            if (textarea && charCount) {
                textarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }

            // Adjust animation speed based on number of cards
            const marqueeContainer = document.querySelector('.marquee-container');
            if (marqueeContainer) {
                const cardCount = marqueeContainer.children.length / 2; // Divided by 2 because we duplicate
                const baseSpeed = 60; // Base speed in seconds
                const adjustedSpeed = Math.max(30, baseSpeed * (cardCount / 5)); // Adjust based on card count
                marqueeContainer.style.animationDuration = adjustedSpeed + 's';
            }
        });
    </script>
</body>
</html>