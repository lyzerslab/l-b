<?php

// Call API header
require_once '../../db-connection/cors.php';

// Connect to the database
require_once '../../db-connection/config.php';


header("Content-Type: application/json");
function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle video upload and save
    try {
        $rawData = file_get_contents("php://input");
        $body = json_decode($rawData, true);

        if (!isset($body['url'])) {
            respond(400, ["error" => "Video URL is required"]);
        }

        $videoUrl = $body['url'];
        $id = uniqid();
        $filename = $id . '.webm';
        $zipFilename = $id . '.zip';

        // Download the video from the given URL
        $videoData = file_get_contents($videoUrl);
        if ($videoData === false) {
            respond(500, ["error" => "Failed to download video"]);
        }

        // Save the video to a temporary file
        $videoPath = __DIR__ . '/uploads/' . $filename;
        file_put_contents($videoPath, $videoData);

        // Create a ZIP file containing the video
        $zipPath = __DIR__ . '/uploads/' . $zipFilename;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            respond(500, ["error" => "Failed to create ZIP file"]);
        }
        $zip->addFile($videoPath, $filename);
        $zip->close();

        // Save metadata to the database
        $db = getDatabaseConnection();
        $stmt = $db->prepare("INSERT INTO videos (id, filename, zip_filename) VALUES (?, ?, ?)");
        $stmt->execute([$id, $filename, $zipFilename]);

        // Generate a shareable link
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $shareLink = $baseUrl . '/share/' . $id;

        respond(201, ["id" => $id, "link" => $shareLink]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        respond(500, ["error" => "Server error occurred"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve video ZIP by ID
    try {
        if (!isset($_GET['id'])) {
            respond(400, ["error" => "Video ID is required"]);
        }

        $id = $_GET['id'];
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT zip_filename FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$video) {
            respond(404, ["error" => "Video not found"]);
        }

        $zipPath = __DIR__ . '/uploads/' . $video['zip_filename'];
        if (!file_exists($zipPath)) {
            respond(404, ["error" => "ZIP file not found"]);
        }

        // Serve the ZIP file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
        readfile($zipPath);
        exit();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        respond(500, ["error" => "Server error occurred"]);
    }
} else {
    respond(405, ["error" => "Method not allowed"]);
}
?>