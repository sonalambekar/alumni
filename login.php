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
            if (!isset($_SESSION['role'])) {
                if (isset($_SESSION['is_alumni_table']) && $_SESSION['is_alumni_table']) {
                    $_SESSION['role'] = 'alumni';
                } else {
                    if (isset($currentUser['is_director']) && $currentUser['is_director'] == 1) {
                        $_SESSION['role'] = 'admin';
                    } elseif (isset($currentUser['is_spoc']) && $currentUser['is_spoc'] == 1) {
                        $_SESSION['role'] = 'spoc';
                    } elseif (strtolower(trim($currentUser['designation'] ?? '')) === 'student') {
                        $_SESSION['role'] = 'student';
                    } else {
                        $_SESSION['role'] = 'alumni'; // Fallback for old records
                    }
                }
            }

            // Check for redirect URL
            if (isset($_SESSION['redirect_url'])) {
                $redirect_url = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                header("Location: $redirect_url");
            }
            // Redirect directors to admin dashboard if no specific redirect
            else if (isset($currentUser['is_director']) && $currentUser['is_director'] == 1) {
                header("Location: /alumni/admin/admin_dashboard.php");
            }
            // Redirect SPOC to spoc dashboard
            else if (isset($currentUser['is_spoc']) && $currentUser['is_spoc'] == 1) {
                header("Location: /alumni/spoc_dashboard.php");
            }
            // Redirect students to student dashboard
            else if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
                header("Location: /alumni/pages/student_dashboard.php");
            }
            // Redirect alumni to the home page
            else {
                header("Location: /alumni/index.php");
            }
            exit();
        } else {
            // User not found or inactive, clear session
            session_unset();
            session_destroy();
            session_start();
        }
    } catch (PDOException $e) {
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
            $is_alumni = false;

            // Check students table first (gmu database)
            $stmt = $pdo_gmu->prepare("SELECT *, USER_NAME as usn, PASSWORD as password, NAME as name, COLLEGE as institute, DESIGNATION as designation, '' as email_id, SL_NO as id FROM users WHERE USER_NAME = ?");
            $stmt->execute([$usn]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Check users table (alumni database) for alumni/staff
                $stmt = $pdo->prepare("SELECT * FROM users WHERE usn = ?");
                $stmt->execute([$usn]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $is_alumni = true;
                }
            }
            if (!$user) {
                $error = "USN not found. Please check your USN.";
            } elseif (isset($user['is_active']) && $user['is_active'] == 0) {
                $error = "Your account is pending approval from your Branch SPOC.";
            } else {
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email_id'];
                    $_SESSION['is_alumni_table'] = $is_alumni;

                    // Check if user is a director
                    if (isset($user['is_director']) && $user['is_director'] == 1) {
                        $_SESSION['role'] = 'admin';
                        // Add specific debug log before redirect
                        file_put_contents('scratch/failed_login.log', date('Y-m-d H:i:s') . " - Director login successful, redirecting to admin_dashboard.php\n", FILE_APPEND);
                        header("Location: /alumni/admin/admin_dashboard.php");
                        exit();
                    } elseif (isset($user['is_spoc']) && $user['is_spoc'] == 1) {
                        $_SESSION['role'] = 'spoc';
                        file_put_contents('scratch/failed_login.log', date('Y-m-d H:i:s') . " - SPOC login successful, redirecting to spoc_dashboard.php\n", FILE_APPEND);
                        header("Location: /alumni/spoc/spoc_dashboard.php");
                        exit();
                    } elseif ($is_alumni) {
                        $_SESSION['role'] = 'alumni';
                        header("Location: /alumni/index.php");
                        exit();
                    } elseif (!$is_alumni) {
                        $_SESSION['role'] = 'student';
                        header("Location: /alumni/pages/student_dashboard.php");
                        exit();
                    } else {
                        $_SESSION['role'] = 'alumni'; // Fallback
                        header("Location: /alumni/index.php");
                        exit();
                    }
                } else {
                    $error = 'Incorrect password.';
                    $connInfo = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
                    file_put_contents('scratch/failed_login.log', "USN: $usn\nAttempted PW: '$password'\nDB Hash: " . $user['password'] . "\nTime: " . date('Y-m-d H:i:s') . "\nConn: " . $connInfo . "\n\n", FILE_APPEND);
                }
            }
        } catch (PDOException $e) {
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
    <title>Login - GM Alumni Network</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --text-light: #8b7070;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --border-radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8d5d5 0%, #f0e6e6 50%, #e3d7d7 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative elements */
        body::before {
            content: '🎓';
            position: absolute;
            top: 10%;
            right: 15%;
            font-size: 50px;
            opacity: 0.3;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '⭐';
            position: absolute;
            bottom: 15%;
            right: 10%;
            font-size: 60px;
            opacity: 0.3;
            animation: float 8s ease-in-out infinite reverse;
        }

        .decorative-butterfly {
            position: absolute;
            font-size: 40px;
            opacity: 0.3;
            animation: float 7s ease-in-out infinite;
        }

        .butterfly-1 {
            content: '🦋';
            bottom: 25%;
            left: 15%;
            animation-delay: 1s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        .login-wrapper {
            display: flex;
            max-width: 1100px;
            width: 100%;
            gap: 40px;
            align-items: center;
        }

        .login-container {
            background: var(--primary-color);
            border-radius: var(--border-radius);
            box-shadow: 0 20px 60px rgba(91, 31, 31, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 50px 40px 40px;
            text-align: left;
        }

        .login-header h1 {
            margin: 0 0 12px 0;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .login-header p {
            margin: 0;
            opacity: 0.85;
            font-size: 15px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.8);
        }

        .login-form {
            padding: 40px;
            background: var(--primary-color);
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 500;
            color: white;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 16px 18px;
            border: none;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.95);
            color: var(--text-color);
        }

        .form-group input::placeholder {
            color: #aaa;
        }

        .form-group input:focus {
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .error-message {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            border: 1px solid #fecaca;
            text-align: center;
            font-weight: 500;
        }

        .success-message {
            background-color: #f0fdf4;
            color: #16a34a;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            border: 1px solid #bbf7d0;
            text-align: center;
            font-weight: 500;
        }

        .login-btn {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 16px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            background-color: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            padding: 12px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-5px);
        }

        /* Branding card */
        .brand-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            max-width: 450px;
            width: 100%;
            position: relative;
        }

        .brand-icon {
            width: 120px;
            height: 120px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 8px 30px rgba(91, 31, 31, 0.2);
        }

        .brand-icon i {
            font-size: 60px;
            color: white;
        }

        .brand-card h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .brand-card p {
            color: var(--text-light);
            font-size: 16px;
            font-weight: 500;
        }

        .decorative-icon {
            position: absolute;
            opacity: 0.15;
            font-size: 30px;
        }

        .icon-1 {
            top: 20px;
            right: 30px;
        }

        .icon-2 {
            bottom: 30px;
            left: 30px;
        }

        /* Profile section for logged-in users */
        .profile-section {
            padding: 40px;
            text-align: center;
            background: white;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--primary-color);
            box-shadow: 0 8px 20px rgba(91, 31, 31, 0.2);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h2 {
            margin: 0 0 8px 0;
            color: var(--text-color);
            font-size: 26px;
            font-weight: 700;
        }

        .profile-info p {
            margin: 6px 0;
            color: var(--text-light);
            font-size: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
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
            box-shadow: 0 8px 20px rgba(91, 31, 31, 0.3);
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

        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column;
            }

            .brand-card {
                order: -1;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                border-radius: 16px;
            }

            .login-header,
            .login-form,
            .profile-section {
                padding: 32px 24px;
            }

            .login-header h1 {
                font-size: 30px;
            }

            .brand-card {
                padding: 40px 30px;
            }

            .brand-card h2 {
                font-size: 28px;
            }

            .brand-icon {
                width: 100px;
                height: 100px;
            }

            .brand-icon i {
                font-size: 50px;
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
    <div class="decorative-butterfly butterfly-1">🦋</div>

    <div class="login-wrapper">
        <!-- Login Form Section -->
        <div class="login-container">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your GM Alumni account</p>
            </div>

            <?php if (!$isLoggedIn): ?>
                <!-- Login Form -->
                <form class="login-form" method="POST" action="">
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="usn">USN (University Seat Number)</label>
                        <input type="text" id="usn" name="usn" placeholder="Enter your USN" required
                            value="<?php echo htmlspecialchars($_POST['usn'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>

                    <div style="text-align: center; margin-top: 16px;">
                        <a href="register.php"
                            style="color: rgba(255,255,255,0.9); text-decoration: none; font-size: 14px; transition: color 0.3s;"
                            onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.9)'">
                            Don't have an account? <strong>Register</strong>
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <!-- User Profile Section -->
                <div class="profile-section">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['name']); ?>&background=5b1f1f&color=fff&size=200"
                                alt="Profile" />
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($currentUser['name']); ?></h2>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($currentUser['email_id']); ?></p>
                            <p><i class="fas fa-id-card"></i> <strong>USN:</strong>
                                <?php echo htmlspecialchars($currentUser['usn']); ?></p>
                            <?php if (!empty($currentUser['designation'])): ?>
                                <p><i class="fas fa-briefcase"></i> <strong>Role:</strong>
                                    <?php echo htmlspecialchars($currentUser['designation']); ?></p>
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
            <?php endif; ?>
        </div>

        <!-- Branding Card -->
        <div class="brand-card">
            <div class="decorative-icon icon-1">🎓</div>
            <div class="decorative-icon icon-2">🦋</div>

            <div class="brand-icon">
                <i class="fas fa-university"></i>
            </div>
            <h2>Gems of GM</h2>
            <p>Connecting Success Stories</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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



