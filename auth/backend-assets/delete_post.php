<?php
// Initialize the session
session_start();

// Include config file for database connection
require_once "../db-connection/config.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

// Check if `id` is provided in the URL
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    try {
        // Start transaction to ensure atomic operations
        $connection->beginTransaction();

        // 1️⃣ **Get the featured image path from the database**
        $getImageQuery = "SELECT featured_image FROM blogs WHERE id = :id";
        $stmt = $connection->prepare($getImageQuery);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $imageData = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2️⃣ **Delete the image file if it exists**
        if ($imageData && !empty($imageData['featured_image'])) {
            $imagePath = str_replace("https://www.dashboard.lyzerslab.com", $_SERVER['DOCUMENT_ROOT'], $imageData['featured_image']);
            
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the file
            }
        }

        // 3️⃣ **Delete tags associated with the blog**
        $deleteTagsQuery = "DELETE FROM blog_tags WHERE blog_id = :id";
        $stmt = $connection->prepare($deleteTagsQuery);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        // 4️⃣ **Delete comments associated with the blog**
        $deleteCommentsQuery = "DELETE FROM comments WHERE blog_id = :id";
        $stmt = $connection->prepare($deleteCommentsQuery);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        // 5️⃣ **Delete the blog post itself**
        $deleteBlogQuery = "DELETE FROM blogs WHERE id = :id";
        $stmt = $connection->prepare($deleteBlogQuery);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        // ✅ **Commit transaction**
        $connection->commit();

        // ✅ **Redirect with success message**
        header("location: " . BASE_URL . "files/manage_posts.php?message=Post deleted successfully.");
        exit;
    } catch (Exception $e) {
        // ❌ **Rollback transaction in case of error**
        $connection->rollBack();
        header("location: " . BASE_URL . "files/manage_posts.php?error=An error occurred: " . $e->getMessage());
        exit;
    }
} else {
    // ❌ Redirect if ID is missing
    header("location: " . BASE_URL . "files/manage_posts.php?error=Invalid request.");
    exit;
}
?>