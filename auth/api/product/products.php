<?php
// Call API header
require_once '../../db-connection/cors.php';

// Connect to the database
require_once '../../db-connection/config.php';

try {
    $query = "SELECT * FROM products";
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Unable to fetch products. " . $e->getMessage()));
}
?>
