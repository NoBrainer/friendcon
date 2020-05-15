<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Teams as Teams;
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

$name = trim($_POST['name']);
$teamIndex = $_POST['teamIndex'];

$hasName = isset($name) && is_string($name) && !empty($name) && !empty(trim($name));
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;

if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
$name = trim($name);
if (!Teams::isValidMemberName($name)) {
	$response['error'] = "Field 'name' contains invalid special characters.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Randomly pick a team if one is not set
if (!$hasTeamIndex) {
	if (!Teams::isSetup()) {
		$response['error'] = "Must setup teams before adding members.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Randomly pick a team (within a threshold based on team member count)
	$teamIndex = Teams::getRandomTeamIndex();
}
$teamIndex = intval($teamIndex);

// Save the member
$member = [
		'name'      => $name,
		'teamIndex' => $teamIndex
];

// Make the changes
$query = "INSERT INTO teamMembers (name, teamIndex) VALUES (?, ?)";
$affectedRows = Sql::executeSqlForAffectedRows($query, 'si', $name, $teamIndex);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create team member.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
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

	// Save the team name for the response message
	if ($member['teamIndex'] === intval($row['teamIndex'])) {
		$member['teamName'] = "" . $row['name'];
	}
}

// Add the members to the teams
$result = Sql::executeSqlForResult("SELECT * FROM teamMembers ORDER BY name ASC");
while ($row = Sql::getNextRow($result)) {
	$memberName = "" . $row['name'];
	$teamIndex = intval($row['teamIndex']);

	// Add the member name to the team's members
	$key = array_search($teamIndex, array_column($teams, 'teamIndex'));
	$teams[$key]['members'][] = $memberName;
}

// Return the update teams
$response['data'] = [
		'member' => $member,
		'teams'  => $teams
];
$response['message'] = sprintf("%s added to %s.", $member['name'], $member['teamName']);
Http::responseCode('OK');
echo json_encode($response);
