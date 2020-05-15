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

$names = trim($_POST['names']);
$teamIndex = $_POST['teamIndex'];

$hasNames = isset($names) && is_string($names) && !empty($names);
$hasTeamIndex = isset($teamIndex) && is_numeric($teamIndex) && $teamIndex >= 0;

// Input validation
if (!$hasTeamIndex) {
	$response['error'] = "Missing required field 'teamIndex'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasNames) {
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
echo json_encode($response);
