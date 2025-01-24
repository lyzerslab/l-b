<?php

require_once '../../db-connection/cors.php';
require_once '../../db-connection/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize an empty array for blog posts
$blogs = array();

// Fetch blogs from the database
$sql = "SELECT b.id, b.title, b.slug, b.content, b.status, b.created_at, c.name as category, GROUP_CONCAT(bt.tag) AS tags, b.featured_image, b.author
        FROM blogs b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN blog_tags bt ON b.id = bt.blog_id
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