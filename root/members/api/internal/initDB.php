<?php
// Requirements: The DB object is configured in the included PHP file.
// Usage:
// 1. Connects to the database

include($_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/db.php');

$mysqli = new MySQLi($DB['HOST'], $DB['USER'], $DB['PASS'], $DB['NAME']);
unset($DB); //Remove sensitive info from memory

if ($mysqli->connect_error) {
	die("Error connecting to the database");
}

// Report all errors, converting them to the mysqli_sql_exception class
// Note: Since this is after the data connection error, we won't ever print the username/password.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set the specific UTF8 character set
$mysqli->set_charset("utf8mb4");
