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
            IFNULL(GROUP_CONCAT(DISTINCT t.tag ORDER BY t.tag ASC), '') AS tags
        FROM blogs b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN blog_tags t ON b.id = t.blog_id
        LEFT JOIN admin_users u ON b.author_id = u.id
        GROUP BY b.id
        ORDER BY b.created_at DESC;
    ";

    // Debugging: Log the SQL query
    error_log("Executing SQL query: " . $sql);

    $stmt = $connection->prepare($sql);
    $stmt->execute();

    // Fetch results
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Log the raw query result to check if the data is fetched correctly
    error_log("Fetched blogs: " . print_r($blogs, true));

    if ($blogs) {
        // Format and send response
        $response['status'] = 'success';
        
        // Process the blogs and tags
        $response['data'] = array_map(function ($blog) {
            // Debugging: Log the current blog being processed
            error_log("Processing blog: " . print_r($blog, true));

            // Clean up the slug to replace `:` with `-` if needed, and avoid double hyphen
            $cleanSlug = str_replace(":", "-", $blog['slug']);  // Replace colon with hyphen
            $cleanSlug = preg_replace('/-+/', '-', $cleanSlug); // Remove any consecutive hyphens

            return [
                'id' => (int) $blog['id'],
                'title' => $blog['title'],
                'slug' => $cleanSlug,  // Cleaned slug with single hyphen
                'content' => $blog['content'],
                'status' => $blog['status'],
                'created_at' => $blog['created_at'],
                'author' => $blog['author'],
                'category' => $blog['category'],
                'tags' => $blog['tags'] ? array_filter(explode(',', $blog['tags'])) : []  // Convert tags into an array, or empty if none
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
    // Debugging: Log the exception
    error_log("PDOException: " . $e->getMessage());
}

// Debugging: Output the final response to ensure it’s correct
error_log("Final response: " . json_encode($response));

// Output response in JSON
echo json_encode($response);

?>