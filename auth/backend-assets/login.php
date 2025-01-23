<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "../db-connection/config.php";

if ($connection === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$input = $password = "";
$input_err = $password_err = $login_err = "";

$maxAttempts = 3;
$lockoutTime = 300; // 5 minutes
$blockTime = 3600; // 1 hour

if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= $maxAttempts) {
    blockIpAddress($_SERVER['REMOTE_ADDR'], $blockTime);
    header("location: ../../index.php?error=account_locked");
    exit;
}

// Assuming this part of the code is where you verify the user's credentials

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize variables for input and password
    $input = $password = "";
    $input_err = $password_err = $login_err = "";

    // Your maxAttempts and lockoutTime logic is fine

    // Retrieve the user input (username/email) and password
    if (empty(trim($_POST["input"]))) {
        $input_err = "Please enter username or email.";
    } else {
        $input = trim($_POST["input"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, proceed to validate the user
    if (empty($input_err) && empty($password_err)) {
        $sql = "SELECT id, username, password, is_admin, profile_photo FROM admin_users WHERE username = :input OR email = :input"; // Added is_admin and profile_photo

        if ($stmt = $connection->prepare($sql)) {
            $stmt->bindParam(":input", $param_input, PDO::PARAM_STR);
            $param_input = $input;

            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $stmt->bindColumn("id", $id);
                    $stmt->bindColumn("username", $result_username);
                    $stmt->bindColumn("password", $hashed_password);
                    $stmt->bindColumn("is_admin", $is_admin); // Bind the is_admin value
                    $stmt->bindColumn("profile_photo", $profile_photo); // Bind the profile photo if present
                    $stmt->fetch();

                    // Check if the IP address is blocked (blocked IP logic remains the same)

                    if (password_verify($password, $hashed_password)) {
                        // Start the session
                        session_start();
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $result_username;
                        $_SESSION["profile_photo"] = $profile_photo; // Store the profile photo in session
                        $_SESSION["is_admin"] = $is_admin; // Store the is_admin value in the session

                        unset($_SESSION['login_attempts']); // Reset the login attempts counter
                        logAccess($_SERVER['REMOTE_ADDR']); // Log the access

                        // Redirect the user to the dashboard or appropriate page
                        header("location: ../../files/dashboard.php");
                        exit;
                    } else {
                        $login_err = "Invalid username or password.";
                        header("location: ../../index.php?error=1");
                        exit;
                    }
                } else {
                    $login_err = "Invalid username or password.";
                    header("location: ../../index.php?error=1");
                    exit;
                }
            } else {
                echo "SQL Error: " . implode(" ", $stmt->errorInfo());
            }

            unset($stmt);
        }
    }
}

function blockIpAddress($ipAddress, $blockTime) {
    global $connection;
    $clientTimeZone = isset($_POST['timezone']) ? $_POST['timezone'] : 'UTC';
    date_default_timezone_set($clientTimeZone);

    // Debug: Print client's time zone and current server time
    echo "Client's Time Zone: $clientTimeZone<br>";
    echo "Server's Time: " . date('Y-m-d H:i:s') . "<br>";

    $checkQuery = "SELECT * FROM blocked_ips WHERE ip_address = ? AND blocked_until > NOW()";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->execute([$ipAddress]);
    $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$checkResult) {
        $minBlockTime = 15 * 60; // 15 minutes in seconds
        $blockedUntil = date('Y-m-d H:i:s', strtotime("+ " . max($blockTime, $minBlockTime) . " seconds"));

        // Debug: Print calculated blockedUntil time
        echo "Blocked Until: $blockedUntil<br>";

        $insertQuery = "INSERT INTO blocked_ips (ip_address, blocked_until) VALUES (?, ?)";
        $insertStmt = $connection->prepare($insertQuery);
        $insertStmt->execute([$ipAddress, $blockedUntil]);
    } else {
        $blockedUntil = $checkResult['blocked_until'];

        if (strtotime($blockedUntil) <= time()) {
            $removeQuery = "DELETE FROM blocked_ips WHERE ip_address = ?";
            $removeStmt = $connection->prepare($removeQuery);
            $removeStmt->execute([$ipAddress]);
        }
    }

    $checkStmt = null;
    $insertStmt = null;
    $removeStmt = null;
}

function logAccess($ipAddress) {
    global $connection;

    $insertQuery = "INSERT INTO access_logs (ip_address, access_time) VALUES (?, NOW())";
    $insertStmt = $connection->prepare($insertQuery);
    $insertStmt->execute([$ipAddress]);
    $insertStmt->closeCursor();
}
?>