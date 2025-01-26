<?php
// Initialize the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../../index.php");
    exit;
}

// Include config file
require_once "../db-connection/config.php";

// Check if the `id` parameter exists in the URL
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    // Prepare the delete query
    $query = "DELETE FROM blogs WHERE id = :id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':id', $postId, PDO::PARAM_INT);

    try {
        if ($stmt->execute()) {
            // Redirect to the blog management page with a success message
            header("location: ../../files/manage_posts.php?message=Post deleted successfully.");
            exit;
        } else {
            // Redirect with an error message
            header("location: ../../files/manage_posts.php?error=Failed to delete the post.");
            exit;
        }
    } catch (Exception $e) {
        // Handle any exceptions
        header("location: ../../files/manage_posts.php?error=An error occurred: " . $e->getMessage());
        exit;
    }
} else {
    // Redirect if the ID is not provided
    header("location: ../../files/manage_posts.php?error=Invalid request.");
    exit;
}
?>