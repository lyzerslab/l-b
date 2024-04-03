<?php
include "../db-connection/config.php";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $employeeName = $_POST['employeeName'];
    $employeeDesignation = $_POST['employeeDesignation'];
    $totalExperience = $_POST['totalExperience'];
    $joiningDate = $_POST['joiningDate'];
    $skillsArray = isset($_POST['skillsArray']) ? json_decode($_POST['skillsArray'], true) : [];
    $currentAddress = $_POST['currentAddress'];
    $presentAddress = $_POST['presentAddress'];
    $employeeType = $_POST['employeeType'];

    // Upload photo file
    $targetDir = "./employee-photo/";
    $employeePhoto = $_FILES['employeePhoto']['name'];
    $targetFile = $targetDir . basename($employeePhoto);
    
    if (move_uploaded_file($_FILES["employeePhoto"]["tmp_name"], $targetFile)) {
        // Insert data into the Employees table using prepared statement
        $sql = "INSERT INTO employees (employeeName, photo, designation, totalExperience, joiningDate, fieldOfExpertise, currentAddress, presentAddress, employeeType)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $skillsString = implode(', ', $skillsArray);
        $stmt->execute([$employeeName, $employeePhoto, $employeeDesignation, $totalExperience, $joiningDate, $skillsString, $currentAddress, $presentAddress, $employeeType]);
        echo "New record created successfully";
    } else {
        echo "Error uploading file";
    }
}

// Close connection (if necessary)
// $connection = null;
?>
