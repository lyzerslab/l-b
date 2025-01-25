<?php
require_once '../../../db-connection/cors.php';
require_once '../../../db-connection/config.php';

// Set content type to JSON
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    // Fetch comments for a specific blog post
    if (!isset($_GET['blog_id'])) {
        echo json_encode(["error" => "Blog ID is required"]);
        http_response_code(400);
        exit;
    }

    $blog_id = $_GET['blog_id'];

    try {
        $query = $connection->prepare("SELECT id, name, email, content, created_at FROM comments WHERE blog_id = ? AND status = 'approved' ORDER BY created_at DESC");
        $query->execute([$blog_id]);

        // Check if we have any results
        $comments = $query->fetchAll(PDO::FETCH_ASSOC);
        if (empty($comments)) {
            echo json_encode(["message" => "No approved comments found for this blog."]);
            http_response_code(200);  // Status 200 even if no comments exist
        } else {
            echo json_encode(["comments" => $comments]);
            http_response_code(200);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        http_response_code(500);
    }
} elseif ($method === "POST") {
    // Add a new comment
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['blog_id'], $input['name'], $input['email'], $input['content'])) {
        echo json_encode(["error" => "All fields (blog_id, name, email, content) are required"]);
        http_response_code(400);
        exit;
    }

    $blog_id = $input['blog_id'];
    $name = $input['name'];
    $email = $input['email'];
    $content = $input['content'];
    $created_at = date("Y-m-d H:i:s");
    $status = "pending"; // Default status

    try {
        $query = $connection->prepare("INSERT INTO comments (blog_id, name, email, content, created_at, status) VALUES (?, ?, ?, ?, ?, ?)");
        $query->execute([$blog_id, $name, $email, $content, $created_at, $status]);

        echo json_encode(["message" => "Comment added successfully", "comment" => [
            "id" => $connection->lastInsertId(),
            "blog_id" => $blog_id,
            "name" => $name,
            "email" => $email,
            "content" => $content,
            "created_at" => $created_at,
            "status" => $status
        ]]);
        http_response_code(201);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        http_response_code(500);
    }
} else {
    // Invalid method
    echo json_encode(["error" => "Invalid request method"]);
    http_response_code(405);
}
?>