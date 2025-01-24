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
               u.username AS author,  
               c.name AS category, 
               IFNULL(GROUP_CONCAT(DISTINCT bt.tag ORDER BY bt.tag ASC), '') AS tags  -- Use IFNULL to return an empty string if no tags
        FROM blogs b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN blog_tags bt ON b.id = bt.blog_id
        LEFT JOIN admin_users u ON b.author_id = u.id  
        GROUP BY b.id
        ORDER BY b.created_at DESC";

$stmt = $connection->prepare($sql);

try {
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Output the raw SQL results
    var_dump($posts);  // Uncomment to view the raw result of the query

    // If posts are found, add them to the response array
    if ($posts) {
        // Check and sanitize the 'tags' field to ensure no hidden issues
        foreach ($posts as &$post) {
            $post['tags'] = trim($post['tags']); // Remove any extra spaces or hidden characters
        }

        // Prepare final response
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

// Return the response as JSON with proper UTF-8 encoding
echo json_encode($blogs, JSON_UNESCAPED_UNICODE);
?>