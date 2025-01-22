<?php

//Database connection config

$config = array(
    'db_hostname' => 'localhost',
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