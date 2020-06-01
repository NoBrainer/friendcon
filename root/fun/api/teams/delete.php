<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Teams as Teams;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

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
	$teamIndex = isset($_POST['teamIndex']) ? Param::asInteger($_POST['teamIndex']) : null;
	if (!Teams::isValidTeamIndex($teamIndex)) {
		$response['error'] = "Missing required field 'teamIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Teams::exists($teamIndex)) {
		$response['error'] = "No team found with teamIndex [$teamIndex].";
		Http::responseCode('NOT_FOUND');
		echo json_encode($response);
		return;
	}

	// Prevent deleting teams with approved uploads
	if (Teams::hasApprovedUploads($teamIndex)) {
		$response['error'] = "Cannot delete a team with approved uploads.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Prevent deleting teams with members
	if (Teams::hasMembers($teamIndex)) {
		$response['error'] = "Cannot delete a team with members. Delete them first.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Delete the team
	$successful = Teams::delete($teamIndex);
	if ($successful) {
		$response['message'] = "Team deleted.";
		Http::responseCode('OK');
	} else {
		$response['error'] = "Unable to delete team with teamIndex [$teamIndex].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
