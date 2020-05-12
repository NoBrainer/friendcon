<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../../internal/constants.php');
include('../../internal/functions.php');
include('../../internal/initDB.php');
include('../../internal/checkAdmin.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

if (!isset($userSession) || $userSession == "" || !$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	http_response_code(HTTP['FORBIDDEN']);
	echo json_encode($response);
	return;
}

$names = trim($_POST['names']);
$teamIndex = $_POST['teamIndex'];

$hasNames = isset($names) && is_string($names) && !empty($names);
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!$hasNames) {
	$response['error'] = "Missing required field 'names'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Delete the team members (one at a time to support duplicates)
$namesArr = explode(",", $names);
$total = count($namesArr);
$deleted = 0;
$failedNames = [];
foreach($namesArr as $name) {
	$name = trim($name);
	if (empty($name)) continue;

	$query = "DELETE FROM teamMembers WHERE teamIndex = ? AND name = ? LIMIT 1";
	$affectedRows = executeSqlForAffectedRows($mysqli, $query, 'is', $teamIndex, $name);
	if ($affectedRows === 1) {
		$deleted++;
	} else {
		$failedNames[] = $name;
	}
}

// Evaluate the status
if ($deleted === $total) {
	$response['message'] = "Team members deleted.";
	http_response_code(HTTP['OK']);
} else if ($deleted === 0) {
	$response['error'] = "Unable to delete members";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
} else {
	$response['message'] = "Team members deleted. [$deleted of $total]";
	$response['data'] = [
			'failedNames' => $failedNames
	];
	http_response_code(HTTP['OK']);
}
echo json_encode($response);
