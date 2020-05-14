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

$name = $_POST['name'];
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];

$hasName = isset($name) && is_string($name) && !empty($name);
$hasStartTime = isset($startTime) && (is_string($startTime) || is_null($startTime));
$hasEndTime = isset($endTime) && (is_string($endTime) || is_null($endTime));

// Input validation
if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make sure the name is unique
$result = Sql::executeSqlForResult("SELECT * FROM challenges WHERE name = ?", 's', $name);
if ($result->num_rows > 0) {
	$response['error'] = "There's already a challenge with that name.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Build the SQL pieces
$fields = [];
$vals = [];
$types = '';
$params = [];
if ($hasName) {
	$fields[] = "name";
	$vals[] = "?";
	$types .= 's';
	$params[] = "$name";
}
if ($hasStartTime) {
	$fields[] = "startTime";
	$vals[] = "?";
	$types .= 's';
	$params[] = General::stringToDate($startTime);
}
if ($hasEndTime) {
	$fields[] = "endTime";
	$vals[] = "?";
	$types .= 's';
	$params[] = General::stringToDate($endTime);
}
$fieldStr = join(", ", $fields);
$valStr = join(", ", $vals);

// Make the changes
$query = "INSERT INTO challenges ($fieldStr) VALUES ($valStr)";
$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create challenge.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Get the new challenge
$query = "SELECT * FROM challenges WHERE name = ?";
$result = Sql::executeSqlForResult($query, 's', $name);
$row = Sql::getNextRow($result);
$response['data'] = [
		'challengeIndex' => intval($row['challengeIndex']),
		'name'           => "" . $row['name'],
		'startTime'      => General::stringToDate($row['startTime']),
		'endTime'        => General::stringToDate($row['endTime']),
		'published'      => boolval($row['published'])
];
$response['message'] = "Challenge created.";
Http::responseCode('OK');
echo json_encode($response);
