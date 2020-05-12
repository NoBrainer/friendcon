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

$name = trim($_POST['name']);
$hasName = isset($name) && is_string($name) && !empty($name);

if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Make sure the name is unique
$result = executeSqlForResult($mysqli, "SELECT * FROM teams WHERE name = ?", 's', $name);
if ($result->num_rows > 0) {
	$response['error'] = "There's already a team with that name [$name].";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Make the changes
$query = "INSERT INTO teams(name) VALUES (?)";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, 's', $name);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create team.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
	return;
}

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

// Return the updated teams
$response['data'] = $teams;
$response['message'] = "Team created.";
http_response_code(HTTP['OK']);
echo json_encode($response);
