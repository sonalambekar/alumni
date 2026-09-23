<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../config/database.php";

class MessagesAPI {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getConversations() {
        try {
            $query = "SELECT DISTINCT
                             CASE
                               WHEN sender_id = :user_id THEN receiver_id
                               ELSE sender_id
                             END as other_user_id,
                             u.name, u.profile_picture,
                             (SELECT message FROM messages WHERE (sender_id = :user_id AND receiver_id = other_user_id) OR (sender_id = other_user_id AND receiver_id = :user_id) ORDER BY created_at DESC LIMIT 1) as last_message,
                             (SELECT created_at FROM messages WHERE (sender_id = :user_id AND receiver_id = other_user_id) OR (sender_id = other_user_id AND receiver_id = :user_id) ORDER BY created_at DESC LIMIT 1) as last_message_time,
                             (SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND sender_id = other_user_id AND is_read = 0) as unread_count
                      FROM messages m
                      JOIN users u ON u.id = (
                          CASE
                            WHEN sender_id = :user_id THEN receiver_id
                            ELSE sender_id
                          END
                      )
                      WHERE (sender_id = :user_id OR receiver_id = :user_id)
                      GROUP BY other_user_id
                      ORDER BY last_message_time DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
            $stmt->execute();

            $conversations = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $conversations[] = [
                    'user_id' => $row['other_user_id'],
                    'name' => $row['name'],
                    'profile_picture' => $row['profile_picture'] ?: 'default.jpg',
                    'last_message' => $row['last_message'] ?: '',
                    'last_message_time' => $row['last_message_time'],
                    'unread_count' => (int)$row['unread_count']
                ];
            }

            return [
                'success' => true,
                'conversations' => $conversations
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function getMessages($other_user_id, $limit = 50, $offset = 0, $current_user_id = null) {
        try {
            // Get current user ID from parameter or session
            $user_id = $current_user_id ?? $_SESSION['user_id'] ?? null;
            
            if (!$user_id) {
                return [
                    'success' => false,
                    'message' => 'User not authenticated'
                ];
            }
            
            // Mark messages as read
            $update_query = "UPDATE messages SET is_read = 1 WHERE sender_id = :other_user_id AND receiver_id = :user_id AND is_read = 0";
            $update_stmt = $this->db->prepare($update_query);
            $update_stmt->bindParam(":other_user_id", $other_user_id);
            $update_stmt->bindParam(":user_id", $user_id);
            $update_stmt->execute();

            // Get messages
            $query = "SELECT m.*, u.name as sender_name, u.profile_picture as sender_profile_picture
                      FROM messages m
                      JOIN users u ON m.sender_id = u.id
                      WHERE (m.sender_id = :user_id AND m.receiver_id = :other_user_id)
                         OR (m.sender_id = :other_user_id AND m.receiver_id = :user_id)
                      ORDER BY m.created_at DESC
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":other_user_id", $other_user_id);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            $messages = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $messages[] = [
                    'id' => $row['id'],
                    'sender_id' => $row['sender_id'],
                    'receiver_id' => $row['receiver_id'],
                    'message' => $row['message'],
                    'is_read' => (bool)$row['is_read'],
                    'created_at' => $row['created_at'],
                    'sender_name' => $row['sender_name'],
                    'sender_profile_picture' => $row['sender_profile_picture'] ?: 'default.jpg'
                ];
            }

            // Reverse array to show oldest first
            $messages = array_reverse($messages);

            return [
                'success' => true,
                'messages' => $messages
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function sendMessage($receiver_id, $message, $sender_id = null) {
        try {
            // Get sender ID from parameter or session
            $current_sender_id = $sender_id ?? $_SESSION['user_id'] ?? null;
            
            if (!$current_sender_id) {
                return [
                    'success' => false,
                    'message' => 'User not authenticated'
                ];
            }
            
            $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":sender_id", $current_sender_id);
            $stmt->bindParam(":receiver_id", $receiver_id);
            $stmt->bindParam(":message", $message);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Message sent successfully',
                'message_id' => $this->db->lastInsertId()
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function markAsRead($sender_id) {
        try {
            $query = "UPDATE messages SET is_read = 1 WHERE sender_id = :sender_id AND receiver_id = :receiver_id AND is_read = 0";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":sender_id", $sender_id);
            $stmt->bindParam(":receiver_id", $_SESSION['user_id']);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Messages marked as read'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

$messagesAPI = new MessagesAPI();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'get_conversations':
                    $result = $messagesAPI->getConversations();
                    break;
                case 'get_messages':
                    $other_user_id = (int)$_GET['user_id'];
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                    $current_user_id = isset($_GET['current_user_id']) ? (int)$_GET['current_user_id'] : ($_SESSION['user_id'] ?? null);
                    $result = $messagesAPI->getMessages($other_user_id, $limit, $offset, $current_user_id);
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
                case 'send_message':
                    $sender_id = $input['sender_id'] ?? $_SESSION['user_id'] ?? null;
                    $result = $messagesAPI->sendMessage($input['receiver_id'], $input['message'], $sender_id);
                    break;
                case 'mark_as_read':
                    $result = $messagesAPI->markAsRead($input['sender_id']);
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
