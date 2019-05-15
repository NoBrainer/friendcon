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
include('dbconnect.php');

// Get parameters from the url
$params = [];
if (isset($_POST['phone'])) {
    $param = $MySQLi_CON->real_escape_string(trim($_POST['phone']));
    $param = preg_replace('/\D+/', '', $param);
    $params[] = "phone='$param'";
}
if (isset($_POST['emergencyCN'])) {
    $param = $MySQLi_CON->real_escape_string(trim($_POST['emergencyCN']));
    $params[] = "emergencyCN='$param'";
}
if (isset($_POST['emergencyCNP'])) {
    $param = $MySQLi_CON->real_escape_string(trim($_POST['emergencyCNP']));
    $param = preg_replace('/\D+/', '', $param);
    $params[] = "emergencyCNP='$param'";
}
if (isset($_POST['favoriteAnimal'])) {
    $param = $MySQLi_CON->real_escape_string(trim($_POST['favoriteAnimal']));
    $params[] = "favoriteAnimal='$param'";
}
if (isset($_POST['favoriteBooze'])) {
    $param = $MySQLi_CON->real_escape_string(trim($_POST['favoriteBooze']));
    $params[] = "favoriteBooze='$param'";
}
if (isset($_POST['favoriteNerdism'])) {
    $param = $MySQLi_CON->real_escape_string(trim($_POST['favoriteNerdism']));
    $params[] = "favoriteNerdism='$param'";
}
$setStr = join(", ", $params);

if ($setStr === '') {
    die("No changes.");
}

// Build query
$query = "UPDATE users
	SET $setStr
	WHERE uid=" . $_SESSION['userSession'];
if ($MySQLi_CON->query($query)) {
    die("Update Successful!");
} else {
    die("Something went wrong. Please try again!");
}

?>