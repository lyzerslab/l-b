<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    // Check if all required fields are set
    if (isset($data['name'], $data['email'], $data['phone'], $data['service'], $data['message'])) {
        // Validate email format
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'];
            $service = $data['service'];
            $message = $data['message'];
            $web_package = $data['packageOption'];
            

            try {
                // Prepare a parameterized SQL statement (prevents SQL injection)
                $stmt_insert = $connection->prepare("INSERT INTO formdata (name, email, phone, service, web_package, message, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                // Bind parameters
                $stmt_insert->bindValue(1, $name);
                $stmt_insert->bindValue(2, $email);
                $stmt_insert->bindValue(3, $phone);
                $stmt_insert->bindValue(4, $service);
                $stmt_insert->bindValue(5, $web_package);
                $stmt_insert->bindValue(6, $message);

                // Execute the statement
                if ($stmt_insert->execute()) {
                    $response = array("success" => true, "message" => "Form data inserted successfully");
                    echo json_encode($response);
                } else {
                    $response = array("success" => false, "error" => "Error inserting form data: " . $stmt_insert->errorInfo()[2]); // Use errorInfo for details
                    echo json_encode($response);
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
            // If the email field is not valid, return an error
            $response = array("success" => false, "error" => "Invalid email address provided");
            echo json_encode($response);
        }
    } else {
        // If any required field is missing, return an error
        $response = array("success" => false, "error" => "All fields are required");
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
