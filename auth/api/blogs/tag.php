<?php

// Allow requests only from your specific domain
header('Access-Control-Allow-Origin: https://lyzerslab.com/');

// Allow the following HTTP methods
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

// Allow the following headers
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

// Allow cookies to be sent with the request (useful for sessions or authentication tokens)
header('Access-Control-Allow-Credentials: true');

// Set the response content type to JSON
header('Content-Type: application/json; charset=UTF-8');

require_once '../../db-connection/config.php';

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

    if ($blogs) {  // ✅ Check if blogs exist
        $response['status'] = 'success';

        $response['data'] = array_map(function ($blog) use ($base_url, $author_photo_path) {
            if (!empty($blog['featured_image'])) {
                // Remove leading slash and incorrect domain part if present
                $cleaned_path = str_replace('/dashboard.lyzerslab.com/', '', $blog['featured_image']);
        
                // Ensure full URL with base path
                $featured_image_url = $base_url . ltrim($cleaned_path, '/');
            } else {
                $featured_image_url = $base_url . 'files/blog/uploads/featured-images/default-image.jpg';
            }
        
            // Construct author photo URL
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