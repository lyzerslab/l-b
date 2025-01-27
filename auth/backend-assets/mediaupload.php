<?php
// Initialize the session
session_start();
// Include the database configuration file
require_once "../db-connection/config.php";
 
// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the current year and month to create a folder
    $currentYearMonth = date('Y-m');  // Example: '2025-01'

    $currentYearMonth = date('Y-m');  
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/files/blog/uploads/' . $currentYearMonth . '/';
    $webDir = 'https://www.dashboard.lyzerslab.com/files/blog/uploads/' . $currentYearMonth . '/';
    
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        echo json_encode(['error' => 'Failed to create upload directory.', 'uploadDir' => $uploadDir]);
        exit;
    }

    // Validate the uploaded file
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'No file uploaded or an upload error occurred.']);
        exit;
    }

    $file = $_FILES['file'];
    $fileName = basename($file['name']);
    $safeFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName); // Sanitize file name
    $targetPath = $uploadDir . $safeFileName;
    $webPath = $webDir . $safeFileName;

    // Validate file type and size
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', // Image formats
        'application/pdf',                                     // PDF
        'video/mp4', 'video/mpeg', 'video/avi', 'video/webm'   // Video formats
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
            $stmt->execute([$safeFileName, $webPath, $file['type']]);

            echo json_encode(['success' => 'File uploaded successfully.', 'filePath' => $webPath]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to save file info to the database: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Failed to upload file. Please try again.']);
    }
}
?>