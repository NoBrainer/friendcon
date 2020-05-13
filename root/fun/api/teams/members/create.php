<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../../internal/constants.php');
include('../../internal/functions.php');
include('../../internal/initDB.php');
include('../../internal/checkAdmin.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

if (!isset($userSession) || $userSession == "" || !$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	http_response_code(HTTP['FORBIDDEN']);
	echo json_encode($response);
	return;
}

$name = trim($_POST['name']);
$teamIndex = $_POST['teamIndex'];

$hasName = isset($name) && is_string($name) && !empty($name) && !empty(trim($name));
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;

if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}
$name = trim($name);
if (preg_match('/[,<>()]/', $name)) {
	$response['error'] = "Field 'name' contains invalid special characters.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Randomly pick a team if one is not set
if (!$hasTeamIndex) {
	// Get the teams with member counts and figure out the least members on a single team
	$teams = [];
	$minMemberCount = 9001;
	$result = $mysqli->query("SELECT *, (SELECT COUNT(*) FROM teamMembers m WHERE m.teamIndex = t.teamIndex) AS memberCount FROM teams t");
	if ($result->num_rows === 0) {
		$response['error'] = "Must setup teams before adding members.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}
	while ($row = getNextRow($result)) {
		$memberCount = intval($row['memberCount']);
		$minMemberCount = min($minMemberCount, $memberCount);
		$teams[] = [
				'teamIndex'   => intval($row['teamIndex']),
				'name'        => "" . $row['name'],
				'score'       => intval($row['score']),
				'updateTime'  => stringToDate($row['updateTime']),
				'memberCount' => $memberCount
		];
	}

	// Figure out which teams are candidates (less than 2 members more than the minimum)
	$teamCandidates = [];
	foreach($teams as $team) {
		if ($team['memberCount'] < $minMemberCount + 2) {
			$teamCandidates[] = $team;
		}
	}

	// Randomly pick one of the candidates
	$teamIndex = $teamCandidates[array_rand($teamCandidates)]['teamIndex'];
}
$teamIndex = intval($teamIndex);

// Save the member
$member = [
		'name'      => $name,
		'teamIndex' => $teamIndex
];

// Make the changes
$query = "INSERT INTO teamMembers(name, teamIndex) VALUES (?, ?)";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, 'si', $name, $teamIndex);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create team member.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
	return;
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

	// Save the team name for the response message
	if ($member['teamIndex'] === intval($row['teamIndex'])) {
		$member['teamName'] = "" . $row['name'];
	}
}

// Add the members to the teams
$result = $mysqli->query("SELECT * FROM teamMembers ORDER BY name ASC");
while ($row = getNextRow($result)) {
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
http_response_code(HTTP['OK']);
echo json_encode($response);
