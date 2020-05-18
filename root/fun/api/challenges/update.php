<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
use util\Http as Http;
use util\Param as Param;
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

// Validate input
$challengeIndex = Param::asInteger($_POST['challengeIndex']);
$name = $_POST['name'];
$hasName = Param::isPopulatedString($name);
$hasStartTime = isset($_POST['startTime']);
$hasEndTime = isset($_POST['endTime']);
if ($hasStartTime) $startTime = Param::asTimestamp($_POST['startTime']);
if ($hasEndTime) $endTime = Param::asTimestamp($_POST['endTime']);
if (!Challenges::isValidChallengeIndex($challengeIndex)) {
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
