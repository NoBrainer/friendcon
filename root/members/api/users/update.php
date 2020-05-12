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

include('../internal/initDB.php');
include('../internal/constants.php');
include('../internal/functions.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

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
	http_response_code(HTTP['NOT_MODIFIED']);
	return;
}

// Add the last value, the userSession
$values[] = (int)$userSession;
$types .= "i";

// Update the user
$query = "UPDATE users SET $setStr WHERE uid = ?";
$stmt = prepareSqlStatement($mysqli, $query, $types, ...$values);
$stmt->execute();

if ($stmt->affected_rows === 1) {
	$response['data'] = "User updated.";
	http_response_code(HTTP['OK']);
} else if ($stmt->affected_rows > 1) {
	$response['error'] = "Multiple profiles updated... HOW!?";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
} else {
	http_response_code(HTTP['NOT_MODIFIED']);
}
echo json_encode($response);
