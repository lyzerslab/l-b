<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once "../db-connection/config.php";

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    header("HTTP/1.1 200 OK");
    exit();
}

session_start();

// Add debug statement for CORS headers
error_log("CORS Headers: " . json_encode(getallheaders()));

// Check if the user is authenticated
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $sql = "SELECT * FROM users WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $userData = mysqli_fetch_assoc($result);

            header('Content-Type: application/json');
            echo json_encode($userData);
            exit(); // Don't forget to exit after sending the response
        }
        mysqli_stmt_close($stmt);
    }
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized - User not authenticated"]);
    exit();
}

mysqli_close($link);
?>
