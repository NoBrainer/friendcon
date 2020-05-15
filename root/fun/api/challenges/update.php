<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
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
$name = $_POST['name'];
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];

$hasChallengeIndex = isset($challengeIndex) && !is_nan($challengeIndex);
$hasName = isset($name) && is_string($name) && !empty($name);
$hasStartTime = isset($startTime) && (is_string($startTime) || is_null($startTime));
$hasEndTime = isset($endTime) && (is_string($endTime) || is_null($endTime));

// Input validation
if (!$hasChallengeIndex) {
	$response['error'] = "Missing required field 'challengeIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasName && !$hasStartTime && !$hasEndTime) {
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
echo json_encode($response);
