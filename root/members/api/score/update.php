<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');
include('../internal/checkAdmin.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

if (!isset($userSession) || $userSession == "" || !$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	http_response_code(HTTP['FORBIDDEN']);
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
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!$hasDelta) {
	$response['error'] = "Missing required field 'delta'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (intval($delta) === 0) {
	$response['error'] = "Invalid value for 'delta'.";
	http_response_code(HTTP['BAD_REQUEST']);
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
$affectedRows = executeSqlForAffectedRows($mysqli, $query, $types, ...$params);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create change log entry for score.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
	return;
}

// Update the team score to reflect the change
$query = "UPDATE teams SET score = score + ? WHERE teamIndex = ?";
$result = executeSqlForResult($mysqli, $query, 'ii', $delta, $teamIndex);

// Get the updated change log entries
$result = $mysqli->query("SELECT * FROM scoreChanges");
$entries = [];
while ($row = getNextRow($result)) {
	$entry = [
			'updateTime'     => stringToDate($row['updateTime']),
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
$result = $mysqli->query("SELECT * FROM teams");
while ($row = getNextRow($result)) {
	$teams[] = [
			'teamIndex'  => intval($row['teamIndex']),
			'name'       => "" . $row['name'],
			'score'      => intval($row['score']),
			'updateTime' => stringToDate($row['updateTime']),
			'members'    => []
	];
}

$response['data'] = [
		'scoreChanges' => $entries,
		'teams'        => $teams
];
$response['message'] = "Updated score change log.";
http_response_code(HTTP['OK']);
echo json_encode($response);
