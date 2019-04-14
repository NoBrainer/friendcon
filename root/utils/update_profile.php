<?php
// Usage: 
// 1. Connects to the database
// 2. Accepts any combination of these parameters:
//		- phone (String)
//		- emergencyCN (String)
//		- emergencyCNP (String)
//		- favoriteAnimal (String)
//		- favoriteBooze (String)
//		- favoriteNerdism (String)

session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
	// If not logged in, go to main homepage
	header("Location: /");
	exit;
}
include_once './dbconnect.php';

// Get parameters from the url
$setStr = "";
if (isset($_POST['phone'])) {
	$param = $MySQLi_CON->real_escape_string(trim($_POST['phone']));
	$param = preg_replace('/\D+/', '', $param);
	if ($setStr !== "") $setStr = "{$setStr}, ";
	$setStr = "{$setStr}phone='$param'";
}
if (isset($_POST['emergencyCN'])) {
	$param = $MySQLi_CON->real_escape_string(trim($_POST['emergencyCN']));
	if ($setStr !== "") $setStr = "{$setStr}, ";
	$setStr = "{$setStr}emergencyCN='$param'";
}
if (isset($_POST['emergencyCNP'])) {
	$param = $MySQLi_CON->real_escape_string(trim($_POST['emergencyCNP']));
	$param = preg_replace('/\D+/', '', $param);
	if ($setStr !== "") $setStr = "{$setStr}, ";
	$setStr = "{$setStr}emergencyCNP='$param'";
}
if (isset($_POST['favoriteAnimal'])) {
	$param = $MySQLi_CON->real_escape_string(trim($_POST['favoriteAnimal']));
	if ($setStr !== "") $setStr = "{$setStr}, ";
	$setStr = "{$setStr}favoriteAnimal='$param'";
}
if (isset($_POST['favoriteBooze'])) {
	$param = $MySQLi_CON->real_escape_string(trim($_POST['favoriteBooze']));
	if ($setStr !== "") $setStr = "{$setStr}, ";
	$setStr = "{$setStr}favoriteBooze='$param'";
}
if (isset($_POST['favoriteNerdism'])) {
	$param = $MySQLi_CON->real_escape_string(trim($_POST['favoriteNerdism']));
	if ($setStr !== "") $setStr = "{$setStr}, ";
	$setStr = "{$setStr}favoriteNerdism='$param'";
}

if ($setStr === '') {
	die("No changes.");
}

// Build query
$query = "UPDATE users
	SET $setStr
	WHERE uid=".$_SESSION['userSession'];
if ($MySQLi_CON->query($query)) {
	die("Update Successful!");
} else {
	die("Something went wrong. Please try again!");
}

?>