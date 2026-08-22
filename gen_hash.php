<?php
$hash = password_hash('password123', PASSWORD_BCRYPT);
echo "Hash for password123: " . $hash . "\n";
$hash2 = password_hash('password', PASSWORD_BCRYPT);
echo "Hash for password: " . $hash2 . "\n";

var_dump(password_verify('password', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'));
?>
