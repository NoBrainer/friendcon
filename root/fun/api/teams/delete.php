<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

if (!Session::$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	Http::responseCode('FORBIDDEN');
	echo json_encode($response);
	return;
}

$teamIndex = $_POST['teamIndex'];
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Prevent deleting teams with approved uploads
$query = "SELECT * FROM uploads WHERE teamIndex = ? AND state > 0";
$result = Sql::executeSqlForResult($query, 'i', $teamIndex);
if ($result->num_rows > 0) {
	$response['error'] = "Cannot delete a team with approved uploads.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Prevent deleting teams with members
$query = "SELECT * FROM teamMembers WHERE teamIndex = ?";
$result = Sql::executeSqlForResult($query, 'i', $teamIndex);
if ($result->num_rows > 0) {
	$response['error'] = "Cannot delete a team with members. Delete them first.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Delete the team
$query = "DELETE FROM teams WHERE teamIndex = ?";
$affectedRows = Sql::executeSqlForAffectedRows($query, 'i', $teamIndex);
if ($affectedRows === 1) {
	$response['message'] = "Team deleted.";
	Http::responseCode('OK');
} else if ($affectedRows === 0) {
	$response['error'] = "No team found with teamIndex [$teamIndex].";
	Http::responseCode('NOT_FOUND');
} else {
	$response['error'] = "Unable to delete team with teamIndex [$teamIndex].";
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
