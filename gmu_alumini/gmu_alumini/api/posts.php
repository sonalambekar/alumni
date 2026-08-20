<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../config/database.php";

class PostsAPI {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getPosts($user_institute, $user_id = null, $limit = 20, $offset = 0) {
        try {
            // Get current user ID from parameter or session
            $current_user_id = $user_id ?? $_SESSION['user_id'] ?? null;
            
            // Show approved posts filtered by institute
            $query = "SELECT p.*, u.name, u.profile_picture, u.usn, u.institute,
                             (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                             (SELECT COUNT(*) FROM shares WHERE post_id = p.id) as share_count";
            
            // Add is_liked only if user is logged in
            if ($current_user_id) {
                $query .= ", (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = :current_user_id) as is_liked";
            } else {
                $query .= ", 0 as is_liked";
            }
            
            $query .= " FROM posts p
                       JOIN users u ON p.user_id = u.id
                       WHERE p.status = 'approved' 
                         AND (p.is_global = 1 OR p.target_institute = :user_institute OR p.target_institute IS NULL)
                       ORDER BY p.created_at DESC
                       LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($query);
            
            if ($current_user_id) {
                $stmt->bindParam(":current_user_id", $current_user_id);
            }
            $stmt->bindParam(":user_institute", $user_institute);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            $posts = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = [
                    'id' => (int)$row['id'],
                    'user_id' => (int)$row['user_id'],
                    'content' => $row['content'],
                    'media_type' => $row['media_type'],
                    'media_url' => $row['media_url'],
                    'status' => $row['status'],
                    'target_institute' => $row['target_institute'],
                    'is_global' => (bool)$row['is_global'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'user' => [
                        'id' => (int)$row['user_id'],
                        'name' => $row['name'],
                        'profile_picture' => $row['profile_picture'] ?: 'default.jpg',
                        'usn' => $row['usn'],
                        'institute' => $row['institute']
                    ],
                    'like_count' => (int)$row['like_count'],
                    'share_count' => (int)$row['share_count'],
                    'is_liked' => (bool)$row['is_liked']
                ];
            }

            return [
                'success' => true,
                'data' => $posts
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function createPost($content, $media_type = 'none', $media_url = null, $user_id = null, $is_global = false, $target_institute = null) {
        try {
            // Get user_id from parameter or session
            $current_user_id = $user_id ?? $_SESSION['user_id'] ?? null;
            
            if (!$current_user_id) {
                return [
                    'success' => false,
                    'message' => 'User not authenticated'
                ];
            }
            
            // If not global and no target institute specified, get user's institute
            if (!$is_global && !$target_institute) {
                $user_query = "SELECT institute FROM users WHERE id = :user_id";
                $user_stmt = $this->db->prepare($user_query);
                $user_stmt->bindParam(":user_id", $current_user_id);
                $user_stmt->execute();
                $user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user_row) {
                    $target_institute = $user_row['institute'];
                } else {
                    return [
                        'success' => false,
                        'message' => 'User not found'
                    ];
                }
            }
            
            // Clean media_url if it's a JSON string (fix for JSON response being stored)
            if ($media_url && is_string($media_url)) {
                // Remove leading backtick if present
                $cleanedUrl = $media_url;
                if (strpos($cleanedUrl, '`') === 0) {
                    $cleanedUrl = substr($cleanedUrl, 1);
                }
                
                // Check if it's a JSON string
                if (strpos($cleanedUrl, '{"success"') === 0 || strpos($cleanedUrl, '{') === 0) {
                    try {
                        $decoded = json_decode($cleanedUrl, true);
                        if (isset($decoded['url'])) {
                            $media_url = $decoded['url'];
                        }
                    } catch (Exception $e) {
                        // If JSON decode fails, keep original value
                        error_log("Failed to decode media URL JSON: " . $e->getMessage());
                    }
                }
            }
            
            $query = "INSERT INTO posts (user_id, content, media_type, media_url, target_institute, is_global, status, created_at) 
                      VALUES (:user_id, :content, :media_type, :media_url, :target_institute, :is_global, 'pending', NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $current_user_id);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":media_type", $media_type);
            $stmt->bindParam(":media_url", $media_url);
            $stmt->bindParam(":target_institute", $target_institute);
            $stmt->bindParam(":is_global", $is_global, PDO::PARAM_BOOL);
            $stmt->execute();

            $post_id = $this->db->lastInsertId();
            
            // Get the created post with user info
            $select_query = "SELECT p.*, u.name, u.profile_picture, u.usn, u.institute
                            FROM posts p
                            JOIN users u ON p.user_id = u.id
                            WHERE p.id = :id";
            
            $select_stmt = $this->db->prepare($select_query);
            $select_stmt->bindParam(":id", $post_id);
            $select_stmt->execute();
            $row = $select_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $post = [
                    'id' => (int)$row['id'],
                    'user_id' => (int)$row['user_id'],
                    'content' => $row['content'],
                    'media_type' => $row['media_type'],
                    'media_url' => $row['media_url'],
                    'status' => $row['status'],
                    'target_institute' => $row['target_institute'],
                    'is_global' => (bool)$row['is_global'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'user' => [
                        'id' => (int)$row['user_id'],
                        'name' => $row['name'],
                        'profile_picture' => $row['profile_picture'] ?: 'default.jpg',
                        'usn' => $row['usn'],
                        'institute' => $row['institute']
                    ],
                    'like_count' => 0,
                    'share_count' => 0,
                    'is_liked' => false
                ];
                
                return [
                    'success' => true,
                    'message' => 'Post created successfully',
                    'data' => $post
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to retrieve created post'
                ];
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function likePost($post_id) {
        try {
            // Check if already liked
            $check_query = "SELECT id FROM likes WHERE user_id = :user_id AND post_id = :post_id";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->bindParam(":user_id", $_SESSION['user_id']);
            $check_stmt->bindParam(":post_id", $post_id);
            $check_stmt->execute();

            if($check_stmt->rowCount() > 0) {
                // Unlike
                $query = "DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":user_id", $_SESSION['user_id']);
                $stmt->bindParam(":post_id", $post_id);
                $stmt->execute();

                return [
                    'success' => true,
                    'message' => 'Post unliked',
                    'action' => 'unliked'
                ];
            } else {
                // Like
                $query = "INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":user_id", $_SESSION['user_id']);
                $stmt->bindParam(":post_id", $post_id);
                $stmt->execute();

                return [
                    'success' => true,
                    'message' => 'Post liked',
                    'action' => 'liked'
                ];
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function sharePost($post_id) {
        try {
            $query = "INSERT INTO shares (user_id, post_id) VALUES (:user_id, :post_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
            $stmt->bindParam(":post_id", $post_id);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Post shared successfully'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function getPendingPosts($director_institute = null) {
        try {
            $query = "SELECT p.*, u.name, u.profile_picture, u.usn, u.institute
                      FROM posts p
                      JOIN users u ON p.user_id = u.id
                      WHERE p.status = 'pending'";
            
            // Filter by institute if director institute is provided
            if ($director_institute) {
                $query .= " AND (p.is_global = 1 OR p.target_institute = :director_institute OR p.target_institute IS NULL)";
            }
            
            $query .= " ORDER BY p.created_at ASC";

            $stmt = $this->db->prepare($query);
            
            if ($director_institute) {
                $stmt->bindParam(":director_institute", $director_institute);
            }
            
            $stmt->execute();

            $posts = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = [
                    'id' => (int)$row['id'],
                    'user_id' => (int)$row['user_id'],
                    'content' => $row['content'],
                    'media_type' => $row['media_type'],
                    'media_url' => $row['media_url'],
                    'status' => $row['status'],
                    'target_institute' => $row['target_institute'],
                    'is_global' => (bool)$row['is_global'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'user' => [
                        'id' => (int)$row['user_id'],
                        'name' => $row['name'],
                        'profile_picture' => $row['profile_picture'] ?: 'default.jpg',
                        'usn' => $row['usn'],
                        'institute' => $row['institute']
                    ]
                ];
            }

            return [
                'success' => true,
                'data' => $posts
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function approvePost($post_id) {
        try {
            $query = "UPDATE posts SET status = 'approved' WHERE id = :post_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":post_id", $post_id);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Post approved successfully'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function rejectPost($post_id) {
        try {
            $query = "UPDATE posts SET status = 'rejected' WHERE id = :post_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":post_id", $post_id);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Post rejected'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

$postsAPI = new PostsAPI();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        // New format: GET /api/posts.php?user_institute=GMU&current_user_id=1&limit=20&offset=0
        if(isset($_GET['user_institute'])) {
            $user_institute = $_GET['user_institute'];
            $current_user_id = isset($_GET['current_user_id']) ? (int)$_GET['current_user_id'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $result = $postsAPI->getPosts($user_institute, $current_user_id, $limit, $offset);
        } else if(isset($_GET['action'])) {
            // Backward compatibility with old format
            switch($_GET['action']) {
                case 'get_posts':
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : ($_SESSION['user_id'] ?? null);
                    // For backward compatibility, show all posts if no institute specified
                    $result = $postsAPI->getPosts('', $user_id, $limit, $offset);
                    break;
                case 'get_pending_posts':
                    $director_institute = isset($_GET['director_institute']) ? $_GET['director_institute'] : null;
                    $result = $postsAPI->getPendingPosts($director_institute);
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
        if(isset($input['user_id']) && isset($input['content'])) {
            $user_id = $input['user_id'];
            $content = $input['content'];
            $media_type = $input['media_type'] ?? 'none';
            $media_url = $input['media_url'] ?? null;
            $is_global = $input['is_global'] ?? false;
            $target_institute = $input['target_institute'] ?? null;
            $result = $postsAPI->createPost($content, $media_type, $media_url, $user_id, $is_global, $target_institute);
        } else if(isset($input['action'])) {
            // Backward compatibility with old format
            switch($input['action']) {
                case 'create_post':
                    $result = $postsAPI->createPost($input['content'], $input['media_type'] ?? 'none', $input['media_url'] ?? null, $input['user_id'] ?? null);
                    break;
                case 'like_post':
                    $result = $postsAPI->likePost($input['post_id']);
                    break;
                case 'share_post':
                    $result = $postsAPI->sharePost($input['post_id']);
                    break;
                case 'approve_post':
                    $result = $postsAPI->approvePost($input['post_id']);
                    break;
                case 'reject_post':
                    $result = $postsAPI->rejectPost($input['post_id']);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'message' => 'Required fields: user_id, content'];
        }
        break;

    default:
        $result = ['success' => false, 'message' => 'Method not allowed'];
}

echo json_encode($result);
?>
