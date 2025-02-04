<?php
require_once '../../db-connection/cors.php';
require_once '../../db-connection/config.php';

header("Content-Type: application/json");
function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}
$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);  // Ensure the uploads directory exists
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if the video file is set in the request
        if (!isset($_FILES['video'])) {
            respond(400, ["error" => "Video file is required"]);
        }
        // Get the video file
        $video = $_FILES['video'];
        // Check if the file is valid (no error)
        if ($video['error'] !== UPLOAD_ERR_OK) {
            respond(400, ["error" => "Failed to upload video"]);
        }
        // Validate file extension (check if it's a .webm file)
        $fileExtension = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'webm') {
            respond(400, ["error" => "Only WebM videos are allowed"]);
        }
        // Generate a unique ID for the video
        $id = bin2hex(random_bytes(16)); // More secure random ID
        $filename = $id . '.webm';
        $zipFilename = $id . '.zip';
        // Set the file path to save the video temporarily
        $videoPath = $uploadsDir . $filename;
        // Move the uploaded video to a temporary location
        if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
            respond(500, ["error" => "Failed to save video file temporarily"]);
        }
        // Create a ZIP file
        $zipPath = $uploadsDir . $zipFilename;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            respond(500, ["error" => "Failed to create ZIP file"]);
        }
        $zip->addFile($videoPath, $filename);
        $zip->close();
        // After creating the ZIP, remove the original .webm file to save space
        unlink($videoPath);
        // Save video data to the database with only the ZIP file reference
        $db = getDatabaseConnection();
        $stmt = $db->prepare("INSERT INTO videos (id, filename, zip_filename) VALUES (?, ?, ?)");
        $stmt->execute([$id, $filename, $zipFilename]);
        // Generate the shareable link
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $shareLink = $baseUrl . '/share/' . $id;
        respond(201, ["id" => $id, "link" => $shareLink]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        respond(500, ["error" => "Server error occurred"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
        // Generate the URL to the ZIP file (if it's stored under the 'uploads' folder)
        $zipUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/auth/api/videos/uploads/' . $video['zip_filename'];
        // Respond with the metadata (including the video URL)
        respond(200, ["id" => $id, "zip_url" => $zipUrl]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        respond(500, ["error" => "Server error occurred"]);
    }
} else {
    respond(405, ["error" => "Method not allowed"]);
}
?>