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
include_once('sql_functions.php');

// With the provided parameters, determine the $values and $types for the prepared statement, and get what we need for
// the query.
$queryPieces = [];
$values = [];
$types = "";
if (isset($_POST['phone'])) {
    $param = trim($_POST['phone']);
    $param = preg_replace('/\D+/', '', $param);
    $queryPieces[] = "phone = ?";
    $values[] = $param;
    $types .= "s";
}
if (isset($_POST['emergencyCN'])) {
    $param = trim($_POST['emergencyCN']);
    $queryPieces[] = "emergencyCN = ?";
    $values[] = $param;
    $types .= "s";
}
if (isset($_POST['emergencyCNP'])) {
    $param = trim($_POST['emergencyCNP']);
    $param = preg_replace('/\D+/', '', $param);
    $queryPieces[] = "emergencyCNP = ?";
    $values[] = $param;
    $types .= "s";
}
if (isset($_POST['favoriteAnimal'])) {
    $param = trim($_POST['favoriteAnimal']);
    $queryPieces[] = "favoriteAnimal = ?";
    $values[] = $param;
    $types .= "s";
}
if (isset($_POST['favoriteBooze'])) {
    $param = trim($_POST['favoriteBooze']);
    $queryPieces[] = "favoriteBooze = ?";
    $values[] = $param;
    $types .= "s";
}
if (isset($_POST['favoriteNerdism'])) {
    $param = trim($_POST['favoriteNerdism']);
    $queryPieces[] = "favoriteNerdism = ?";
    $values[] = $param;
    $types .= "s";
}
$setStr = join(", ", $queryPieces);

if ($setStr === '') {
    die("No changes.");
}

// Add the last value, the userSession
$values[] = $userSession;
$types .= "i";

// Update the user
$query = "UPDATE users SET $setStr WHERE uid = ?";
$result = prepareSqlForResult($MySQLi_CON, $query, $types, ...$values);
if ($result) {
    die("Update Successful!");
} else {
    die("Something went wrong. Please try again!");
}

?>