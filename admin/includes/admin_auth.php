<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../../includes/db_config.php';

// Function to get database connection
function getDBConnection() {
    global $pdo;
    if (!isset($pdo)) {
        // Fallback to direct connection if $pdo is not available
        $host = 'localhost:3307';
        $dbname = 'alumni';
        $username = 'root';
        $password = '';
        
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("A database error occurred. Please try again later.");
        }
    }
    return $pdo;
}

// Function to check if user is logged in and is an admin/director
function isAdmin() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        return false;
    }

    // Check if user is a director (admin)
    if (isset($_SESSION['is_director']) && $_SESSION['is_director'] == 1) {
        return true;
    }

    // If not in session, check database
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT is_director FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && $user['is_director'] == 1) {
            $_SESSION['is_director'] = 1;
            return true;
        }
    } catch (PDOException $e) {
        error_log("Admin auth error: " . $e->getMessage());
    }
    
    return false;
}

// Redirect to login if not admin
if (!isAdmin() && basename($_SERVER['PHP_SELF']) != 'login.php') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Get admin user data
function getAdminUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, profile_picture FROM users WHERE id = ? AND is_director = 1 AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching admin user: " . $e->getMessage());
        return null;
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Add CSRF token to forms
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Verify CSRF token from POST data
function verifyCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }
    }
}

// Call verifyCSRF at the beginning of any admin page that processes forms
// verifyCSRF(); // Uncomment this line in files that process forms
?>
