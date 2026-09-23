<?php
// Simple image serving script
$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    http_response_code(400);
    echo 'No file specified';
    exit;
}

// Sanitize filename to prevent directory traversal
$filename = basename($filename);
$filepath = __DIR__ . '/uploads/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Get file info
$fileInfo = pathinfo($filepath);
$extension = strtolower($fileInfo['extension']);

// Set appropriate content type
switch ($extension) {
    case 'jpg':
    case 'jpeg':
        $contentType = 'image/jpeg';
        break;
    case 'png':
        $contentType = 'image/png';
        break;
    case 'gif':
        $contentType = 'image/gif';
        break;
    case 'webp':
        $contentType = 'image/webp';
        break;
    default:
        http_response_code(400);
        echo 'Unsupported file type';
        exit;
}

// Set headers
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Output file
readfile($filepath);
?>
