<?php
session_start();

// Define the root folder name to ensure the redirect finds index.php
$root = "/concert_ticketing_system/"; 

// 1. Clear all session variables
$_SESSION = array();

// 2. Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Redirect to index.php using the absolute path
header("Location: " . $root . "index.php");
exit;
?>