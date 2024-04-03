<?php
// Call API header
require_once '../../db-connection/cors.php';

// Connect to the database
require_once '../../db-connection/config.php';

function fetchCategories($parentCategoryId = null) {
    global $connection;

    $query = "SELECT id, name, parent_category_id, category_description, created_at, updated_at, level, SUBSTRING_INDEX(category_photo, '/', -1) AS photo_name
              FROM categories
              WHERE parent_category_id " . ($parentCategoryId ? "= :parentCategoryId" : "IS NULL");

    $stmt = $connection->prepare($query);

    if ($parentCategoryId) {
        $stmt->bindParam(':parentCategoryId', $parentCategoryId, PDO::PARAM_INT);
    }

    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as &$category) {
        $category['product_count'] = fetchProductCount($category['id']);
        $category['subcategories'] = fetchCategories($category['id']);
    }

    return $categories;
}

function fetchProductCount($categoryId) {
    global $connection;

    $query = "SELECT COUNT(*) FROM products WHERE category_id = :categoryId";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
    $stmt->execute();

    return (int)$stmt->fetchColumn();
}

try {
    $categories = fetchCategories();

    header('Content-Type: application/json');
    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Unable to fetch categories. " . $e->getMessage()));
}
?>
