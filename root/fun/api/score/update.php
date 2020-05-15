<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Score as Score;
use dao\Teams as Teams;
use util\Http as Http;
use util\Session as Session;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

if (!Session::$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	Http::responseCode('FORBIDDEN');
	echo json_encode($response);
	return;
}

$challengeIndex = $_POST['challengeIndex'];
$teamIndex = $_POST['teamIndex'];
$delta = $_POST['delta'];

$hasChallengeIndex = isset($challengeIndex) && is_numeric($challengeIndex) && $challengeIndex >= 0;
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;
$hasDelta = isset($delta) && is_numeric($delta);

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasDelta) {
	$response['error'] = "Missing required field 'delta'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (intval($delta) === 0) {
	$response['error'] = "Invalid value for 'delta'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
if (!$hasChallengeIndex) {
	$challengeIndex = null;
}

// Make the changes
$successful = Score::update($teamIndex, $delta, $challengeIndex);
if (!$successful) {
	$response['error'] = "Unable to create change log entry for score.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Get the updated change log entries and teams
$updatedChangeLogEntries = Score::getChangeLogEntries();
$updatedTeams = Teams::getAll();

$response['data'] = [
		'scoreChanges' => $updatedChangeLogEntries,
		'teams'        => $updatedTeams
];
$response['message'] = "Updated score change log.";
Http::responseCode('OK');
echo json_encode($response);
