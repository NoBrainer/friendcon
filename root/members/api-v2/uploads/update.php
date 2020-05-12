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

$file = $_POST['file'];
$state = $_POST['state'];

$hasFile = isset($file) && is_string($file) && !empty($file);
$hasState = isset($state) && is_string($state) && !empty($state);

// Validate input
if (!$hasFile) {
	$response['error'] = "Missing required field 'file'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!$hasState) {
	http_response_code(HTTP['NOT_MODIFIED']);
	return;
}

// Get the value of the state string
$result = executeSqlForResult($mysqli, "SELECT * FROM uploadState WHERE state = ?", 's', $state);
if (!hasRows($result, 1)) {
	$response['error'] = "Invalid value provided for 'state'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}
$row = getNextRow($result);
$stateValue = intval($row['value']);

// Make the change
$query = "UPDATE uploads SET state = ? WHERE file = ?";
$info = executeSqlForInfo($mysqli, $query, 'is', $stateValue, $file);
if ($info['matched'] === 1) {
	$response['message'] = "Set file [$file] to $state.";
	http_response_code(HTTP['OK']);
} else if ($info['matched'] === 0) {
	$response['error'] = "No file found.";
	http_response_code(HTTP['NOT_FOUND']);
} else {
	$response['error'] = "Unexpected error occurred.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}

// Send the JSON
echo json_encode($response);
