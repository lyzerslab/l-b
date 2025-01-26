<?php

// Define the base URL dynamically
define('BASE_URL', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]/");


//Database connection config

$config = array(
    'db_hostname' => '127.0.0.1',
    'db_name' => 'aeshuvoi_lyzerDash',
    'db_username' => 'aeshuvoi_lyzerDU',
    'db_password' => ',PDoJ~F&gy-A',
);

try 
{
    $connection = new PDO("mysql:host=" . $config['db_hostname'] . ";dbname=" . $config['db_name'], $config['db_username'], $config['db_password']);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_AUTOCOMMIT, true);

} 
catch(PDOException $e) 
{
    die("Connection failed: " . $e->getMessage());
}

// Function to get the database connection
function getDatabaseConnection() {
    global $connection;
    return $connection;
}

?>