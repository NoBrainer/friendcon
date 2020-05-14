<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

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
$published = $_POST['published'];

$hasChallengeIndex = isset($challengeIndex) && !is_nan($challengeIndex);
$hasPublished = General::isBooleanSet($published);

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

// Make the change
$query = "UPDATE challenges SET published = ? WHERE challengeIndex = ?";
$affectedRows = Sql::executeSqlForAffectedRows($query, 'ii', General::getBooleanValue($published), intval($challengeIndex));
if ($affectedRows === 1) {
	$response['message'] = "Challenge published.";
	Http::responseCode('OK');
} else if ($affectedRows === 0) {
	Http::responseCode('NOT_MODIFIED');
} else {
	$response['error'] = "Unable to publish challenge [$challengeIndex].";
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
