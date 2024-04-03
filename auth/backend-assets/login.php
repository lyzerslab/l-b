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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clientTimeZone = isset($_POST['timezone']) ? $_POST['timezone'] : 'UTC';
    date_default_timezone_set($clientTimeZone);

    $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;

    if (isset($_SESSION['last_login_attempt_time']) &&
        time() - $_SESSION['last_login_attempt_time'] < $lockoutTime) {
        header("location: ../../index.php?error=rate_limited");
        exit;
    }

    $_SESSION['last_login_attempt_time'] = time();

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

    if (empty($input_err) && empty($password_err)) {
        $sql = "SELECT id, username, password FROM admin_users WHERE username = :input OR email = :input";

        if ($stmt = $connection->prepare($sql)) {
            $stmt->bindParam(":input", $param_input, PDO::PARAM_STR);
            $param_input = $input;

            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $stmt->bindColumn("id", $id);
                    $stmt->bindColumn("username", $result_username);
                    $stmt->bindColumn("password", $hashed_password);
                    $stmt->fetch();

                    // Fetch 'blocked' from access_logs table
                    $sqlBlocked = "SELECT blocked FROM access_logs WHERE ip_address = :ip_address AND blocked = 1";
                    $stmtBlocked = $connection->prepare($sqlBlocked);
                    $stmtBlocked->bindParam(":ip_address", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

                    if ($stmtBlocked->execute()) {
                        $resultBlocked = $stmtBlocked->fetch(PDO::FETCH_ASSOC);

                        if ($resultBlocked && $resultBlocked['blocked'] == 1) {
                             $login_err = "You're blocked, Please contact the administrator.";
                            header("location: ../../index.php?error=blocked");
                            exit;
                        }
                    } else {
                        // Print SQL error for debugging
                        echo "SQL Error: " . implode(" ", $stmtBlocked->errorInfo());
                        exit;
                    }


                    if (password_verify($password, $hashed_password)) {
                        session_start();
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $result_username;
                        unset($_SESSION['login_attempts']);
                        logAccess($_SERVER['REMOTE_ADDR']);
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