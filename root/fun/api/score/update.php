<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Score as Score;
use fun\classes\dao\Teams as Teams;
use fun\classes\util\Http as Http;
use fun\classes\util\Param as Param;
use fun\classes\util\Session as Session;

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
	$challengeIndex = isset($_POST['challengeIndex']) ? Param::asInteger($_POST['challengeIndex']) : null;
	$teamIndex = isset($_POST['teamIndex']) ? Param::asInteger($_POST['teamIndex']) : null;
	$delta = isset($_POST['delta']) ? Param::asInteger($_POST['delta']) : null;
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
