<?php
// Initialize the session
session_start();

// Include config file
require_once "../db-connection/config.php";

// Check if the user is logged in, if not then redirect him to the login page
// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $slug = $_POST['slug'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];
    $tags = $_POST['tags'];
    $author_id = $_POST['author_id'];

    // Prepare the update SQL query
    $sql = "UPDATE blogs SET 
        title = :title, 
        slug = :slug, 
        category_id = :category_id, 
        status = :status,
        author_id = :author_id 
        WHERE id = :id";

    $stmt = $connection->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':title' => $title,
        ':slug' => $slug,
        ':category_id' => $category_id,
        ':status' => $status,
        ':author_id' => $author_id
    ]);

    // Optional: Update the tags if necessary
    // You can handle tags here if needed (you may need to add or remove tags from a separate table)

    echo "Post updated successfully!";
    // Redirect back to the posts list or show success message
    header("location: " . BASE_URL . "files/manage_posts.php?message=Post deleted successfully.");
    exit;
}
?>