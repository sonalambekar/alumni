<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

require_once "../config/database.php";

class AuthAPI {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($usn, $password) {
        try {
            $query = "SELECT * FROM users WHERE usn = :usn AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":usn", $usn);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // For demo purposes, using a simple password check
                // In production, use password_verify()
                if($password === "password123" || password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email_id'];
                    $_SESSION['is_director'] = $user['is_director'];

                    return [
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email_id'],
                            'usn' => $user['usn'],
                            'profile_picture' => $user['profile_picture'] ?: 'default.jpg',
                            'is_director' => (bool)$user['is_director'],
                            'bio' => $user['bio'] ?: '',
                            'institute' => $user['institute'] ?: '',
                            'branch' => $user['branch'] ?: '',
                            'phone_number' => $user['phone_number'] ?: '',
                            'year_of_graduation' => $user['year_of_graduation'] ?: null,
                            'designation' => $user['designation'] ?: '',
                            'company' => $user['company'] ?: '',
                            'city' => $user['city'] ?: '',
                            'is_active' => (bool)$user['is_active']
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Invalid password'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'USN not found or account inactive'
                ];
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function logout() {
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }

    public function getCurrentUser() {
        if(!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Not authenticated'
            ];
        }

        try {
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $_SESSION['user_id']);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email_id'],
                        'usn' => $user['usn'],
                        'profile_picture' => $user['profile_picture'] ?: 'default.jpg',
                        'is_director' => (bool)$user['is_director'],
                        'bio' => $user['bio'] ?: '',
                        'institute' => $user['institute'] ?: '',
                        'branch' => $user['branch'] ?: '',
                        'phone_number' => $user['phone_number'] ?: '',
                        'year_of_graduation' => $user['year_of_graduation'] ?: null,
                        'designation' => $user['designation'] ?: '',
                        'company' => $user['company'] ?: '',
                        'city' => $user['city'] ?: '',
                        'is_active' => (bool)$user['is_active']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

$authAPI = new AuthAPI();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'POST':
        if(isset($input['action'])) {
            switch($input['action']) {
                case 'login':
                    $result = $authAPI->login($input['usn'], $input['password']);
                    break;
                case 'logout':
                    $result = $authAPI->logout();
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'No action specified'];
        }
        break;

    case 'GET':
        if(isset($_GET['action']) && $_GET['action'] === 'current_user') {
            $result = $authAPI->getCurrentUser();
        } else {
            $result = ['success' => false, 'message' => 'Invalid action'];
        }
        break;

    default:
        $result = ['success' => false, 'message' => 'Method not allowed'];
}

echo json_encode($result);
?>
