<?php
header("Access-Control-Allow-Origin: https://lyzerslab.com");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../db-connection/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_data = file_get_contents("php://input");
    $data = json_decode($post_data, true);

    if (isset($data['name'], $data['email'], $data['phone'], $data['service'], $data['message'])) {
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'];
            $service = $data['service'];
            $message = $data['message'];
            $platform = isset($data['platform']) ? $data['platform'] : null;
            $web_package = isset($data['packageOption']) ? $data['packageOption'] : null;

            try {
                $stmt_insert = $connection->prepare("INSERT INTO formdata (name, email, phone, service, platform, web_package, message, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                
                $stmt_insert->bindValue(1, $name);
                $stmt_insert->bindValue(2, $email);
                $stmt_insert->bindValue(3, $phone);
                $stmt_insert->bindValue(4, $service);
                $stmt_insert->bindValue(5, $platform);
                $stmt_insert->bindValue(6, $web_package);
                $stmt_insert->bindValue(7, $message);

                if ($stmt_insert->execute()) {
                    // Send email notification
                    $to = "support@lyzerslab.com";
                    $subject = "New Contact Form Submission";
                    $email_message = "Name: $name\n";
                    $email_message .= "Email: $email\n";
                    $email_message .= "Phone: $phone\n";
                    $email_message .= "Service: $service\n";
                    if ($platform) {
                        $email_message .= "Platform: $platform\n";
                    }
                    if ($web_package) {
                        $email_message .= "Package: $web_package\n";
                    }
                    $email_message .= "Message: $message\n";

                    $headers = "From: $email";

                    mail($to, $subject, $email_message, $headers);

                    $response = array("success" => true, "message" => "Form submitted successfully");
                    echo json_encode($response);
                } else {
                    $response = array("success" => false, "error" => "Error inserting form data");
                    echo json_encode($response);
                }
            } catch (PDOException $e) {
                $response = array("success" => false, "error" => "Database error: " . $e->getMessage());
                echo json_encode($response);
            } finally {
                if (isset($stmt_insert)) {
                    $stmt_insert->closeCursor();
                }
            }
        } else {
            $response = array("success" => false, "error" => "Invalid email address");
            echo json_encode($response);
        }
    } else {
        $response = array("success" => false, "error" => "Missing required fields");
        echo json_encode($response);
    }

    $connection = null;
} else {
    $response = array("success" => false, "error" => "Invalid request method");
    echo json_encode($response);
}
?>