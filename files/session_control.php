<?php
// Session timeout configuration (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Only apply timeout logic for authenticated users
if (isset($_SESSION['user_id'])) {
    // Check for inactivity timeout
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        session_unset();     
        session_destroy();
        header("Location: login.php");
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();

    // Regenerate session ID periodically for security
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > SESSION_TIMEOUT) {
        session_regenerate_id(true);    
        $_SESSION['CREATED'] = time(); 
    }
}
