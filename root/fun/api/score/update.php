<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Score as Score;
use dao\Teams as Teams;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isGameAdmin) {
		$response['error'] = "You are not an admin! GTFO.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Validate input
	$challengeIndex = Param::asInteger($_POST['challengeIndex']);
	$teamIndex = Param::asInteger($_POST['teamIndex']);
	$delta = Param::asInteger($_POST['delta']);
	if (!Teams::isValidTeamIndex($teamIndex)) {
		$response['error'] = "Missing required field 'teamIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (is_null($delta)) {
		$response['error'] = "Missing required field 'delta'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if ($delta === 0) {
		$response['error'] = "Invalid value for 'delta'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
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
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
