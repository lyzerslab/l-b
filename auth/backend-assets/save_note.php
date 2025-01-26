<?php
// Include database connection
include "../db-connection/config.php";

// Initialize the session
session_start();
 
// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the project ID and note data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate that both project ID and note are provided
    if (isset($data['projectID']) && isset($data['note'])) {
        // Sanitize inputs
        $projectID = filter_var($data['projectID'], FILTER_SANITIZE_NUMBER_INT);
        $note = filter_var($data['note'], FILTER_SANITIZE_STRING);

        try {
            // Prepare SQL statement to update project notes
            $sql = "UPDATE projects SET ProjectNote = :note WHERE ProjectID = :projectID";
            $stmt = $connection->prepare($sql);
            // Bind parameters
            $stmt->bindParam(':note', $note, PDO::PARAM_STR);
            $stmt->bindParam(':projectID', $projectID, PDO::PARAM_INT);
            // Execute the statement
            $stmt->execute();

            // Check if the update was successful
            if ($stmt->rowCount() > 0) {
                // Return success message
                echo "Note updated successfully.";
            } else {
                // Return error message if no rows were affected (probably due to no matching project ID)
                echo "Error: Project not found or note unchanged.";
            }
        } catch (PDOException $e) {
            // Handle database errors
            echo "Database Error: " . $e->getMessage();
        }
    } else {
        // Return error message if project ID or note is missing
        echo "Error: Project ID and note are required.";
    }
} else {
    // Return error message if the request method is not POST
    echo "Error: Invalid request method.";
}
?>
