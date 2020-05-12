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

$teamIndex = $_POST['teamIndex'];
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Prevent deleting teams with approved uploads
$query = "SELECT * FROM uploads WHERE teamIndex = ? AND state > 0";
$result = executeSqlForResult($mysqli, $query, 'i', $teamIndex);
if ($result->num_rows > 0) {
	$response['error'] = "Cannot delete a team with approved uploads.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Prevent deleting teams with members
$query = "SELECT * FROM teamMembers WHERE teamIndex = ?";
$result = executeSqlForResult($mysqli, $query, 'i', $teamIndex);
if ($result->num_rows > 0) {
	$response['error'] = "Cannot delete a team with members.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Delete the team
$query = "DELETE FROM teams WHERE teamIndex = ?";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, 'i', $teamIndex);
if ($affectedRows === 1) {
	$response['message'] = "Team deleted.";
	http_response_code(HTTP['OK']);
} else if ($affectedRows === 0) {
	$response['error'] = "No team found with teamIndex [$teamIndex].";
	http_response_code(HTTP['NOT_FOUND']);
} else {
	$response['error'] = "Unable to delete team with teamIndex [$teamIndex].";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
