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
$teamIndex = $_POST['teamIndex'];
$delta = $_POST['delta'];

$hasChallengeIndex = isset($challengeIndex) && is_numeric($challengeIndex) && $challengeIndex >= 0;
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;
$hasDelta = isset($delta) && is_numeric($delta);

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasDelta) {
	$response['error'] = "Missing required field 'delta'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (intval($delta) === 0) {
	$response['error'] = "Invalid value for 'delta'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Build the SQL pieces
$fields = ['teamIndex', 'delta'];
$vals = ['?', '?'];
$types = 'ii';
$params = [$teamIndex, $delta];
if ($hasChallengeIndex) {
	$fields[] = "challengeIndex";
	$vals[] = "?";
	$types .= 'i';
	$params[] = "$challengeIndex";
}
$fieldStr = join(", ", $fields);
$valStr = join(", ", $vals);

// Make the changes
$query = "INSERT INTO scoreChanges ($fieldStr) VALUES ($valStr)";
$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create change log entry for score.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Update the team score to reflect the change
$query = "UPDATE teams SET score = score + ? WHERE teamIndex = ?";
$result = Sql::executeSqlForResult($query, 'ii', $delta, $teamIndex);

// Get the updated change log entries
$result = Sql::executeSqlForResult("SELECT * FROM scoreChanges");
$entries = [];
while ($row = Sql::getNextRow($result)) {
	$entry = [
			'updateTime'     => General::stringToDate($row['updateTime']),
			'teamIndex'      => intval($row['teamIndex']),
			'delta'          => intval($row['delta']),
			'challengeIndex' => null
	];
	if (!is_null($row['challengeIndex'] && is_numeric($row['challengeIndex']))) {
		$entry['challengeIndex'] = intval($row['challengeIndex']);
	}

	$entries[] = $entry;
}

// Get the updated teams
$teams = [];
$result = Sql::executeSqlForResult("SELECT * FROM teams");
while ($row = Sql::getNextRow($result)) {
	$teams[] = [
			'teamIndex'  => intval($row['teamIndex']),
			'name'       => "" . $row['name'],
			'score'      => intval($row['score']),
			'updateTime' => General::stringToDate($row['updateTime']),
			'members'    => []
	];
}

$response['data'] = [
		'scoreChanges' => $entries,
		'teams'        => $teams
];
$response['message'] = "Updated score change log.";
Http::responseCode('OK');
echo json_encode($response);
