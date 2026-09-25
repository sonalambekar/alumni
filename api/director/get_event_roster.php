<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/db_config.php';

$authHeader = '';
if (function_exists('getallheaders')) {
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    if (!empty($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    }
}
if (empty($authHeader) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}
if (empty($authHeader) && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}
if (empty($authHeader) && !empty($_SERVER['HTTP_X_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_X_AUTHORIZATION'];
}

$token = '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
} else {
    $token = trim($authHeader);
}

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, is_director FROM users WHERE auth_token = ? AND is_active = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['is_director'] != 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden: Director access required']);
        exit;
    }

    $eventId = $_GET['event_id'] ?? null;
    
    if (!$eventId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'event_id is required']);
        exit;
    }

    $eventStmt = $pdo->prepare("SELECT registered_alumni_ids, updated_at FROM events WHERE id = :event_id");
    $eventStmt->execute([':event_id' => $eventId]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    $roster = [];
    if ($event && !empty($event['registered_alumni_ids'])) {
        $registrations = json_decode($event['registered_alumni_ids'], true);
        if (is_array($registrations) && count($registrations) > 0) {
            $ids = [];
            foreach ($registrations as $reg) {
                if (is_array($reg) && isset($reg['id'])) {
                    $ids[] = $reg['id'];
                } elseif (is_numeric($reg)) {
                    $ids[] = $reg;
                }
            }
            
            if (count($ids) > 0) {
                $inQuery = implode(',', array_fill(0, count($ids), '?'));
                $userStmt = $pdo->prepare("SELECT id, name, email_id, usn, branch, year_of_graduation as batch, phone_number as phone FROM users WHERE id IN ($inQuery)");
                $userStmt->execute($ids);
                
                $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($users as &$u) {
                    $u['registered_at'] = $event['updated_at'];
                }
                $roster = $users;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $roster
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

