<?php
// Call API header
require_once '../../db-connection/cors.php';
// Include database connection config
require_once '../../db-connection/config.php';

// Define response array
$response = array();

// Check if the request method is GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    // Select all banners from the database
    $sql = "SELECT * FROM banner_photos";
    $result = $connection->query($sql);

    // Check if there are any banners
    if ($result->rowCount() > 0) {
        // Fetch banners as an associative array
        $banners = $result->fetchAll(PDO::FETCH_ASSOC);
        // Add banners to the response array
        $response["success"] = true;
        $response["message"] = "Banners fetched successfully";
        $response["banners"] = $banners;
    } else {
        // No banners found
        $response["success"] = false;
        $response["message"] = "No banners found";
    }
} else {
    // Invalid request method
    $response["success"] = false;
    $response["message"] = "Invalid request method";
}

// Convert the response array to JSON and echo it
header('Content-Type: application/json');
echo json_encode($response);

?>
