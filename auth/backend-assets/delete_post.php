<?php
// Initialize the session
session_start();

// Include config file for base URL
require_once "../db-connection/config.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

// Check if the `id` parameter exists in the URL
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    try {
        // Start a transaction to ensure atomic operations
        $connection->beginTransaction();

        // Delete tags associated with the blog
        $deleteTagsQuery = "DELETE FROM blog_tags WHERE blog_id = :id";
        $stmt = $connection->prepare($deleteTagsQuery);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        // Delete comments associated with the blog
        $deleteCommentsQuery = "DELETE FROM comments WHERE blog_id = :id";
        $stmt = $connection->prepare($deleteCommentsQuery);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        // Delete the blog post
        $deleteBlogQuery = "DELETE FROM blogs WHERE id = :id";
        $stmt = $connection->prepare($deleteBlogQuery);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        // Commit the transaction
        $connection->commit();

        // Redirect to the blog management page with a success message
        header("location: " . BASE_URL . "files/manage_posts.php?message=Post deleted successfully.");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $connection->rollBack();
        header("location: " . BASE_URL . "files/manage_posts.php?error=An error occurred: " . $e->getMessage());
        exit;
    }
} else {
    // Redirect if the ID is not provided
    header("location: " . BASE_URL . "files/manage_posts.php?error=Invalid request.");
    exit;
}
?>