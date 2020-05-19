<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

// Only allow POST request method
if (Http::return404IfNotPost()) exit;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

if (!Session::$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	Http::responseCode('FORBIDDEN');
	echo json_encode($response);
	return;
}

// Validate input
$challengeIndex = Param::asInteger($_POST['challengeIndex']);
$isPublished = Param::asBoolean($_POST['published']);
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
	$response['message'] = "Challenge published.";
	Http::responseCode('OK');
} else {
	Http::responseCode('NOT_MODIFIED');
}
echo json_encode($response);
