<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include('dbconnect.php');

// Check the user points
$result = $MySQLi_CON->query("SELECT u.upoints FROM users u WHERE u.uid={$userSession}");
if (!$result) {
    die("Error getting points [DB-1]");
}
$checkPoints = $result->fetch_array();
$result->free_result();
$points = $checkPoints['upoints'];

// Return the points
die("{$points}");
?>