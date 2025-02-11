<?php
// Initialize the session
session_start();

// Include config file
require_once "../db-connection/config.php";

// Check if the user is logged in
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
    $tags = $_POST['tags']; // Assuming tags are sent as a comma-separated string
    $author_id = $_POST['author_id'];

    // Start a transaction for safety
    $connection->beginTransaction();

    try {
        // Step 1: Update the blog post details
        $sql = "UPDATE blogs SET 
            title = :title, 
            slug = :slug, 
            category_id = :category_id, 
            status = :status,
            author_id = :author_id,
            updated_at = NOW()
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

        // Step 2: Handle tags

        // Convert tags to an array
        $newTags = is_array($tags) ? $tags : explode(',', $tags);
        $newTags = array_map('trim', $newTags); // Trim spaces

        // Get existing tags from the database
        $existingTagsQuery = "SELECT tag FROM blog_tags WHERE blog_id = :id";
        $stmt = $connection->prepare($existingTagsQuery);
        $stmt->execute([':id' => $id]);
        $existingTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Find tags to add (new tags not in existing ones)
        $tagsToAdd = array_diff($newTags, $existingTags);

        // Find tags to remove (old tags not in new ones)
        $tagsToRemove = array_diff($existingTags, $newTags);

        // Insert new tags
        if (!empty($tagsToAdd)) {
            $insertTag = "INSERT INTO blog_tags (blog_id, tag) VALUES (:blog_id, :tag)";
            $stmt = $connection->prepare($insertTag);

            foreach ($tagsToAdd as $tag) {
                $stmt->execute([
                    ':blog_id' => $id,
                    ':tag' => $tag
                ]);
            }
        }

        // Delete removed tags
        if (!empty($tagsToRemove)) {
            $deleteTag = "DELETE FROM blog_tags WHERE blog_id = :blog_id AND tag = :tag";
            $stmt = $connection->prepare($deleteTag);

            foreach ($tagsToRemove as $tag) {
                $stmt->execute([
                    ':blog_id' => $id,
                    ':tag' => $tag
                ]);
            }
        }

        // Commit the transaction
        $connection->commit();

        // Redirect after success
        header("location: " . BASE_URL . "files/manage_posts.php?message=Post updated successfully.");
        exit;

    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        $connection->rollBack();
        echo "Error updating post: " . $e->getMessage();
    }
}
?>