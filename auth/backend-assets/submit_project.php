<?php
// Include database connection
include "../db-connection/config.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Initialize variables with form data
        $projectName = $_POST["projectName"];
        $projectDescription = $_POST["projectDescription"];
        $clientName = $_POST["clientsName"];
        $projectType = $_POST["projectType"];
        $duration = $_POST["duration"];
        $projectStart = $_POST["projectStart"];
        $projectEnd = $_POST["projectEnd"];
        $assignTo = implode(",", $_POST["employees"]); // Assuming employees are stored as comma-separated values

        // Prepare SQL statement to insert project data into the database
        $sql = "INSERT INTO projects (ProjectName, ProjectDescription, ClientName, ProjectType, Duration, ProjectStart, ProjectEnd, AssignTo, CreatedAt) 
                VALUES (:projectName, :projectDescription, :clientName, :projectType, :duration, :projectStart, :projectEnd, :assignTo, NOW())";
        
        // Prepare and execute the SQL statement using PDO
        $stmt = $connection->prepare($sql); // Use $connection instead of $pdo
        $stmt->execute([
            ':projectName' => $projectName,
            ':projectDescription' => $projectDescription,
            ':clientName' => $clientName,
            ':projectType' => $projectType,
            ':duration' => $duration,
            ':projectStart' => $projectStart,
            ':projectEnd' => $projectEnd,
            ':assignTo' => $assignTo
        ]);

        // Check if the SQL statement executed successfully
        if ($stmt) {
            // Return success message
            echo "Project added successfully.";
        } else {
            // Return error message
            echo "Error: Unable to add project.";
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Database Error: " . $e->getMessage();
    }
} else {
    // Return error message if the form is not submitted
    echo "Error: Form not submitted.";
}
?>
