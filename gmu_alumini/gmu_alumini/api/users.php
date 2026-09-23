<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../config/database.php";

class UsersAPI {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getUsers($search = '', $limit = 20, $offset = 0, $current_user_id = null) {
        try {
            // Get current user ID from parameter or session
            $user_id = $current_user_id ?? $_SESSION['user_id'] ?? 0;
            
            // Enhanced search condition to include designation
            $search_condition = $search ? "AND (u.name LIKE :search OR u.usn LIKE :search OR u.designation LIKE :search)" : "";

            $query = "SELECT u.*, u.profile_picture";
            
            // Add follow status only if user is logged in
            if ($user_id > 0) {
                $query .= ", (SELECT COUNT(*) FROM follows WHERE follower_id = :current_user_id AND following_id = u.id) as is_following";
            } else {
                $query .= ", 0 as is_following";
            }
            
            $query .= ", (SELECT COUNT(*) FROM follows WHERE following_id = u.id) as followers_count,
                         (SELECT COUNT(*) FROM posts WHERE user_id = u.id AND status = 'approved') as posts_count
                      FROM users u
                      WHERE u.is_active = 1";
            
            // Exclude current user only if logged in
            if ($user_id > 0) {
                $query .= " AND u.id != :current_user_id";
            }
            
            $query .= " $search_condition ORDER BY u.name LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($query);
            
            if ($user_id > 0) {
                $stmt->bindParam(":current_user_id", $user_id);
            }

            if($search) {
                $search_param = "%$search%";
                $stmt->bindParam(":search", $search_param);
            }

            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            $users = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'] ?: '',
                    'usn' => $row['usn'] ?: '',
                    'email' => $row['email_id'] ?: '',
                    'profile_picture' => $row['profile_picture'] ?: 'default.jpg',
                    'bio' => $row['bio'] ?: '',
                    'is_director' => (bool)($row['is_director'] ?? false),
                    'is_active' => (bool)($row['is_active'] ?? true),
                    'phone_number' => $row['phone_number'] ?: null,
                    'year_of_graduation' => $row['year_of_graduation'] ? (int)$row['year_of_graduation'] : null,
                    'institute' => $row['institute'] ?: null,
                    'branch' => $row['branch'] ?: null,
                    'designation' => $row['designation'] ?: null,
                    'created_at' => $row['created_at'] ?: null,
                    'updated_at' => $row['updated_at'] ?: null,
                    'is_following' => (bool)($row['is_following'] ?? false),
                    'followers_count' => (int)($row['followers_count'] ?? 0),
                    'posts_count' => (int)($row['posts_count'] ?? 0)
                ];
            }

