<?php
require_once '../includes/db_config.php';

header('Content-Type: application/json');

function migrateStudents($pdo) {
    try {
        // Check if students table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'students'");
        if ($tableCheck->rowCount() == 0) {
            return [
                'success' => false,
                'message' => 'Students table does not exist. Please run the create_students_table.sql script first.'
            ];
        }

        // Get all student users
        $stmt = $pdo->query("SELECT ID, NAME, USER_NAME, EMAIL FROM users WHERE USER_TYPE = 'student'");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($users)) {
            return [
                'success' => false,
                'message' => 'No student users found in the users table.'
            ];
        }

        $imported = 0;
        $skipped = 0;

        // Prepare the insert statement
        $insertStmt = $pdo->prepare("
            INSERT INTO students (student_id, name, email, usn)
            VALUES (:student_id, :name, :email, :usn)
            ON DUPLICATE KEY UPDATE 
                name = VALUES(name),
                email = VALUES(email),
                usn = VALUES(usn)
        ");

        // Begin transaction
        $pdo->beginTransaction();

        foreach ($users as $user) {
            try {
                $insertStmt->execute([
                    ':student_id' => $user['ID'],
                    ':name' => $user['NAME'],
                    ':email' => $user['EMAIL'],
                    ':usn' => $user['USER_NAME']
                ]);
                $imported++;
            } catch (PDOException $e) {
                // Log the error but continue with other records
                error_log("Error importing student {$user['ID']}: " . $e->getMessage());
                $skipped++;
            }
        }

        $pdo->commit();

        return [
            'success' => true,
            'message' => "Successfully imported $imported students. $skipped records skipped.",
            'imported' => $imported,
            'skipped' => $skipped
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage()
        ];
    }
}

// Only allow access from admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Handle migration request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = migrateStudents($pdo);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
