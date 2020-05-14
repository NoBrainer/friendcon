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

$teamIndex = $_POST['teamIndex'];
$name = $_POST['name'];
$score = $_POST['score'];
$members = $_POST['members'];

$hasTeamIndex = isset($teamIndex) && !is_nan($teamIndex);
$hasName = isset($name) && is_string($name) && !empty($name);
$hasScore = isset($score) && !is_nan($score);
$hasMembers = isset($members);

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasName && !$hasScore && !$hasMembers) {
	$response['error'] = "No change fields.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

try {
	// Handle members updates
	if ($hasMembers) {
		$membersArr = empty($members) ? [] : explode(",", $members);
		if (sizeof($membersArr) === 0) {
			// Delete all members
			Sql::executeSql("DELETE FROM teamMembers WHERE teamIndex = ?", 'i', $teamIndex);
		} else {
			// Build SQL pieces
			$valuesStr = "";
			$types = "";
			$params = [];
			foreach($membersArr as $memberName) {
				// Validate each name
				if (preg_match("[,<>()&]", $memberName)) {
					$response['error'] = "One of the members contains invalid special characters [$memberName].";
					Http::responseCode('BAD_REQUEST');
					echo json_encode($response);
					return;
				}

				$params[] = trim($memberName);
				$params[] = $teamIndex;
				$types .= 'si';
				if (!empty($valuesStr)) $valuesStr .= ",";
				$valuesStr .= "(?, ?)";
			}

			// Delete the previous members
			Sql::executeSql("DELETE FROM teamMembers WHERE teamIndex = ?", 'i', $teamIndex);

			// Add the updated members
			$query = "INSERT INTO teamMembers (name, teamIndex) VALUES $valuesStr";
			$affectedMemberRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
			if ($affectedMemberRows === 0) {
				$response['error'] = "Unable to update the team members.";
				Http::responseCode('INTERNAL_SERVER_ERROR');
				echo json_encode($response);
				return;
			}
		}
	}

	// Build the SQL pieces
	$changes = [];
	$types = '';
	$params = [];
	if ($hasName) {
		$changes[] = "name = ?";
		$types .= 's';
		$params[] = "$name";
	}
	if ($hasScore) {
		$changes[] = "score = ?";
		$types .= 'i';
		$params[] = intval($score);
	}
	$changesStr = join(", ", $changes);
	$types .= 'i';
	$params[] = $teamIndex;

	// Make the changes
	$query = "UPDATE teams SET $changesStr WHERE teamIndex = ?";
	$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
	if ($affectedRows === 1 || $affectedRows === 0) {
		$response['message'] = "Team updated.";
		Http::responseCode('OK');

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

		// Return the updated teams
		$response['data'] = $teams;
	} else {
		$response['error'] = "Unable to update team.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
