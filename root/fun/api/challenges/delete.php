<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

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
$hasChallengeIndex = isset($challengeIndex) && is_string($challengeIndex) && !empty($challengeIndex);

// Input validation
if (!$hasChallengeIndex) {
	$response['error'] = "Missing required field 'challengeIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Prevent deleting challenges with approved uploads
$query = "SELECT * FROM uploads WHERE challengeIndex = ? AND state > 0";
$result = Sql::executeSqlForResult($query, 'i', $challengeIndex);
if ($result->num_rows > 0) {
	$response['error'] = "Cannot delete a challenge with approved uploads.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Delete the challenge
$query = "DELETE FROM challenges WHERE challengeIndex = ?";
$affectedRows = Sql::executeSqlForAffectedRows($query, 'i', $challengeIndex);
if ($affectedRows === 1) {
	$response['message'] = "Challenge deleted.";
	Http::responseCode('OK');
} else if ($affectedRows === 0) {
	$response['error'] = "No challenge with challengeIndex [$challengeIndex].";
	Http::responseCode('BAD_REQUEST');
} else {
	$response['error'] = "Unable to delete challenge.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
