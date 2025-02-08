<?php

require_once '../../db-connection/cors.php';
require_once '../../db-connection/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Base URLs
$base_url = 'https://www.dashboard.lyzerslab.com/';
$image_path = $base_url . 'files/blog/uploads/featured-images/';
$author_photo_path = $base_url . 'files/';

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
            b.featured_image, 
            b.created_at, 
            u.username AS author,
            u.profile_photo AS author_photo, 
            c.name AS category, 
            IFNULL(GROUP_CONCAT(DISTINCT t.tag ORDER BY t.tag ASC), '') AS tags
        FROM blogs b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN blog_tags t ON b.id = t.blog_id
        LEFT JOIN admin_users u ON b.author_id = u.id
        GROUP BY b.id
        ORDER BY b.created_at DESC;
    ";

    $stmt = $connection->prepare($sql);
    $stmt->execute();
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($blogs) {
        $response['status'] = 'success';

        // Process blogs data
        $response['data'] = array_map(function ($blog) use ($image_path, $author_photo_path) {
            // Construct full image URLs
            $featured_image_url = !empty($blog['featured_image']) ? $image_path . basename($blog['featured_image']) : $image_path . 'default-image.jpg';
            $author_photo_url = !empty($blog['author_photo']) ? $author_photo_path . $blog['author_photo'] : $author_photo_path . 'default.jpg';

            return [
                'id' => (int) $blog['id'],
                'title' => $blog['title'],
                'slug' => str_replace(":", "-", $blog['slug']), // Replace `:` with `-`
                'content' => $blog['content'],
                'status' => $blog['status'],
                'featured_image' => $featured_image_url,
                'created_at' => $blog['created_at'],
                'author' => $blog['author'],
                'author_photo' => $author_photo_url,
                'category' => $blog['category'],
                'tags' => $blog['tags'] ? array_filter(explode(',', $blog['tags'])) : []  // Convert tags into an array
            ];
        }, $blogs);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No blogs found.';
    }
} catch (PDOException $e) {
    $response['status'] = 'error';
    $response['message'] = 'Database query failed: ' . $e->getMessage();
}

// Output response in JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>