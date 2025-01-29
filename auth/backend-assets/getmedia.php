<?php
// Include the database configuration file
require_once "../db-connection/config.php";

// Initialize the session
session_start();

// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

header('Content-Type: application/json');

try {
    // Get the database connection
    $pdo = getDatabaseConnection();

    // Query to fetch all media files from the media table
    $media_query = "SELECT file_name, file_path, file_type FROM media ORDER BY id DESC";
    $media_stmt = $pdo->query($media_query);
    $media_files = $media_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query to fetch all featured images from the blogs table
    $blogs_query = "SELECT featured_image FROM blogs WHERE featured_image IS NOT NULL ORDER BY id DESC";
    $blogs_stmt = $pdo->query($blogs_query);
    $featured_images = $blogs_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine both results
    $files = [
        'media_files' => $media_files,
        'featured_images' => $featured_images
    ];

    // Return the combined data as JSON
    echo json_encode($files);
} catch (PDOException $e) {
    // Return an error response if the query fails
    echo json_encode(['error' => 'Failed to fetch media files and featured images: ' . $e->getMessage()]);
}
?>