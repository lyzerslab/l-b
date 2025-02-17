<?php
// Set CORS headers
header('Access-Control-Allow-Origin: https://lyzerslab.com'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // ✅ Now allows POST
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../db-connection/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') { // ✅ Allow both
    error_log("Received request method: " . $_SERVER['REQUEST_METHOD']); // Debugging

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['slug'])) {
        echo json_encode(["error" => "Blog slug is required"]);
        exit();
    }

    $slug = $data['slug'];
    $db = getDatabaseConnection();

    // Update the view count
    $stmt = $db->prepare("UPDATE blogs SET views = views + 1 WHERE slug = ?");
    $stmt->execute([$slug]);

    echo json_encode(["message" => "View count updated"]);
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>