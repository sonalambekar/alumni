<?php
$host = '127.0.0.1';
$db   = 'alumni';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';
$port = '3306';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Add connection for the GMU (students) database
    $dsn_gmu = "mysql:host=$host;port=$port;dbname=gmu;charset=$charset";
    $pdo_gmu = new PDO($dsn_gmu, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Helper function to check if user is logged in
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

// Helper function to get current user data
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        if (!isLoggedIn()) {
            return null;
        }

        global $pdo, $pdo_gmu;
        
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
            $stmt = $pdo_gmu->prepare("SELECT *, USER_NAME as usn, PASSWORD as password, NAME as name, COLLEGE as institute, DESIGNATION as designation, '' as email_id, SL_NO as id FROM users WHERE SL_NO = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        }
    }
}

// Helper function to redirect if not logged in
if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isLoggedIn()) {
            header("Location: /alumni/login.php");
            exit();
        }
    }
}

// Helper function to redirect if logged in (for login page)
if (!function_exists('redirectIfLoggedIn')) {
    function redirectIfLoggedIn() {
        if (isLoggedIn()) {
            header("Location: /alumni/index.php");
            exit();
        }
    }
}
?>




