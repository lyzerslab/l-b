<?php

$baseDirectory = 'C:/xampp/htdocs/reactcrud/backend/auth/api/';
$endpoints = [];

function scanDirectory($dir) {
    global $endpoints, $baseDirectory;

    $files = scandir($dir);

    foreach ($files as $file) {
        $current = $dir . '/' . $file;
        
        if ($file != '.' && $file != '..') {
            if (is_dir($current)) {
                // If it's a directory, recursively scan it
                scanDirectory($current);
            } else {
                // If it's a file, add it to the endpoints
                $relativePath = str_replace($baseDirectory, '', $current);
                $endpoints[] = 'http://localhost/reactcrud/backend/auth/api' . $relativePath;
            }
        }
    }
}

if (is_dir($baseDirectory)) {
    scanDirectory($baseDirectory);
}

header('Content-Type: application/json');
echo json_encode($endpoints);
?>
