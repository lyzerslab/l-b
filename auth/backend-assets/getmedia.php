<?php
// Include the database configuration file
require_once "../db-connection/config.php";

header('Content-Type: application/json');

try {
    // Get the database connection
    $pdo = getDatabaseConnection();

    // Query to fetch all media files
    $stmt = $pdo->query("SELECT file_name, file_path, file_type FROM media ORDER BY id DESC");

    // Fetch all results as an associative array
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the files as JSON
    echo json_encode($files);
} catch (PDOException $e) {
    // Return an error response if the query fails
    echo json_encode(['error' => 'Failed to fetch media files: ' . $e->getMessage()]);
}
?>