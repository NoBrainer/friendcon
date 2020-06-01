<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
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
	$challengeIndex = isset($_POST['challengeIndex']) ? Param::asInteger($_POST['challengeIndex']) : null;
	$name = isset($_POST['name']) ? Param::asString($_POST['name']) : null;
	$hasChanges = Param::isPopulatedString($name) || isset($_POST['startTime']) || isset($_POST['endTime']);
	if (!Challenges::isValidChallengeIndex($challengeIndex)) {
		$response['error'] = "Missing required field 'challengeIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!$hasChanges) {
		$response['error'] = "No change fields.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the challenge exists
	if (!Challenges::exists($challengeIndex)) {
		$response['error'] = "No challenge with challengeIndex [$challengeIndex].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make the changes
	$successful = Challenges::update($challengeIndex, $name, $startTime, $endTime);
	if ($successful) {
		// Return the updated challenge
		$response['data'] = Challenges::get($challengeIndex);
		$response['message'] = "Challenge updated.";
		Http::responseCode('OK');
	} else {
		Http::responseCode('NOT_MODIFIED');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
