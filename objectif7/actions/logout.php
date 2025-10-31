<?php
/**
 * Logout Page
 * Destroys session and redirects to login page
 */

// Start session
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Regenerate session ID (security)
session_regenerate_id(true);

// Redirect to login page
header("Location: ../login.php");
exit;

/**
 * LOGIC:
 * 1. Start session (need this to destroy it)
 * 2. Clear all session variables
 * 3. Destroy session file on server
 * 4. Regenerate ID (prevent session fixation)
 * 5. Redirect to login
 * 
 * SECURITY:
 * - Completely removes all session data
 * - Regenerates session ID
 * - Forces new login
 * 
 * PHP SYNTAX:
 * $_SESSION = [] - Clear all session variables (empty array)
 * session_destroy() - Delete session file on server
 */
?>