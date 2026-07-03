<?php
// inc/header.php - Global Navigation Component with Session-Safe Initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if a user is actively authenticated to toggle view state layouts
$is_logged_in = isset($_SESSION['user_id']);
?>
