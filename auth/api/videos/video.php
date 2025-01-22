<?php

require_once '../../db-connection/cors.php';
require_once '../../db-connection/config.php';

header("Content-Type: application/json");

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function downloadVideo($url, $savePath) {
    $ch = curl_init($url);
    $fp = fopen($savePath, 'w+');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    if ($error) {
        throw new Exception("Curl error: $error");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        // Download the video
        $videoPath = __DIR__ . '/uploads/' . $filename;
        downloadVideo($videoUrl, $videoPath);

        // Create a ZIP file
        $zipPath = __DIR__ . '/uploads/' . $zipFilename;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            respond(500, ["error" => "Failed to create ZIP file"]);
        }
        $zip->addFile($videoPath, $filename);
        $zip->close();

        // Save to database
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