            return [
                'success' => true,
                'users' => $users
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function getUserById($user_id, $current_user_id = null) {
        try {
            // Get current user ID from parameter or session
            $current_id = $current_user_id ?? $_SESSION['user_id'] ?? null;
            
            $query = "SELECT u.*, u.profile_picture";
            
            // Add follow status only if current user is provided
            if ($current_id) {
                $query .= ", (SELECT COUNT(*) FROM follows WHERE follower_id = :current_user_id AND following_id = u.id) as is_following";
            } else {
                $query .= ", 0 as is_following";
            }
            
            $query .= ", (SELECT COUNT(*) FROM follows WHERE following_id = u.id) as followers_count,
                         (SELECT COUNT(*) FROM posts WHERE user_id = u.id AND status = 'approved') as posts_count
                      FROM users u
                      WHERE u.id = :user_id AND u.is_active = 1";

            $stmt = $this->db->prepare($query);
            
            if ($current_id) {
                $stmt->bindParam(":current_user_id", $current_id);
            }
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'user' => [
                        'id' => (int)$row['id'],
                        'name' => $row['name'] ?: '',
                        'usn' => $row['usn'] ?: '',
                        'email' => $row['email_id'] ?: '',
                        'profile_picture' => $row['profile_picture'] ?: 'default.jpg',
                        'bio' => $row['bio'] ?: '',
                        'is_director' => (bool)($row['is_director'] ?? false),
                        'is_active' => (bool)($row['is_active'] ?? true),
                        'phone_number' => $row['phone_number'] ?: null,
                        'year_of_graduation' => $row['year_of_graduation'] ? (int)$row['year_of_graduation'] : null,
                        'institute' => $row['institute'] ?: null,
                        'branch' => $row['branch'] ?: null,
                        'designation' => $row['designation'] ?: null,
                        'company' => $row['company'] ?: null,
                        'city' => $row['city'] ?: null,
                        'created_at' => $row['created_at'] ?: null,
                        'updated_at' => $row['updated_at'] ?: null,
                        'is_following' => (bool)($row['is_following'] ?? false),
                        'followers_count' => (int)($row['followers_count'] ?? 0),
                        'posts_count' => (int)($row['posts_count'] ?? 0)
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

    public function followUser($user_id, $follower_id = null) {
        try {
            // Get follower ID from parameter or session
            $current_follower_id = $follower_id ?? $_SESSION['user_id'] ?? null;
            
            if (!$current_follower_id) {
                return [
                    'success' => false,
                    'message' => 'User not authenticated'
                ];
            }
            
            // Check if already following
            $check_query = "SELECT id FROM follows WHERE follower_id = :follower_id AND following_id = :following_id";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->bindParam(":follower_id", $current_follower_id);
            $check_stmt->bindParam(":following_id", $user_id);
            $check_stmt->execute();

            if($check_stmt->rowCount() > 0) {
                // Unfollow
                $query = "DELETE FROM follows WHERE follower_id = :follower_id AND following_id = :following_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":follower_id", $current_follower_id);
                $stmt->bindParam(":following_id", $user_id);
                $stmt->execute();

                return [
                    'success' => true,
                    'message' => 'User unfollowed',
                    'action' => 'unfollowed'
                ];
            } else {
                // Follow
                $query = "INSERT INTO follows (follower_id, following_id) VALUES (:follower_id, :following_id)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":follower_id", $current_follower_id);
                $stmt->bindParam(":following_id", $user_id);
                $stmt->execute();

                return [
                    'success' => true,
                    'message' => 'User followed',
                    'action' => 'followed'
                ];
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function updateProfile($user_id, $data) {
        try {
            // Build dynamic query based on provided data
            $updateFields = [];
            $params = [':user_id' => $user_id];
            
            if (isset($data['bio'])) {
                $updateFields[] = "bio = :bio";
                $params[':bio'] = $data['bio'];
            }
            
            if (isset($data['profile_picture'])) {
                $updateFields[] = "profile_picture = :profile_picture";
                $params[':profile_picture'] = $data['profile_picture'];
            }
            
            if (isset($data['designation'])) {
                $updateFields[] = "designation = :designation";
                $params[':designation'] = $data['designation'];
            }
            
            if (isset($data['company'])) {
                $updateFields[] = "company = :company";
                $params[':company'] = $data['company'];
            }
            
            if (isset($data['city'])) {
                $updateFields[] = "city = :city";
                $params[':city'] = $data['city'];
            }
            
            if (isset($data['phone_number'])) {
                $updateFields[] = "phone_number = :phone_number";
                $params[':phone_number'] = $data['phone_number'];
            }
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

$usersAPI = new UsersAPI();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'get_users':
                    $search = $_GET['search'] ?? '';
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                    $current_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : ($_SESSION['user_id'] ?? null);
                    $result = $usersAPI->getUsers($search, $limit, $offset, $current_user_id);
                    break;
                case 'get_user':
                    $user_id = (int)$_GET['user_id'];
                    $current_user_id = isset($_GET['current_user_id']) ? (int)$_GET['current_user_id'] : ($_SESSION['user_id'] ?? null);
                    $result = $usersAPI->getUserById($user_id, $current_user_id);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'No action specified'];
        }
        break;

    case 'POST':
        if(isset($input['action'])) {
            switch($input['action']) {
                case 'follow_user':
                    $follower_id = $input['follower_id'] ?? $_SESSION['user_id'] ?? null;
                    $result = $usersAPI->followUser($input['user_id'], $follower_id);
                    break;
                case 'update_profile':
                    $user_id = $input['user_id'] ?? $_SESSION['user_id'] ?? null;
                    if (!$user_id) {
                        $result = ['success' => false, 'message' => 'User not authenticated'];
                    } else {
                        $result = $usersAPI->updateProfile($user_id, $input);
                    }
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'No action specified'];
        }
        break;

    default:
        $result = ['success' => false, 'message' => 'Method not allowed'];
}

echo json_encode($result);
?>
