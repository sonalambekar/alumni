<?php
session_start();
require_once __DIR__ . '/includes/db_config.php';

// Clear session
session_unset();
session_destroy();

// Redirect to login page
header("Location: /alumni/login.php");
exit();
?>
