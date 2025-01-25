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

    // Debugging: Log the SQL query
    error_log("Executing SQL query: " . $sql);

    $stmt = $connection->prepare($sql);
    $stmt->execute();

    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Log the raw query result to check if the data is fetched correctly
    error_log("Fetched blogs: " . print_r($blogs, true));

    if ($blogs) {
        // Format and send response
        $response['status'] = 'success';
        
        // Log the fetched data before processing
        error_log("Fetched blogs data: " . json_encode($blogs));

        // Process the blogs and tags
        $response['data'] = array_map(function ($blog) {
            // Construct full URL for the featured image
            $base_url = 'https://www.dashboard.lyzerslab.com/'; // Replace with your domain or path
            $featured_image_url = $base_url . 'files/' . $blog['featured_image'];  // Replace with your actual image folder

            // Clean up the slug to replace `:` with `-` if needed, and avoid double hyphen
            $cleanSlug = str_replace(":", "-", $blog['slug']);  // Replace colon with hyphen
            $cleanSlug = preg_replace('/-+/', '-', $cleanSlug); // Remove any consecutive hyphens

             // Use the author's profile photo from the database if available
             $author_photo_base_path = $base_url . 'files/';  // Path to the author photo folder
             $author_photo = $blog['author_photo'] ? $author_photo_base_path . $blog['author_photo'] : $author_photo_base_path . 'https://lyzerslab.com/_next/image?url=https%3A%2F%2Fwww.dashboard.lyzerslab.com%2Ffiles%2Fblog%2Fimage.avif&w=3840&q=75'; // Fallback to default if not set

            // Return cleaned-up blog data with full image URL
            return [
                'id' => (int) $blog['id'],
                'title' => $blog['title'],
                'slug' => $cleanSlug,  // Cleaned slug with single hyphen
                'content' => $blog['content'],
                'status' => $blog['status'],
                'featured_image' => $featured_image_url,  // Full image URL
                'created_at' => $blog['created_at'],
                'author' => $blog['author'],
                'author_photo' => $author_photo,
                'category' => $blog['category'],
                'tags' => $blog['tags'] ? array_filter(explode(',', $blog['tags'])) : []  // Convert tags into an array, or empty if none
            ];
        }, $blogs);

        // Debugging: Log the final data being sent in the response
        error_log("Final response data: " . json_encode($response['data']));
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