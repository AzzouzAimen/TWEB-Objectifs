<?php
/**
 * Database Configuration (MySQLi Version)
 * This file establishes a connection to the MySQL database using MySQLi.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password');
define('DB_NAME', 'TDW');

// Create MySQLi connection
// The '@' symbol suppresses default warnings, allowing our custom error handling.
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    // If connection fails, display error and stop execution
    die("Database connection failed: " . $mysqli->connect_error);
}

// Set the character set to utf8mb4 for full Unicode support
$mysqli->set_charset("utf8mb4");

/**
 * PHP SYNTAX EXPLANATION:
 * 
 * new mysqli() - Creates a new MySQLi object (database connection).
 * ->connect_error - A property of the MySQLi object that contains an error message if the connection failed.
 * ->set_charset() - A method to set the default client character set. Essential for preventing encoding issues.
 * 
 * We will now use the '$mysqli' variable throughout the application instead of '$pdo'.
 */
?>