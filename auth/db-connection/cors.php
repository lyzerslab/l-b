<?php
// Allow requests only from your specific domain
header('Access-Control-Allow-Origin: https://lyzerslab.com/');

// Allow the following HTTP methods
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

// Allow the following headers
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

// Allow cookies to be sent with the request (useful for sessions or authentication tokens)
header('Access-Control-Allow-Credentials: true');

// Set the response content type to JSON
header('Content-Type: application/json; charset=UTF-8');

// Handle OPTIONS request (preflight) to ensure CORS is allowed
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return a 200 OK for preflight request
    http_response_code(200);
    exit;
}
?>