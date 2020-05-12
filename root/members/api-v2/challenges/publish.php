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
$published = $_POST['published'];

$hasChallengeIndex = isset($challengeIndex) && !is_nan($challengeIndex);
$hasPublished = isBooleanSet($published);

// Validate input
if (!$hasChallengeIndex) {
	$response['error'] = "Missing required field 'challengeIndex'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!$hasPublished) {
	$response['error'] = "Missing required field 'published'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Make the change
$query = "UPDATE challenges SET published = ? WHERE challengeIndex = ?";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, 'ii', getBooleanValue($published), intval($challengeIndex));
if ($affectedRows === 1) {
	$response['message'] = "Challenge published.";
	http_response_code(HTTP['OK']);
} else if ($affectedRows === 0) {
	http_response_code(HTTP['NOT_MODIFIED']);
} else {
	$response['error'] = "Unable to publish challenge [$challengeIndex].";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
