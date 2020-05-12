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

$teamIndex = $_POST['teamIndex'];
$name = $_POST['name'];
$score = $_POST['score'];
$members = $_POST['members'];

$hasTeamIndex = isset($teamIndex) && !is_nan($teamIndex);
$hasName = isset($name) && is_string($name) && !empty($name);
$hasScore = isset($score) && !is_nan($score);
$hasMembers = isset($members) && is_string($members);

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!$hasName && !$hasScore && !$hasMembers) {
	$response['error'] = "No change fields.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

try {
	// Handle members updates
	if ($hasMembers) {
		$membersArr = explode(",", $members);
		if (sizeof($membersArr) === 0) {
			// Delete the previous members
			executeSql($mysqli, "DELETE FROM teamMembers WHERE teamIndex = ?", 'i', $teamIndex);
		} else if (sizeof($membersArr) > 0) {
			// Build SQL pieces
			$valuesStr = "";
			$types = "";
			$params = [];
			foreach($membersArr as $memberName) {
				// Validate each name
				if (preg_match("[,<>()&]", $memberName)) {
					$response['error'] = "One of the members contains invalid special characters [$memberName].";
					http_response_code(HTTP['BAD_REQUEST']);
					echo json_encode($response);
					return;
				}

				$params[] = trim($memberName);
				$params[] = $teamIndex;
				$types .= 'si';
				if (!empty($valuesStr)) $valuesStr .= ",";
				$valuesStr .= "(?,?)";
			}

			// Delete the previous members
			executeSql($mysqli, "DELETE FROM teamMembers WHERE teamIndex = ?", 'i', $teamIndex);

			// Add the updated members
			$query = "INSERT INTO teamMembers (name, teamIndex) VALUES $valuesStr";
			$affectedMemberRows = executeSqlForAffectedRows($mysqli, $query, $types, ...$params);
			if ($affectedMemberRows === 0) {
				$response['error'] = "Unable to update the team members.";
				http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
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
	$affectedRows = executeSqlForAffectedRows($mysqli, $query, $types, ...$params);
	if ($affectedRows === 1 || $affectedRows === 0) {
		$response['message'] = "Team updated.";
		http_response_code(HTTP['OK']);

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

		// Return the updated teams
		$response['data'] = $teams;
	} else {
		$response['error'] = "Unable to update team.";
		http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
