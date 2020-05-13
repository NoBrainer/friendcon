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

$challengeIndex = $_POST['challengeIndex'];
$name = $_POST['name'];
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];

$hasChallengeIndex = isset($challengeIndex) && !is_nan($challengeIndex);
$hasName = isset($name) && is_string($name) && !empty($name);
$hasStartTime = isset($startTime) && (is_string($startTime) || is_null($startTime));
$hasEndTime = isset($endTime) && (is_string($endTime) || is_null($endTime));

// Input validation
if (!$hasChallengeIndex) {
	$response['error'] = "Missing required field 'challengeIndex'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!$hasName && !$hasStartTime && !$hasEndTime) {
	$response['error'] = "No change fields.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Build the SQL pieces
$changes = [];
$types = '';
$params = [];
if ($hasName) {
	$changes[] = "name = ?";
	$types .= 's';
	$params[] = "$name";
}
if ($hasStartTime) {
	$changes[] = "startTime = ?";
	$types .= 's';
	$params[] = stringToDate($startTime);
}
if ($hasEndTime) {
	$changes[] = "endTime = ?";
	$types .= 's';
	$params[] = stringToDate($endTime);
}
$changesStr = join(", ", $changes);
$types .= 'i';
$params[] = $challengeIndex;

// Make the changes
$query = "UPDATE challenges SET $changesStr WHERE challengeIndex = ?";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, $types, ...$params);
if ($affectedRows === 1) {
	$response['message'] = "Challenge updated.";
	http_response_code(HTTP['OK']);

	// Return the updated challenge
	$result = executeSqlForResult($mysqli, "SELECT * FROM challenges WHERE challengeIndex = ?", 'i', $challengeIndex);
	$row = getNextRow($result);
	$response['data'] = [
			'challengeIndex' => intval($row['challengeIndex']),
			'startTime'      => stringToDate($row['startTime']),
			'endTime'        => stringToDate($row['endTime']),
			'name'           => "" . $row['name'],
			'published'      => boolval($row['published'])
	];
} else if ($affectedRows === 0) {
	http_response_code(HTTP['NOT_MODIFIED']);
} else {
	$response['error'] = "Unable to update challenge.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
