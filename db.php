<?php

// This file connects to the database
// Database connection settings for XAMPP
// Usually the default settings are:
// - host: localhost
// - user: root  
// - password: (empty)
// - database: e1_personal_record_db

// Function to get database connection
function db()
{
    // Database settings
    $host = 'localhost';
    $dbName = 'e1_personal_record_db';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    
    // Create connection string
    $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";
    
    // Create PDO connection
    // PDO is a PHP class for connecting to databases
    $pdo = new PDO($dsn, $user, $pass);
    
    // Set error mode to throw exceptions (so we can catch errors)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array (returns column names as keys)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Return the connection
    return $pdo;
}

?>
