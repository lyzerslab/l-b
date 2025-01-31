<?php
require_once '../../db-connection/cors.php';
require_once '../../db-connection/config.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input sanitization and validation
    $license_key = isset($_POST['license_key']) ? trim($_POST['license_key']) : '';
    $domain = isset($_POST['domain']) ? trim($_POST['domain']) : '';

    // Validate license_key and domain
    if (empty($license_key) || empty($domain)) {
        echo json_encode(["status" => "error", "message" => "License key and domain are required"]);
        exit;
    }

    // Basic domain validation (optional, more advanced options can be used)
    if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        echo json_encode(["status" => "error", "message" => "Invalid domain format"]);
        exit;
    }

    // Prepare the query to validate the license
    $stmt = $connection->prepare("SELECT expiry_date FROM licenses WHERE domain = ? AND license_key = ?");
    $stmt->execute([$domain, $license_key]);
    $license = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($license) {
        $expiry_date = $license['expiry_date'];
        if (strtotime($expiry_date) > time()) {
            echo json_encode(["status" => "valid", "message" => "License is active", "expiry_date" => $expiry_date]);
        } else {
            echo json_encode(["status" => "expired", "message" => "License has expired"]);
        }
    } else {
        echo json_encode(["status" => "invalid", "message" => "Invalid license"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>