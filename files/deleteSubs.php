<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();


// Include config file
require_once "../auth/db-connection/config.php";

// Check if the user is logged in
// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

// Check if the subscriber ID is provided
if (isset($_GET['id'])) {
    // Get the subscriber ID
    $subscriberId = $_GET['id'];

    try {
        // Prepare the SQL statement
        $stmt = $connection->prepare("DELETE FROM subscribers WHERE id = :id");

        // Bind the parameter
        $stmt->bindParam(':id', $subscriberId, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Redirect back to the previous page after deletion
        header('Location: subscriber.php?message="Successfully Deleted."');
        exit();
    } catch (PDOException $e) {
        // If an error occurs during deletion, redirect with an error message
        header('Location: subscriber.php?message="Failed to delete record."');
        exit();
    }
} else {
    // If the subscriber ID is not provided, redirect with an error message
    header('Location: subscriber.php?message="Subscriber ID is not provided."');
    exit();
}
?>
