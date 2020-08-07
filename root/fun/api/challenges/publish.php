<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Challenges as Challenges;
use fun\classes\util\{Http as Http, Param as Param, Session as Session};

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
	$isPublished = isset($_POST['published']) ? Param::asBoolean($_POST['published']) : null;
	if (!Challenges::isValidChallengeIndex($challengeIndex)) {
		$response['error'] = "Missing required field 'challengeIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (is_null($isPublished)) {
		$response['error'] = "Missing required field 'published'.";
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

	// Make the change
	$successful = Challenges::publish($challengeIndex, $isPublished);
	if ($successful) {
		$response['message'] = $isPublished ? "Challenge published." : "Challenge unpublished.";
		Http::responseCode('OK');
	} else {
		Http::responseCode('NOT_MODIFIED');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
