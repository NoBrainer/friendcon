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
$hasChallengeIndex = isset($challengeIndex) && is_string($challengeIndex) && !empty($challengeIndex);

// Input validation
if (!$hasChallengeIndex) {
	$response['error'] = "Missing required field 'challengeIndex'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Prevent deleting challenges with approved uploads
$query = "SELECT * FROM uploads WHERE challengeIndex = ? AND state > 0";
$result = executeSqlForResult($mysqli, $query, 'i', $challengeIndex);
if ($result->num_rows > 0) {
	$response['error'] = "Cannot delete a challenge with approved uploads.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Delete the challenge
$query = "DELETE FROM challenges WHERE challengeIndex = ?";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, 'i', $challengeIndex);
if ($affectedRows === 1) {
	$response['message'] = "Challenge deleted.";
	http_response_code(HTTP['OK']);
} else if ($affectedRows === 0) {
	$response['error'] = "No challenge with challengeIndex [$challengeIndex].";
	http_response_code(HTTP['BAD_REQUEST']);
} else {
	$response['error'] = "Unable to delete challenge.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
