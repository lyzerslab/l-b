<?php
// Set CORS headers
header('Access-Control-Allow-Origin: *'); // Temporarily allow all origins for testing
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../db-connection/config.php';

// Get the tag parameter
$tag = isset($_GET['tag']) ? urldecode($_GET['tag']) : '';

// Base URLs
$base_url = 'https://www.dashboard.lyzerslab.com/';
$image_path = $base_url . 'files/blog/uploads/featured-images/';
$author_photo_path = $base_url . 'files/';

try {
    // Modified SQL query to filter by tag
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
        WHERE t.tag = :tag AND b.status = 'published'
        GROUP BY b.id
        ORDER BY b.created_at DESC
    ";

    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
    $stmt->execute();
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($blogs) {
        $response = [
            'status' => 'success',
            'data' => array_map(function ($blog) use ($base_url, $author_photo_path) {
                $featured_image_url = !empty($blog['featured_image']) 
                    ? $base_url . ltrim(str_replace('/dashboard.lyzerslab.com/', '', $blog['featured_image']), '/') 
                    : $base_url . 'files/blog/uploads/featured-images/default-image.jpg';
                
                $author_photo_url = !empty($blog['author_photo']) 
                    ? $author_photo_path . $blog['author_photo'] 
                    : $author_photo_path . 'default.jpg';
                
                return [
                    'id' => (int) $blog['id'],
                    'title' => $blog['title'],
                    'slug' => str_replace(":", "-", $blog['slug']),
                    'content' => $blog['content'],
                    'status' => $blog['status'],
                    'featured_image' => $featured_image_url,
                    'created_at' => $blog['created_at'],
                    'author' => $blog['author'],
                    'author_photo' => $author_photo_url,
                    'category' => $blog['category'],
                    'tags' => $blog['tags'] ? array_filter(explode(',', $blog['tags'])) : []
                ];
            }, $blogs)
        ];
    } else {
        $response = [
            'status' => 'success',
            'data' => []
        ];
    }

} catch (PDOException $e) {
    $response = [
        'status' => 'error',
        'message' => 'Database query failed: ' . $e->getMessage()
    ];
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>