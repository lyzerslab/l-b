<?php

// Call API header
require_once '../db-connection/cors.php';

// Connect to the database
require_once '../db-connection/config.php';

// Get user ID from the request parameters
$userID = isset($_GET['userId']) ? $_GET['userId'] : null;

if ($userID !== null) {
    // Get the user's photo file path from the database
    $selectQuery = "SELECT * FROM customers WHERE id = ?";
    $stmt = $connection->prepare($selectQuery);
    $stmt->bindParam(1, $userID);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['photo'])) {
        // Return the relative photo path as JSON
        $photoPath = 'assets/user-profile/' . $result['username'] . '/' . basename($result['photo']);
        $response = ['status' => 'success', 'filePath' => $photoPath];
        echo json_encode($response);
    } else {
        $response = ['status' => 'error', 'message' => 'User photo not found.'];
        echo json_encode($response);
    }
} else {
    $response = ['status' => 'error', 'message' => 'Missing user ID.'];
    echo json_encode($response);
}

?>
