<?php

require_once '../../db-connection/cors.php';
require_once '../../db-connection/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'status' => '',
    'data' => []
];

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method. Only GET requests are allowed.'
    ]);
    exit;
}

try {
    // SQL query to fetch blogs with related tags, authors, and categories
    $sql = "
        SELECT 
            b.id, 
            b.title, 
            b.slug, 
            b.content, 
            b.status, 
            b.created_at, 
            u.username AS author, 
            c.name AS category, 
            IFNULL(GROUP_CONCAT(DISTINCT bt.tag ORDER BY bt.tag ASC), '') AS tags
        FROM blogs b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN blog_tags bt ON b.id = bt.blog_id
        LEFT JOIN admin_users u ON b.author_id = u.id
        GROUP BY b.id
        ORDER BY b.created_at DESC;
    ";

    $stmt = $connection->prepare($sql);
    $stmt->execute();

    // Fetch results
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($blogs) {
        // Format and send response
        $response['status'] = 'success';
        $response['data'] = array_map(function ($blog) {
            return [
                'id' => (int) $blog['id'],
                'title' => $blog['title'],
                'slug' => $blog['slug'],
                'content' => $blog['content'],
                'status' => $blog['status'],
                'created_at' => $blog['created_at'],
                'author' => $blog['author'],
                'category' => $blog['category'],
                'tags' => array_filter(explode(',', $blog['tags'])) // Convert tags into an array
            ];
        }, $blogs);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No blogs found.';
    }
} catch (PDOException $e) {
    // Handle database errors
    $response['status'] = 'error';
    $response['message'] = 'Database query failed: ' . $e->getMessage();
}

// Output response in JSON
echo json_encode($response);

?>