<?php
// Set CORS headers
header('Access-Control-Allow-Origin: https://lyzerslab.com'); 
header('Access-Control-Allow-Methods: POST'); // Only allow POST as that's all we need
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No content needed for preflight
    exit();
}

require_once '../../db-connection/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON payload');
        }

        if (empty($data['slug'])) {
            throw new Exception('Blog slug is required');
        }

        $slug = filter_var($data['slug'], FILTER_SANITIZE_STRING);
        $db = getDatabaseConnection();

        // First check if blog exists
        $checkStmt = $db->prepare("SELECT id FROM blogs WHERE slug = ? LIMIT 1");
        $checkStmt->execute([$slug]);
        
        if (!$checkStmt->fetch()) {
            throw new Exception('Blog not found');
        }

        // Update the view count
        $updateStmt = $db->prepare("UPDATE blogs SET views = views + 1 WHERE slug = ?");
        $success = $updateStmt->execute([$slug]);

        if (!$success) {
            throw new Exception('Failed to update view count');
        }

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "View count updated successfully"
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Method not allowed"
    ]);
}