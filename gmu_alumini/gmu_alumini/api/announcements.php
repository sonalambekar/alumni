<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../config/database.php";

class AnnouncementsAPI {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAnnouncements($user_institute, $limit = 20, $offset = 0) {
        try {
            $query = "SELECT a.*, u.name as director_name, u.profile_picture as director_profile_picture
                      FROM announcements a
                      JOIN users u ON a.director_id = u.id
                      WHERE (a.is_global = 1 OR a.target_institute = :user_institute OR a.target_institute IS NULL)
                      ORDER BY a.created_at DESC
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_institute", $user_institute);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            $announcements = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $announcements[] = [
                    'id' => (int)$row['id'],
                    'director_id' => (int)$row['director_id'],
                    'title' => $row['title'],
                    'content' => $row['content'],
                    'target_institute' => $row['target_institute'],
                    'is_global' => (bool)$row['is_global'],
                    'created_at' => $row['created_at'],
                    'director_name' => $row['director_name'],
                    'director_profile_picture' => $row['director_profile_picture'] ?: 'default.jpg'
                ];
            }

            return [
                'success' => true,
                'data' => $announcements
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function createAnnouncement($title, $content, $director_id = null, $is_global = false, $target_institute = null) {
        try {
            // Get director ID from parameter or session
            $current_director_id = $director_id ?? $_SESSION['user_id'] ?? null;
            
            if (!$current_director_id) {
                return [
                    'success' => false,
                    'message' => 'Director not authenticated'
                ];
            }
            
            // If not global and no target institute specified, get director's institute
            if (!$is_global && !$target_institute) {
                $director_query = "SELECT institute FROM users WHERE id = :director_id AND is_director = 1";
                $director_stmt = $this->db->prepare($director_query);
                $director_stmt->bindParam(":director_id", $current_director_id);
                $director_stmt->execute();
                $director_row = $director_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($director_row) {
                    $target_institute = $director_row['institute'];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Director not found or invalid'
                    ];
                }
            }
            
            $query = "INSERT INTO announcements (director_id, title, content, target_institute, is_global, created_at) 
                      VALUES (:director_id, :title, :content, :target_institute, :is_global, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":director_id", $current_director_id);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":target_institute", $target_institute);
            $stmt->bindParam(":is_global", $is_global, PDO::PARAM_BOOL);
            $stmt->execute();

            $announcement_id = $this->db->lastInsertId();
            
            // Get the created announcement with director info
            $select_query = "SELECT a.*, u.name as director_name, u.profile_picture as director_profile_picture
                            FROM announcements a
                            JOIN users u ON a.director_id = u.id
                            WHERE a.id = :id";
            
            $select_stmt = $this->db->prepare($select_query);
            $select_stmt->bindParam(":id", $announcement_id);
            $select_stmt->execute();
            $row = $select_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $announcement = [
                    'id' => (int)$row['id'],
                    'director_id' => (int)$row['director_id'],
                    'title' => $row['title'],
                    'content' => $row['content'],
                    'target_institute' => $row['target_institute'],
                    'is_global' => (bool)$row['is_global'],
                    'created_at' => $row['created_at'],
                    'director_name' => $row['director_name'],
                    'director_profile_picture' => $row['director_profile_picture'] ?: 'default.jpg'
                ];
                
                return [
                    'success' => true,
                    'message' => 'Announcement created successfully',
                    'data' => $announcement
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to retrieve created announcement'
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

$announcementsAPI = new AnnouncementsAPI();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        // New format: GET /api/announcements.php?user_institute=GMU&limit=20&offset=0
        if(isset($_GET['user_institute'])) {
            $user_institute = $_GET['user_institute'];
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $result = $announcementsAPI->getAnnouncements($user_institute, $limit, $offset);
        } else if(isset($_GET['action'])) {
            // Backward compatibility with old format
            switch($_GET['action']) {
                case 'get_announcements':
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                    // For backward compatibility, show all announcements if no institute specified
                    $result = $announcementsAPI->getAnnouncements('', $limit, $offset);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'user_institute parameter is required'];
        }
        break;

    case 'POST':
        // New format: Direct POST with data
        if(isset($input['director_id']) && isset($input['title']) && isset($input['content'])) {
            $director_id = $input['director_id'];
            $title = $input['title'];
            $content = $input['content'];
            $is_global = $input['is_global'] ?? false;
            $target_institute = $input['target_institute'] ?? null;
            $result = $announcementsAPI->createAnnouncement($title, $content, $director_id, $is_global, $target_institute);
        } else if(isset($input['action'])) {
            // Backward compatibility with old format
            switch($input['action']) {
                case 'create_announcement':
                    $director_id = $input['director_id'] ?? $_SESSION['user_id'] ?? null;
                    $result = $announcementsAPI->createAnnouncement($input['title'], $input['content'], $director_id);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'Required fields: director_id, title, content'];
        }
        break;

    default:
        $result = ['success' => false, 'message' => 'Method not allowed'];
}

echo json_encode($result);
?>
