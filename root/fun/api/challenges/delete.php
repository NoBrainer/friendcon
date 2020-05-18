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
if (!Challenges::isValidChallengeIndex($challengeIndex)) {
	$response['error'] = "Missing required field 'challengeIndex'.";
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

// Prevent deleting challenges with approved uploads
if (Challenges::hasApprovedUploads($challengeIndex)) {
	$response['error'] = "Cannot delete a challenge with approved uploads.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Delete the challenge
$successful = Challenges::delete($challengeIndex);
if ($successful) {
	$response['message'] = "Challenge deleted.";
	Http::responseCode('OK');
} else {
	$response['error'] = "Unable to delete challenge.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
