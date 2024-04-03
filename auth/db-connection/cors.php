<?php
// Allow requests from any origin
header('Access-Control-Allow-Origin: *');

// Allow the following HTTP methods
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

// Allow the following headers
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

// Allow cookies to be sent with the request
header('Access-Control-Allow-Credentials: true');

// Set the response content type to JSON
header('Content-Type: application/json; charset=UTF-8');

?>