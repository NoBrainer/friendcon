<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/initDB.php');
include('../internal/constants.php');
include('../internal/functions.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Get the submit data
$targetUid = $userSession;
$sourceUid = trim($_POST['source_uid']);

if (!$targetUid) {
	$response['error'] = "Login is required";
	http_response_code(HTTP['NOT_AUTHORIZED']);
	echo json_encode($response);
	return;
}

if (!$sourceUid) {
	$response['error'] = "source_uid is required";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Check if the request exists
$query = "SELECT req.status_id" .
		" FROM points_request req" .
		" WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
$result = executeSqlForResult($mysqli, $query, 'ii', $targetUid, $sourceUid);
if (!hasRows($result)) {
	$response['error'] = "Rejecting request failed [DB-1]";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
	return;
}
$checkRequest = getNextRow($result);
$statusId = $checkRequest['status_id'];
if (!isset($statusId)) {
	$response['error'] = "Request does not exist";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Update the status id to REJECTED(2)
$updateQuery = "UPDATE points_request req" .
		" SET status_id = 2" .
		" WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
$info = executeSqlForInfo($mysqli, $updateQuery, 'ii', $targetUid, $sourceUid);
if ($info["matched"] > 0) {
	http_response_code(HTTP['OK']);
} else {
	$response['error'] = "Error rejecting request [DB-2]";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}

// Return the JSON
echo json_encode($response);
