<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Teams as Teams;
use fun\classes\util\Http as Http;
use fun\classes\util\Param as Param;
use fun\classes\util\Session as Session;

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
	$names = isset($_POST['names']) ? Param::asString($_POST['names']) : null;
	$teamIndex = isset($_POST['teamIndex']) ? Param::asInteger($_POST['teamIndex']) : null;
	if (!Teams::isValidTeamIndex($teamIndex)) {
		$response['error'] = "Missing required field 'teamIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($names)) {
		$response['error'] = "Missing required field 'names'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Delete the team members
	$namesArr = explode(",", $names);
	$deleteResponse = Teams::deleteMembers($teamIndex, $namesArr);
	$deleteCount = $deleteResponse['deleteCount'];
	$failedNames = $deleteResponse['failedNames'];
	$total = $deleteResponse['total'];

	// Evaluate the status
	if ($deleteCount === $total) {
		$response['message'] = "Team members deleted.";
		Http::responseCode('OK');
	} else if ($deleteCount === 0) {
		$response['error'] = "Unable to delete members";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	} else {
		$response['message'] = "Team members deleted. [$deleteCount of $total]";
		$response['data'] = [
				'failedNames' => $failedNames
		];
		Http::responseCode('OK');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
