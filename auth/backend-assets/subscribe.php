<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Initialize the session
session_start();
 
// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

// Allow requests from specific origin (replace with your actual domain)
header("Access-Control-Allow-Origin: https://lyzerslab.com"); // Update with your allowed domain
// Allow the use of certain methods
header("Access-Control-Allow-Methods: POST");
// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type");

// Database connection credentials
require_once "../db-connection/config.php";

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data
    $post_data = file_get_contents("php://input");
    // Decode the JSON data into an associative array
    $data = json_decode($post_data, true);

    // Check if 'email' field is set and validate email format
    if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $email = $data['email'];

        try {
            // Check if the email already exists in the database
            $stmt_check = $connection->prepare("SELECT COUNT(*) FROM subscribers WHERE email = ?");
            $stmt_check->bindValue(1, $email); // Bind the parameter
            $stmt_check->execute();
            $count = $stmt_check->fetchColumn();
            $stmt_check->closeCursor();

            if ($count > 0) {
                // If email already exists, return an error
                $response = array("success" => false, "error" => "Email address already subscribed");
                echo json_encode($response);
            } else {
                // Prepare a parameterized SQL statement (prevents SQL injection)
                $stmt_insert = $connection->prepare("INSERT INTO subscribers (email) VALUES (?)");
                $stmt_insert->bindValue(1, $email); // Bind the parameter

                // Execute the statement
                if ($stmt_insert->execute()) {
                    $response = array("success" => true, "message" => "Email subscribed successfully!");
                    echo json_encode($response);
                } else {
                    $response = array("success" => false, "error" => "Error inserting data: " . $stmt_insert->errorInfo()[2]); // Use errorInfo for details
                    echo json_encode($response);
                }
            }
        } catch (PDOException $e) {
            // Catch PDO exceptions
            $response = array("success" => false, "error" => "Internal server error: " . $e->getMessage());
            echo json_encode($response);
        } finally {
            // Close the prepared statement (if created)
            if (isset($stmt_insert)) {
                $stmt_insert->closeCursor(); // Use closeCursor instead of close
            }
        }
    } else {
        // If 'email' field is missing or invalid, return an error
        $response = array("success" => false, "error" => "'email' field is required and must be a valid email address");
        echo json_encode($response);
    }

    // Close connection
    $connection = null; // Close the database connection
} else {
    // If the request method is not POST, return an error
    $response = array("success" => false, "error" => "Only POST requests are allowed");
    echo json_encode($response);
}
?>
