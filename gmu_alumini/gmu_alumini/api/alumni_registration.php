<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../config/database.php";

class AlumniRegistrationAPI {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Submit alumni registration request
    public function submitRegistrationRequest($data) {
        try {
            // Check if USN or email already exists in requests
            $checkQuery = "SELECT id, status FROM alumni_registration_requests WHERE usn = :usn OR email_id = :email_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(":usn", $data['usn']);
            $checkStmt->bindParam(":email_id", $data['email_id']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($existing['status'] === 'pending') {
                    return [
                        'success' => false,
                        'message' => 'A registration request with this USN or email is already pending review.'
                    ];
                } elseif ($existing['status'] === 'approved') {
                    return [
                        'success' => false,
                        'message' => 'This USN or email is already registered as an alumni.'
                    ];
                }
            }

            // Check if already exists in users table
            $userCheckQuery = "SELECT id FROM users WHERE usn = :usn OR email_id = :email_id";
            $userCheckStmt = $this->db->prepare($userCheckQuery);
            $userCheckStmt->bindParam(":usn", $data['usn']);
            $userCheckStmt->bindParam(":email_id", $data['email_id']);
            $userCheckStmt->execute();
            
            if ($userCheckStmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'This USN or email is already registered in the system.'
                ];
            }

            // Insert registration request
            $query = "INSERT INTO alumni_registration_requests 
                      (full_name, usn, email_id, phone_number, year_of_graduation, institute, branch, 
                       current_designation, current_company, current_city, linkedin_profile, reason_for_joining) 
                      VALUES 
                      (:full_name, :usn, :email_id, :phone_number, :year_of_graduation, :institute, :branch,
                       :current_designation, :current_company, :current_city, :linkedin_profile, :reason_for_joining)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":full_name", $data['full_name']);
            $stmt->bindParam(":usn", $data['usn']);
            $stmt->bindParam(":email_id", $data['email_id']);
            $stmt->bindParam(":phone_number", $data['phone_number']);
            $stmt->bindParam(":year_of_graduation", $data['year_of_graduation']);
            $stmt->bindParam(":institute", $data['institute']);
            $stmt->bindParam(":branch", $data['branch']);
            $stmt->bindParam(":current_designation", $data['current_designation']);
            $stmt->bindParam(":current_company", $data['current_company']);
            $stmt->bindParam(":current_city", $data['current_city']);
            $stmt->bindParam(":linkedin_profile", $data['linkedin_profile']);
            $stmt->bindParam(":reason_for_joining", $data['reason_for_joining']);
            
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Alumni registration request submitted successfully. You will be notified once reviewed.',
                'request_id' => $this->db->lastInsertId()
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    // Get pending registration requests (for directors)
    public function getPendingRequests($limit = 20, $offset = 0) {
        try {
            $query = "SELECT * FROM alumni_registration_requests 
                      WHERE status = 'pending' 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            $requests = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $requests[] = $row;
            }

            return [
                'success' => true,
                'requests' => $requests
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    // Approve registration request
    public function approveRequest($request_id, $director_id) {
        try {
            // Get request details
            $getQuery = "SELECT * FROM alumni_registration_requests WHERE id = :request_id AND status = 'pending'";
            $getStmt = $this->db->prepare($getQuery);
            $getStmt->bindParam(":request_id", $request_id);
            $getStmt->execute();

            if ($getStmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Registration request not found or already processed.'
                ];
            }

            $request = $getStmt->fetch(PDO::FETCH_ASSOC);

            // Generate default password (first 4 letters of name + last 4 digits of USN)
            $namePrefix = substr(strtolower(str_replace(' ', '', $request['full_name'])), 0, 4);
            $usnSuffix = substr($request['usn'], -4);
            $defaultPassword = $namePrefix . $usnSuffix;
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

            // Begin transaction
            $this->db->beginTransaction();

            try {
                // Insert into users table
                $userQuery = "INSERT INTO users 
                              (name, usn, email_id, password, phone_number, year_of_graduation, institute, branch,
                               designation, company, city, is_director, is_active) 
                              VALUES 
                              (:name, :usn, :email_id, :password, :phone_number, :year_of_graduation, :institute, :branch,
                               :designation, :company, :city, FALSE, TRUE)";
                
                $userStmt = $this->db->prepare($userQuery);
                $userStmt->bindParam(":name", $request['full_name']);
                $userStmt->bindParam(":usn", $request['usn']);
                $userStmt->bindParam(":email_id", $request['email_id']);
                $userStmt->bindParam(":password", $hashedPassword);
                $userStmt->bindParam(":phone_number", $request['phone_number']);
                $userStmt->bindParam(":year_of_graduation", $request['year_of_graduation']);
                $userStmt->bindParam(":institute", $request['institute']);
                $userStmt->bindParam(":branch", $request['branch']);
                $userStmt->bindParam(":designation", $request['current_designation']);
                $userStmt->bindParam(":company", $request['current_company']);
                $userStmt->bindParam(":city", $request['current_city']);
                
                $userStmt->execute();

                // Update request status
                $updateQuery = "UPDATE alumni_registration_requests 
                                SET status = 'approved', reviewed_at = NOW(), reviewed_by = :director_id 
                                WHERE id = :request_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(":director_id", $director_id);
                $updateStmt->bindParam(":request_id", $request_id);
                $updateStmt->execute();

                $this->db->commit();

                return [
                    'success' => true,
                    'message' => 'Alumni registration approved successfully.',
                    'default_password' => $defaultPassword,
                    'user_email' => $request['email_id']
                ];
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    // Reject registration request
    public function rejectRequest($request_id, $director_id, $reason = '') {
        try {
            $query = "UPDATE alumni_registration_requests 
                      SET status = 'rejected', reviewed_at = NOW(), reviewed_by = :director_id, rejection_reason = :reason 
                      WHERE id = :request_id AND status = 'pending'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":director_id", $director_id);
            $stmt->bindParam(":request_id", $request_id);
            $stmt->bindParam(":reason", $reason);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Registration request not found or already processed.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Alumni registration request rejected.'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$alumniAPI = new AlumniRegistrationAPI();
$result = ['success' => false, 'message' => 'Invalid request'];

switch($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'get_pending_requests':
                    $limit = $_GET['limit'] ?? 20;
                    $offset = $_GET['offset'] ?? 0;
                    $result = $alumniAPI->getPendingRequests($limit, $offset);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        }
        break;

    case 'POST':
        if (isset($input['action'])) {
            switch($input['action']) {
                case 'submit_request':
                    $result = $alumniAPI->submitRegistrationRequest($input);
                    break;
                case 'approve_request':
                    $director_id = $input['director_id'] ?? $_SESSION['user_id'] ?? null;
                    if (!$director_id) {
                        $result = ['success' => false, 'message' => 'Director not authenticated'];
                    } else {
                        $result = $alumniAPI->approveRequest($input['request_id'], $director_id);
                    }
                    break;
                case 'reject_request':
                    $director_id = $input['director_id'] ?? $_SESSION['user_id'] ?? null;
                    if (!$director_id) {
                        $result = ['success' => false, 'message' => 'Director not authenticated'];
                    } else {
                        $result = $alumniAPI->rejectRequest($input['request_id'], $director_id, $input['reason'] ?? '');
                    }
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
        }
        break;

    default:
        $result = ['success' => false, 'message' => 'Method not allowed'];
}

echo json_encode($result);
?>
