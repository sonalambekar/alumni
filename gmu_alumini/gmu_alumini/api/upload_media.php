<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../config/database.php";

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

class MediaUpload {
    private $db;
    private $uploadDir;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->uploadDir = '../uploads/';
    }

    public function uploadMedia() {
        try {
            if (!isset($_FILES['media'])) {
                return [
                    'success' => false,
                    'message' => 'No media file provided'
                ];
            }

            $file = $_FILES['media'];

            // Validate file type (including HEIC from iOS and octet-stream images)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif', 'application/octet-stream'];
            $detectedType = $file['type'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Handle application/octet-stream files by checking extension
            if ($detectedType === 'application/octet-stream') {
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'])) {
                    // Valid image extension, allow the upload
                    $detectedType = 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);
                } else {
                    return [
                        'success' => false,
                        'message' => 'Invalid file type: ' . $detectedType . ' with extension .' . $extension . '. Only image files are allowed.'
                    ];
                }
            }
            
            if (!in_array($detectedType, $allowedTypes) && !in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type: ' . $detectedType . ' (.' . $extension . '). Only JPEG, PNG, GIF, WebP, and HEIC images are allowed.'
                ];
            }

            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File too large. Maximum size is 5MB.'
                ];
            }

            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Convert HEIC to JPEG for better compatibility
            if (in_array($extension, ['heic', 'heif'])) {
                $extension = 'jpg';
            }
            
            $filename = uniqid('media_') . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // Ensure upload directory exists and is writable
            if (!file_exists($this->uploadDir)) {
                if (!mkdir($this->uploadDir, 0755, true)) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create upload directory'
                    ];
                }
            }
            
            if (!is_writable($this->uploadDir)) {
                return [
                    'success' => false,
                    'message' => 'Upload directory is not writable'
                ];
            }

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Store media info in database if needed
                $this->storeMediaInfo($filename, $file['type'], $file['size']);

                return [
                    'success' => true,
                    'url' => $filename,
                    'message' => 'Media uploaded successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to save file to: ' . $filepath . '. Check directory permissions.'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Upload error: ' . $e->getMessage()
            ];
        }
    }

    public function uploadProfilePicture() {
        try {
            if (!isset($_FILES['profile_picture'])) {
                return [
                    'success' => false,
                    'message' => 'No profile picture provided'
                ];
            }

            $file = $_FILES['profile_picture'];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.'
                ];
            }

            // Validate file size (max 2MB for profile pictures)
            if ($file['size'] > 2 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File too large. Maximum size is 2MB.'
                ];
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_') . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => true,
                    'url' => $filename,
                    'message' => 'Profile picture uploaded successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to save file'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Upload error: ' . $e->getMessage()
            ];
        }
    }

    private function storeMediaInfo($filename, $type, $size) {
        try {
            // You can store media information in a media table if needed
            // For now, we'll just rely on the file system
            return true;
        } catch (Exception $e) {
            // Log error but don't fail the upload
            error_log('Failed to store media info: ' . $e->getMessage());
            return false;
        }
    }
}

$mediaUpload = new MediaUpload();

// Handle different actions
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'upload_media':
        $result = $mediaUpload->uploadMedia();
        break;
    case 'upload_profile_picture':
        $result = $mediaUpload->uploadProfilePicture();
        break;
    default:
        // Handle direct file upload (for backward compatibility)
        $result = $mediaUpload->uploadMedia();
}

echo json_encode($result);
?>
