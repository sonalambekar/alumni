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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
        }

        .main-content {
            padding: 40px 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
            animation: fadeInDown 0.6s ease;
        }

        .page-header h1 {
            color: #5b1f1f;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 16px;
        }

        .feedback-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .feedback-form {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(91, 31, 31, 0.08);
            height: fit-content;
            position: sticky;
            top: 30px;
            border: 1px solid rgba(91, 31, 31, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInLeft 0.6s ease;
        }

        .feedback-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(91, 31, 31, 0.12);
        }

        .feedback-form h2 {
            color: #5b1f1f;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feedback-form h2 i {
            font-size: 24px;
        }

        .feedback-form .subtitle {
            color: #888;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            min-height: 180px;
            resize: vertical;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #5b1f1f;
            background: white;
            box-shadow: 0 0 0 4px rgba(91, 31, 31, 0.1);
        }

        .form-group textarea::placeholder {
            color: #aaa;
        }

        .btn-submit {
            background: linear-gradient(135deg, #5b1f1f 0%, #7a2828 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(91, 31, 31, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(91, 31, 31, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .feedbacks-container {
            display: flex;
            flex-direction: column;
            gap: 25px;
            width: 100%;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
            padding-right: 10px;
            animation: fadeInRight 0.6s ease;
        }

        .feedbacks-container > h2 {
            color: #5b1f1f;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feedbacks-container > h2 i {
            font-size: 26px;
        }

        .feedback-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.03);
            width: 100%;
            box-sizing: border-box;
        }

        .feedback-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #5b1f1f 0%, #7a2828 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feedback-card:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 30px rgba(91, 31, 31, 0.12);
        }

        .feedback-card:hover::before {
            opacity: 1;
        }

        .feedback-header {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .user-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar .initials {
            font-size: 22px;
            font-weight: 700;
            color: #5b1f1f;
        }

        .user-info {
            flex: 1;
        }

        .user-info h4 {
            margin: 0;
            color: #333;
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .user-info .date {
            color: #999;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-info .date i {
            font-size: 12px;
        }

        .feedback-content {
            color: #555;
            line-height: 1.8;
            white-space: pre-wrap;
            font-size: 15px;
            padding: 5px 0;
        }

        .no-feedback {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .no-feedback i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-feedback p {
            color: #888;
            font-size: 16px;
            margin: 0;
        }

        .char-count {
            text-align: right;
            font-size: 13px;
            color: #999;
            margin-top: 8px;
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

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 900px) {
            .feedback-container {
                grid-template-columns: 1fr;
            }

            .feedback-form {
                position: static;
            }

            .page-header h1 {
                font-size: 28px;
            }

            .feedback-card:hover {
                transform: none;
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

        <div class="feedback-container">
            <!-- Feedback Form -->
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

            <!-- Feedback List -->
            <div class="feedbacks-container">
                <h2><i class="fas fa-book-open"></i> Alumni Stories</h2>
                
                <?php if (count($feedbacks) > 0): ?>
                    <?php 
                    if (empty($feedbacks)) {
                        echo '<div class="no-feedback">
                                <i class="fas fa-comment-slash"></i>
                                <p>No feedback has been shared yet. Be the first to share your experience!</p>
                              </div>';
                    } else {
                        foreach ($feedbacks as $feedback): 
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
                                            echo $date->format('F j, Y \a\t g:i A');
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="feedback-content">
                                    <?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>
                                </div>
                            </div>
                    <?php 
                        endforeach; 
                    }
                    ?>
                <?php else: ?>
                    <div class="no-feedback">
                        <i class="fas fa-comment-slash"></i>
                        <p>No experiences shared yet. Be the first to share your story!</p>
                    </div>
                <?php endif; ?>
            </div>
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

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>