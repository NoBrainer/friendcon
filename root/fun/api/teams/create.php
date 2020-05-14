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

$name = trim($_POST['name']);
$hasName = isset($name) && is_string($name) && !empty($name);

if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make sure the name is unique
$result = Sql::executeSqlForResult("SELECT * FROM teams WHERE name = ?", 's', $name);
if ($result->num_rows > 0) {
	$response['error'] = "There's already a team with that name [$name].";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make the changes
$query = "INSERT INTO teams(name) VALUES (?)";
$affectedRows = Sql::executeSqlForAffectedRows($query, 's', $name);
if ($affectedRows !== 1) {
	$response['error'] = "Unable to create team.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Get the teams
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

// Return the updated teams
$response['data'] = $teams;
$response['message'] = "Team created.";
Http::responseCode('OK');
echo json_encode($response);
