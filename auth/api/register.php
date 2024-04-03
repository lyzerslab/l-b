<?php
// Call API header
require_once '../db-connection/cors.php';

// Connect to the database
require_once '../db-connection/config.php';

// Output values
function createResponse($status, $message, $data = [])
{
    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];
    return json_encode($response);
}

// Brute force protection - Limit requests
function checkRequestLimit($ip_address)
{
    global $connection;
    $query = $connection->prepare("SELECT COUNT(*) FROM customers 
    WHERE ip_address = :ip_address AND request_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $query->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Maximum 100 requests/hour
    if ($result['COUNT(*)'] > 100) {
        return false;
    }

    return true;
}

// Limitation of access time
function checkRequestTime($ip_address)
{
    global $connection;
    $query = $connection->prepare("SELECT request_time FROM customers 
    WHERE ip_address = :ip_address 
    ORDER BY request_time DESC LIMIT 1");
    $query->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $last_request_time = strtotime($result['request_time']);
        $current_time = strtotime(date('Y-m-d H:i:s'));
        if ($current_time - $last_request_time < 1) {
            return false;
        }
    }

    return true;
}

// Encrypt
function xorEncrypt($input)
{
    return base64_encode($input);
}

// Processing API requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!checkRequestLimit($_SERVER['REMOTE_ADDR'])) {
        echo createResponse('error', 'Too many requests! Try again later.', []);
        exit;
    }

    if (!checkRequestTime($_SERVER['REMOTE_ADDR'])) {
        echo createResponse('error', 'Request too common! Try again later.', []);
        exit;
    }

    // Check and process entered data
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if keys are set in the data array
    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['rePassword'])) {
        echo createResponse('error', 'Invalid request. Missing required parameters.', []);
        exit;
    }

    // Check if values are not empty
    if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['rePassword'])) {
        echo createResponse('error', 'All fields are mandatory on the form.');
        exit;
    }

    // Check and assign values after ensuring the keys exist
    if (isset($data['username'])) {
        $username = $data['username'];
    } else {
        echo createResponse('error', 'Username is missing in the request.');
        exit;
    }

    if (isset($data['email'])) {
        $email = $data['email'];
    } else {
        echo createResponse('error', 'Email is missing in the request.');
        exit;
    }

    if (isset($data['password'])) {
        $password = $data['password'];
    } else {
        echo createResponse('error', 'Password is missing in the request.');
        exit;
    }

    if (isset($data['rePassword'])) {
        $rePassword = $data['rePassword'];
    } else {
        echo createResponse('error', 'Re-entered password is missing in the request.');
        exit;
    }

    if (!validateInput($username) || !validateInput($password) || !validateInput($email)) {
        echo createResponse('error', 'You have entered incorrect information.');
        exit;
    }

    if ($password !== $rePassword) {
        echo createResponse('error', 'Passwords do not match.');
        exit;
    }

    $pattern = '/^(?=.*[0-9])(?=.*[A-Z]).{8,24}$/';
    if (!preg_match($pattern, $password)) {
        echo createResponse('error', 'The password is not strong enough. It must be at least 8 characters long and contain at least one uppercase letter and number. Your password can be a maximum of 24 characters.');
        exit;
    }

    $encrypted_password = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 2048,
        'time_cost'   => 4,
        'threads'     => 2,
    ]);
    $encrypted_email = xorEncrypt($email, 'secret_key');

    echo createResponse('success', 'Account registered successfully.', [
        'username' => $username,
        'password' => $encrypted_password,
        'email' => $encrypted_email
    ]);

    saveRequest($_SERVER['REMOTE_ADDR'], $username, $encrypted_password, $encrypted_email);
} else {
    echo createResponse('error', 'Wrong request.', []);
    exit;
}

function saveRequest($ip_address, $username, $password, $email)
{
    global $connection;
    $query = $connection->prepare("INSERT INTO customers (ip_address, username, password, email)
    VALUES (:ip_address, :username, :password, :email)");
    $query->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
}

function validateInput($input)
{
    // SQL Injection protection
    if (preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $input)) {
        return false;
    }

    // XSS protection
    if (preg_match('/<[^>]*>/', $input)) {
        return false;
    }

    return true;
}
?>
