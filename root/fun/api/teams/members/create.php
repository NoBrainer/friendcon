<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Teams as Teams;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;
use util\Sql as Sql;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isGameAdmin) {
		$response['error'] = "You are not an admin! GTFO.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Validate input
	$name = isset($_POST['name']) ? Param::asString($_POST['name']) : null;
	$teamIndex = isset($_POST['teamIndex']) ? Param::asInteger($_POST['teamIndex']) : null;
	if (Param::isBlankString($name)) {
		$response['error'] = "Missing required field 'name'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Teams::isValidMemberName($name)) {
		$response['error'] = "Field 'name' contains invalid special characters.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Randomly pick a team if one is not set
	if (!Teams::isValidTeamIndex($teamIndex)) {
		if (!Teams::isSetup()) {
			$response['error'] = "Must setup teams before adding members.";
			Http::responseCode('BAD_REQUEST');
			echo json_encode($response);
			return;
		}

		// Randomly pick a team (within a threshold based on team member count)
		$teamIndex = Teams::getRandomTeamIndex();
	}

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
				'teamIndex'  => Param::asInteger($row['teamIndex']),
				'name'       => Param::asString($row['name']),
				'score'      => Param::asInteger($row['score']),
				'updateTime' => Param::asTimestamp($row['updateTime']),
				'members'    => []
		];

		// Save the team name for the response message
		if ($member['teamIndex'] === Param::asInteger($row['teamIndex'])) {
			$member['teamName'] = Param::asString($row['name']);
		}
	}

	// Add the members to the teams
	$result = Sql::executeSqlForResult("SELECT * FROM teamMembers ORDER BY name ASC");
	while ($row = Sql::getNextRow($result)) {
		$memberName = Param::asString($row['name']);
		$teamIndex = Param::asInteger($row['teamIndex']);

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
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
