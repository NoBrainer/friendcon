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

// Build the SQL pieces
$changes = [];
$types = '';
$params = [];
if ($hasName) {
	$changes[] = "name = ?";
	$types .= 's';
	$params[] = "$name";
}
if ($hasStartTime) {
	$changes[] = "startTime = ?";
	$types .= 's';
	$params[] = General::stringToDate($startTime);
}
if ($hasEndTime) {
	$changes[] = "endTime = ?";
	$types .= 's';
	$params[] = General::stringToDate($endTime);
}
$changesStr = join(", ", $changes);
$types .= 'i';
$params[] = $challengeIndex;

// Make the changes
$query = "UPDATE challenges SET $changesStr WHERE challengeIndex = ?";
$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
if ($affectedRows === 1) {
	$response['message'] = "Challenge updated.";
	Http::responseCode('OK');

	// Return the updated challenge
	$result = Sql::executeSqlForResult("SELECT * FROM challenges WHERE challengeIndex = ?", 'i', $challengeIndex);
	$row = Sql::getNextRow($result);
	$response['data'] = [
			'challengeIndex' => intval($row['challengeIndex']),
			'startTime'      => General::stringToDate($row['startTime']),
			'endTime'        => General::stringToDate($row['endTime']),
			'name'           => "" . $row['name'],
			'published'      => boolval($row['published'])
	];
} else if ($affectedRows === 0) {
	Http::responseCode('NOT_MODIFIED');
} else {
	$response['error'] = "Unable to update challenge.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
