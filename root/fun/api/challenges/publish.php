<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
use util\General as General;
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
$isPublished = $_POST['published'];

$hasChallengeIndex = isset($challengeIndex) && !is_nan($challengeIndex);
$hasPublished = General::isBooleanSet($isPublished);

// Validate input
if (!$hasChallengeIndex) {
	$response['error'] = "Missing required field 'challengeIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasPublished) {
	$response['error'] = "Missing required field 'published'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
$challengeIndex = intval($challengeIndex);
$isPublished = General::getBooleanValue($isPublished);

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
