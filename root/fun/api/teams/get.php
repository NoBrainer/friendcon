<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

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

// Add the members to the teams
$result = Sql::executeSqlForResult("SELECT * FROM teamMembers ORDER BY name ASC");
while ($row = Sql::getNextRow($result)) {
	$memberName = "" . $row['name'];
	$teamIndex = intval($row['teamIndex']);

	// Add the member name to the team's members
	$key = array_search($teamIndex, array_column($teams, 'teamIndex'));
	$teams[$key]['members'][] = $memberName;
}

$response['data'] = $teams;
Http::responseCode('OK');
echo json_encode($response);
