<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Get the teams
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

// Add the members to the teams
$result = $mysqli->query("SELECT * FROM teamMembers ORDER BY name ASC");
while ($row = getNextRow($result)) {
	$memberName = "" . $row['name'];
	$teamIndex = intval($row['teamIndex']);

	// Add the member name to the team's members
	$key = array_search($teamIndex, array_column($teams, 'teamIndex'));
	$teams[$key]['members'][] = $memberName;
}

$response['data'] = $teams;
http_response_code(HTTP['OK']);
echo json_encode($response);
