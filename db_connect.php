<?php
/**
 * Database Connection File
 * 
 * This file establishes a connection to the database using settings from config/database_config.php
 * All database connections should use this file to ensure consistency.
 */

require_once __DIR__ . '/config/database_config.php';

$servername = DB_HOST;
$username   = DB_USER;
$password   = DB_PASS;
$dbname     = DB_NAME;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
