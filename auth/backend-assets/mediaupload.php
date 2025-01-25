<?php
// Include the database configuration file
require_once "../db-connection/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the current year and month to create a folder
    $currentYearMonth = date('Y-m');  // Example: '2025-01'

    // Local directory to store uploads, based on the year/month
    $uploadDir = 'https://www.dashboard.lyzerslab.com/files/blog/uploads/' . $currentYearMonth . '/';
    $webDir = 'hhttps://www.dashboard.lyzerslab.com/files/blog/uploads/' . $currentYearMonth . '/';  // Public URL for accessing uploaded files

    // Ensure the uploads directory exists for the current month
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);  // Create the directory if it doesn't exist
    }

    // Handle the uploaded file
    $file = $_FILES['file'];
    $fileName = basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    $webPath = $webDir . $fileName;  // This will be saved in the database

    // Validate file type and size
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', // Image formats
        'application/pdf',                                     // PDF
        'video/mp4', 'video/mpeg', 'video/avi', 'video/webm'    // Video formats
    ];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['error' => 'Invalid file type. Allowed types: JPEG, WEBP, PNG, GIF, PDF, MP4, MPEG, AVI, WEBM.']);
        exit;
    }

    if ($file['size'] > 50 * 1024 * 1024) { // 50 MB limit
        echo json_encode(['error' => 'File size exceeds 50 MB.']);
        exit;
    }

    // Check for duplicate file names
    if (file_exists($targetPath)) {
        echo json_encode(['error' => 'A file with the same name already exists.']);
        exit;
    }

    // Move the uploaded file to the server's upload directory
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        try {
            // Get the database connection
            $pdo = getDatabaseConnection();

            // Save file metadata to the database
            $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_type) VALUES (?, ?, ?)");
            $stmt->execute([$fileName, $webPath, $file['type']]);

            echo json_encode(['success' => 'File uploaded successfully.', 'filePath' => $webPath]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to save file info to the database: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Failed to upload file. Please try again.']);
    }
}
?>