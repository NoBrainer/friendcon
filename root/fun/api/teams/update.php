<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Teams as Teams;
use util\Http as Http;
use util\Session as Session;

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
if (!$hasName) $name = null;
if (!$hasScore) $score = null;

try {
	// Handle members updates
	if ($hasMembers) {
		if (empty($members)) {
			Teams::deleteAllMembers($teamIndex);
		} else {
			$membersArr = explode(",", $members);
			$invalidNames = Teams::getInvalidMemberNames($membersArr);
			if (sizeof($invalidNames) > 0) {
				$response['error'] = "One or more of the members contains invalid special characters.";
				$response['invalidNames'] = $invalidNames;
				Http::responseCode('BAD_REQUEST');
				echo json_encode($response);
				return;
			}

			$successful = Teams::setMembers($teamIndex, $membersArr);
			if (!$successful) {
				$response['error'] = "Unable to update the team members.";
				Http::responseCode('INTERNAL_SERVER_ERROR');
				echo json_encode($response);
				return;
			}
		}
	}

	$successful = Teams::update($teamIndex, $name, $score);
	if ($successful) {
		$response['message'] = "Team updated.";
		Http::responseCode('OK');

		// Get the updated teams
		$updatedTeams = Teams::getAll();

		// Return the updated teams
		$response['data'] = $updatedTeams;
	} else {
		$response['error'] = "Unable to update team.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
