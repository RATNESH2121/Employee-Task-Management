<?php
session_start();

// Destroy session and clear all session data
session_unset();
session_destroy();

// Make sure all session data is cleared
$_SESSION = array();

// If using session cookie, remove it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redirect to login page with proper headers
header("Location: index.php");
exit();
