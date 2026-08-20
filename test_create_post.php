<?php
// Test creating a post without authentication

$url = 'http://172.21.81.215:90/alumni/api/posts/create.php';
$data = [
    'user_id' => 1,
    'content' => 'Test post created at ' . date('Y-m-d H:i:s'),
    'media_type' => 'none'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>Test Create Post (No Auth)</h2>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . print_r(json_decode($response, true), true) . "</pre>";

if ($httpCode == 200) {
    echo "<p style='color: green;'>✅ Post created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to create post</p>";
}

echo "<p><a href='api/posts/list.php?user_id=1'>View Posts</a></p>";
?>
