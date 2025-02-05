<?php
// Allow requests from any origin (change * to your domain for security)
header('Access-Control-Allow-Origin: https://lyzerslab.com/');

// Allow specific HTTP methods
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

// Allow specific headers
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

// Allow credentials (if using authentication tokens or cookies)
header('Access-Control-Allow-Credentials: true');

// Set response content type
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight (OPTIONS) request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../db-connection/config.php';

// Function to send JSON response
function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

// Define the upload directory
$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true); // Create the directory if it doesn't exist
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ensure a file was uploaded
        if (!isset($_FILES['video'])) {
            respond(400, ["error" => "Video file is required"]);
        }

        $video = $_FILES['video'];

        // Ensure there was no error during upload
        if ($video['error'] !== UPLOAD_ERR_OK) {
            respond(400, ["error" => "Failed to upload video"]);
        }

        // Validate file extension
        $fileExtension = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'webm') {
            respond(400, ["error" => "Only WebM videos are allowed"]);
        }

        // Generate a unique ID for the video
        $id = bin2hex(random_bytes(16));
        $filename = $id . '.webm';
        $zipFilename = $id . '.zip';

        // Set the file path to save the video
        $videoPath = $uploadsDir . $filename;

        // Move the uploaded video to the upload directory
        if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
            respond(500, ["error" => "Failed to save video file"]);
        }

        // Create a ZIP file
        $zipPath = $uploadsDir . $zipFilename;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            respond(500, ["error" => "Failed to create ZIP file"]);
        }
        $zip->addFile($videoPath, $filename);
        $zip->close();

        // Remove the original .webm file to save space
        unlink($videoPath);

        // Save video data to the database with the ZIP file reference
        $db = getDatabaseConnection();
        $stmt = $db->prepare("INSERT INTO videos (id, filename, zip_filename) VALUES (?, ?, ?)");
        $stmt->execute([$id, $filename, $zipFilename]);

        // ✅ Fixed: Proper HTTP/HTTPS detection
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
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

        // Generate the URL for the ZIP file
        $zipUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/auth/api/videos/uploads/' . $video['zip_filename'];

        respond(200, ["id" => $id, "zip_url" => $zipUrl]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        respond(500, ["error" => "Server error occurred"]);
    }
} else {
    respond(405, ["error" => "Method not allowed"]);
}
?>