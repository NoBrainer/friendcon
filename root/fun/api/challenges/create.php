<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');
include('../internal/checkAdmin.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

if (!isset($userSession) || $userSession == "" || !$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	http_response_code(HTTP['FORBIDDEN']);
	echo json_encode($response);
	return;
}

$name = $_POST['name'];
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];

$hasName = isset($name) && is_string($name) && !empty($name);
$hasStartTime = isset($startTime) && (is_string($startTime) || is_null($startTime));
$hasEndTime = isset($endTime) && (is_string($endTime) || is_null($endTime));

// Input validation
if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Make sure the name is unique
$result = executeSqlForResult($mysqli, "SELECT * FROM challenges WHERE name = ?", 's', $name);
if ($result->num_rows > 0) {
	$response['error'] = "There's already a challenge with that name.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Build the SQL pieces
$fields = [];
$vals = [];
$types = '';
$params = [];
if ($hasName) {
	$fields[] = "name";
	$vals[] = "?";
	$types .= 's';
	$params[] = "$name";
}
if ($hasStartTime) {
	$fields[] = "startTime";
	$vals[] = "?";
	$types .= 's';
	$params[] = stringToDate($startTime);
}
if ($hasEndTime) {
	$fields[] = "endTime";
	$vals[] = "?";
	$types .= 's';
	$params[] = stringToDate($endTime);
}
$fieldStr = join(", ", $fields);
$valStr = join(", ", $vals);

// Make the changes
$query = "INSERT INTO challenges ($fieldStr) VALUES ($valStr)";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, $types, ...$params);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create challenge.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
	return;
}

// Get the new challenge
$query = "SELECT * FROM challenges WHERE name = ?";
$result = executeSqlForResult($mysqli, $query, 's', $name);
$row = getNextRow($result);
$response['data'] = [
		'challengeIndex' => intval($row['challengeIndex']),
		'name'           => "" . $row['name'],
		'startTime'      => stringToDate($row['startTime']),
		'endTime'        => stringToDate($row['endTime']),
		'published'      => boolval($row['published'])
];
$response['message'] = "Challenge created.";
http_response_code(HTTP['OK']);
echo json_encode($response);
