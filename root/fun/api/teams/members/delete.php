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

$names = trim($_POST['names']);
$teamIndex = $_POST['teamIndex'];

$hasNames = isset($names) && is_string($names) && !empty($names);
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasNames) {
	$response['error'] = "Missing required field 'names'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Delete the team members (one at a time to support duplicates)
$namesArr = explode(",", $names);
$total = count($namesArr);
$deleted = 0;
$failedNames = [];
foreach($namesArr as $name) {
	$name = trim($name);
	if (empty($name)) continue;

	$query = "DELETE FROM teamMembers WHERE teamIndex = ? AND name = ? LIMIT 1";
	$affectedRows = Sql::executeSqlForAffectedRows($query, 'is', $teamIndex, $name);
	if ($affectedRows === 1) {
		$deleted++;
	} else {
		$failedNames[] = $name;
	}
}

// Evaluate the status
if ($deleted === $total) {
	$response['message'] = "Team members deleted.";
	Http::responseCode('OK');
} else if ($deleted === 0) {
	$response['error'] = "Unable to delete members";
	Http::responseCode('INTERNAL_SERVER_ERROR');
} else {
	$response['message'] = "Team members deleted. [$deleted of $total]";
	$response['data'] = [
			'failedNames' => $failedNames
	];
	Http::responseCode('OK');
}
echo json_encode($response);
