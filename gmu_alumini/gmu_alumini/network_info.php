<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Get server network information
$serverInfo = [
    'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'timestamp' => date('Y-m-d H:i:s'),
];

// Get all possible IP addresses
$possibleIPs = [];

// Try to get local IP addresses
if (function_exists('gethostbyname')) {
    $possibleIPs['hostname'] = gethostbyname(gethostname());
}

// Get network interfaces (if available)
if (function_exists('exec')) {
    $output = [];
    exec('hostname -I 2>/dev/null', $output);
    if (!empty($output[0])) {
        $possibleIPs['all_ips'] = trim($output[0]);
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Network information retrieved',
    'server_info' => $serverInfo,
    'possible_ips' => $possibleIPs,
    'suggested_urls' => [
        'current' => "http://{$_SERVER['HTTP_HOST']}/gmu_alumini/api",
        'localhost' => "http://localhost/gmu_alumini/api",
        'ip_based' => "http://{$_SERVER['SERVER_ADDR']}/gmu_alumini/api"
    ]
], JSON_PRETTY_PRINT);
?>
