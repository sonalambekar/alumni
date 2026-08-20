<?php
session_start();
require_once __DIR__ . '/includes/db_config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';

// If already logged in, redirect
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    header("Location: /alumni/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usn = trim($_POST['usn'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $year = trim($_POST['year'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $branch = trim($_POST['branch'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $institute = 'GMU'; // Default or add a field if multiple institutes

    // Basic Validation
    if (empty($usn) || empty($name) || empty($email) || empty($password) || empty($year) || empty($phone)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            // Check if USN or Email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE usn = ? OR email_id = ?");
            $stmt->execute([$usn, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "USN or Email already registered.";
            } else {
                // Insert User
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $is_director = 0; // Default to alumni
                $is_active = 0; // Default to 0, requires SPOC approval
                $profile_picture = 'default.jpg'; // Default placeholder
                $bio = '';

                $sql = "INSERT INTO users (
                    name, usn, email_id, password, year_of_graduation, phone_number, 
                    branch, designation, institute, is_director, is_active, profile_picture, bio
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name,
                    $usn,
                    $email,
                    $hashed_password,
                    $year,
                    $phone,
                    $branch,
                    $designation,
                    $institute,
                    $is_director,
                    $is_active,
                    $profile_picture,
                    $bio
                ]);

                $success = "Registration successful! You can now login.";
                // Optionally redirect
                // header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
            error_log("Registration Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GM Alumni Network</title>
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
            overflow-x: hidden;
        }

        /* Decorative elements from login.php */
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

        .register-container {
            background: var(--primary-color);
            border-radius: var(--border-radius);
            box-shadow: 0 20px 60px rgba(91, 31, 31, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            position: relative;
        }

        .register-header {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 40px 40px 20px;
            text-align: center;
        }

        .register-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 700;
        }

        .register-header p {
            margin: 0;
            opacity: 0.85;
            font-size: 15px;
        }

        .register-form {
            padding: 40px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: white;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: none;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.95);
            color: var(--text-color);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .register-btn {
            width: 100%;
            background-color: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            padding: 16px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            filter: brightness(110%);
        }

        .login-link {
            display: block;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: white;
            text-decoration: underline;
        }

        .error-message {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #fecaca;
        }

        .success-message {
            background-color: #f0fdf4;
            color: #16a34a;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #bbf7d0;
        }

        @media (max-width: 480px) {
            .form-row {
                flex-direction: column;
                gap: 20px;
            }

            .register-container {
                margin: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="decorative-butterfly butterfly-1">🦋</div>

    <div class="register-container">
        <div class="register-header">
            <h1>Join GM Alumni</h1>
            <p>Create your account to connect with peers</p>
        </div>

        <form class="register-form" method="POST" action="">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    <br><a href="login.php" style="color: inherit; font-weight: bold;">Click here to Login</a>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="usn">USN *</label>
                    <input type="text" id="usn" name="usn" placeholder="University Seat No." required
                        value="<?php echo htmlspecialchars($_POST['usn'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" placeholder="Your Name" required
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="example@email.com" required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" placeholder="Mobile Number" required
                        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="year">Graduation Year *</label>
                    <input type="number" id="year" name="year" min="1950" max="<?php echo date('Y'); ?>"
                        placeholder="YYYY" required value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="branch">Branch *</label>
                    <select id="branch" name="branch" required>
                        <option value="" disabled selected>Select Branch</option>
                        <optgroup label="GMIT - Engineering Departments">
                            <option value="CSE" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'CSE') ? 'selected' : ''; ?>>CSE - Computer Science</option>
                            <option value="ISE" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'ISE') ? 'selected' : ''; ?>>ISE - Information Science</option>
                            <option value="ECE" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'ECE') ? 'selected' : ''; ?>>ECE - Electronics & Communication</option>
                            <option value="AIML" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'AIML') ? 'selected' : ''; ?>>AIML - AI & Machine Learning</option>
                            <option value="AIDS" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'AIDS') ? 'selected' : ''; ?>>AIDS - AI & Data Science</option>
                            <option value="MECH" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'MECH') ? 'selected' : ''; ?>>MECH - Mechanical</option>
                            <option value="CIVIL" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'CIVIL') ? 'selected' : ''; ?>>CIVIL - Civil Engineering</option>
                            <option value="EEE" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'EEE') ? 'selected' : ''; ?>>EEE - Electrical & Electronics</option>
                            <option value="R&A" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'R&A') ? 'selected' : ''; ?>>R&A - Robotics and Automation</option>
                            <option value="BT" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'BT') ? 'selected' : ''; ?>>BT-Biotech</option>
                        </optgroup>
                        <optgroup label="Other Schools">
                            <option value="GMBS" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'GMBS') ? 'selected' : ''; ?>>GMBS - Business School</option>
                            <option value="GMSAS" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'GMSAS') ? 'selected' : ''; ?>>GMSAS - Applied Sciences</option>
                            <option value="GMSL" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'GMSL') ? 'selected' : ''; ?>>GMSL - School of Law</option>
                            <option value="PHARMACY" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'PHARMACY') ? 'selected' : ''; ?>>PHARMACY - School of Pharmacy
                            </option>
                            <option value="GMS" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'GMS') ? 'selected' : ''; ?>>GMS Academy</option>
                            <option value="FCM" <?php echo (isset($_POST['branch']) && $_POST['branch'] === 'FCM') ? 'selected' : ''; ?>>FCM</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label for="designation">Current Designation</label>
                    <input type="text" id="designation" name="designation" placeholder="e.g. Software Engineer"
                        value="<?php echo htmlspecialchars($_POST['designation'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Retype password"
                        required>
                </div>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> Register
            </button>

            <a href="login.php" class="login-link">
                Already have an account? <strong>Login here</strong>
            </a>
        </form>
    </div>
</body>

</html>