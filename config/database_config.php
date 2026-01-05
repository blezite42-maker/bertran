<?php
/**
 * Database Configuration
 * 
 * This file contains database connection settings.
 * 
 * IMPORTANT: 
 * 1. Update DB_NAME to match your database name
 * 2. Make sure you have run database_schema.sql on your database
 * 3. The database name here should match the database where you've run database_schema.sql
 * 
 * This is the ONLY place where you need to specify the database name.
 * All other files use this configuration through db_connect.php
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bertran'); // TODO: Change this to your actual database name

// For production, you can use environment variables:
// define('DB_NAME', getenv('DB_NAME') ?: 'your_database_name');

