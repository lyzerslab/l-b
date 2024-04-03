<?php
// Include your database connection code here
include '../../db-connection/config.php'; // Adjust the path accordingly
// Add this at the beginning of your PHP script
error_log('Received POST request: ' . print_r($_POST, true));

// Initialize the session
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    // Validate $action and $id here
    if (empty($action) || !in_array($action, ['block', 'unblock'])) {
        $errorMessage = 'Invalid action';
        error_log($errorMessage, 3, 'error.log'); // Log the error to a file
        echo 'error: ' . $errorMessage;
        exit; // Stop script execution
    }

    if (!is_numeric($id) || $id <= 0) {
        $errorMessage = 'Invalid ID';
        error_log($errorMessage, 3, 'error.log'); // Log the error to a file
        echo 'error: ' . $errorMessage;
        exit; // Stop script execution
    }

    // Update the database based on the action
    try {
        if ($action === 'block') {
            // Perform block action and update the database
            $stmt = $connection->prepare("UPDATE access_logs SET blocked = 1 WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Assuming the action was successful
            if ($stmt->rowCount() > 0) {
                echo 'success';
            } else {
                $errorMessage = 'No rows affected for id ' . $id;
                error_log($errorMessage, 3, 'error.log'); // Log the error to a file
                echo 'error: ' . $errorMessage;
            }
        } elseif ($action === 'unblock') {
            // Perform unblock action and update the database
            $stmt = $connection->prepare("UPDATE access_logs SET blocked = 0 WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Assuming the action was successful
            if ($stmt->rowCount() > 0) {
                echo 'success';
            } else {
                $errorMessage = 'No rows affected for id ' . $id;
                error_log($errorMessage, 3, 'error.log'); // Log the error to a file
                echo 'error: ' . $errorMessage;
            }
        }
    } catch (PDOException $e) {
        // Handle database connection or query error
        $errorMessage = 'Database Error: ' . $e->getMessage();
        error_log($errorMessage, 3, 'error.log'); // Log the error to a file
        echo 'error: ' . $errorMessage;
    }
} else {
    // Invalid request method
    echo 'error: Invalid request method';
}
?>
