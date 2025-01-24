<?php

require_once '../../db-connection/cors.php';
require_once '../../db-connection/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize an empty array for blog posts
$blogs = array();

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method. Only GET requests are allowed.'
    ]);
    exit;
}

// Fetch blogs from the database
$sql = "SELECT b.id, b.title, b.slug, b.content, b.status, b.created_at, 
               u.username AS author,  -- Join admin_users table to get the author's username
               c.name as category, 
               GROUP_CONCAT(bt.tag) AS tags
        FROM blogs b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN blog_tags bt ON b.id = bt.blog_id
        LEFT JOIN admin_users u ON b.author = u.id  -- Assuming 'author' is the ID of the user in the blogs table
        GROUP BY b.id
        ORDER BY b.created_at DESC";

$stmt = $connection->prepare($sql);

try {
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If posts are found, add them to the response array
    if ($posts) {
        $blogs['status'] = 'success';
        $blogs['data'] = $posts;
    } else {
        $blogs['status'] = 'error';
        $blogs['message'] = 'No blogs found.';
    }
} catch (PDOException $e) {
    // Catch any errors during the query execution
    $blogs['status'] = 'error';
    $blogs['message'] = 'Database query failed: ' . $e->getMessage();
}

// Return the response as JSON
echo json_encode($blogs);
?>