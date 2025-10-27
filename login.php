<?php
session_start();
require_once __DIR__ . '/includes/db_config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';

// Check if user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($currentUser) {
            // Set role in session if not already set
            if (!isset($_SESSION['role']) && $currentUser['is_director'] == 1) {
                $_SESSION['role'] = 'admin';
            }

            // Redirect directors to admin dashboard
            if ($currentUser['is_director'] == 1) {
                header("Location: /alumni/admin/admin_dashboard.php");
                exit();
            }
        } else {
            // User not found or inactive, clear session
            session_unset();
            session_destroy();
            session_start();
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usn = trim($_POST['usn'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usn) || empty($password)) {
        $error = 'Please enter both USN and password.';
    } else {
        try {
            // Check if user exists in the database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE usn = ? AND is_active = 1");
            $stmt->execute([$usn]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "USN not found. Please check your USN.";
            } else {
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email_id'];

                    // Check if user is a director
                    if ($user['is_director'] == 1) {
                        $_SESSION['role'] = 'admin';
                        header("Location: /alumni/admin/admin_dashboard.php");
                        exit();
                    } else {
                        header("Location: /alumni/index.php");
                        exit();
                    }
                } else {
                    $error = 'Incorrect password.';
                }
            }
        } catch(PDOException $e) {
            $error = 'Login failed. Please try again.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Check if user is logged in for display
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 && isset($currentUser) && $currentUser;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumni Connect</title>
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
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 380px;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
        }

        .login-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .login-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
            font-weight: 400;
        }

        .login-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            transition: all 0.2s ease;
            background-color: #fafbfc;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: var(--white);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .error-message {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            font-size: 14px;
            border: 1px solid #fecaca;
            text-align: center;
        }

        .success-message {
            background-color: #f0fdf4;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            font-size: 14px;
            border: 1px solid #bbf7d0;
            text-align: center;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: var(--text-light);
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            margin-right: 8px;
            accent-color: var(--primary-color);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-password:hover {
            color: #4a1919;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 16px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s ease;
            margin-bottom: 24px;
        }

        .login-btn:hover {
            background-color: #4a1919;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(91, 31, 31, 0.3);
        }

        .divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
            color: var(--text-light);
            font-size: 14px;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #e1e5e9;
        }

        .divider span {
            background-color: var(--white);
            padding: 0 16px;
            position: relative;
            z-index: 1;
        }

        .social-login {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .social-btn {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: var(--border-radius);
            background: var(--white);
            color: var(--text-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .social-btn:hover {
            background-color: #f8fafc;
            border-color: var(--text-light);
            transform: translateY(-1px);
        }

        .signup-prompt {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #e1e5e9;
        }

        .signup-prompt p {
            margin: 0 0 16px 0;
            color: var(--text-light);
            font-size: 14px;
        }

        .signup-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .signup-link:hover {
            color: #4a1919;
            text-decoration: underline;
        }

        .profile-section {
            padding: 40px 30px;
            text-align: center;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary-color);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h2 {
            margin: 0 0 8px 0;
            color: var(--text-color);
            font-size: 24px;
        }

        .profile-info p {
            margin: 4px 0;
            color: var(--text-light);
            font-size: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #4a1919;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .profile-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                border-radius: 12px;
            }

            .login-header,
            .login-form,
            .profile-section {
                padding: 32px 24px;
            }

            .social-login {
                flex-direction: column;
            }

            .login-header h1 {
                font-size: 28px;
            }

            .profile-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome to Gems of GM</h1>
            <p>Sign in to access your Alumni Connect account</p>
        </div>

        <?php if ($error): ?>
            <div class="login-form">
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="login-form">
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isLoggedIn && $currentUser): ?>
            <!-- User Profile Section (for logged-in users) -->
            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['name']); ?>&background=5b1f1f&color=fff&size=150" alt="Profile" />
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($currentUser['name']); ?></h2>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($currentUser['email_id']); ?></p>
                        <p><i class="fas fa-id-card"></i> <strong>USN:</strong> <?php echo htmlspecialchars($currentUser['usn']); ?></p>
                        <?php if (!empty($currentUser['designation'])): ?>
                            <p><i class="fas fa-briefcase"></i> <strong>Role:</strong> <?php echo htmlspecialchars($currentUser['designation']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="/alumni/index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Go to Dashboard
                    </a>
                    <a href="/alumni/logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Login Form -->
            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="usn">University Serial Number (USN)</label>
                    <input type="text" id="usn" name="usn" placeholder="Enter your USN" required value="<?php echo htmlspecialchars($_POST['usn'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1"> Remember me
                    </label>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

                <div class="divider">
                    <span>or continue with</span>
                </div>

                <div class="social-login">
                    <a href="#" class="social-btn">
                        <i class="fab fa-google"></i> Google
                    </a>
                    <a href="#" class="social-btn">
                        <i class="fab fa-linkedin"></i> LinkedIn
                    </a>
                </div>

                <div class="signup-prompt">
                    <p>Don't have an account?</p>
                    <a href="#" class="signup-link">Create your account</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Login page loaded successfully');
            
            // Auto-hide messages after 5 seconds
            const messages = document.querySelectorAll('.error-message, .success-message');
            messages.forEach(msg => {
                setTimeout(() => {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>