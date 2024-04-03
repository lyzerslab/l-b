<?php
// Call API header
require_once '../db-connection/cors.php';
// Start the session
session_start();
// Unset session variables if they are set
if (isset($_SESSION['username'])) {
    unset($_SESSION['username']);
}
if (isset($_SESSION['loggedIn'])) {
    unset($_SESSION['loggedIn']);
}

// Destroy the session if it exists
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Output JSON response
echo json_encode(['status' => 'success', 'redirect' => '/']);
header('Location: /'); // Redirect to root
exit();

?>
