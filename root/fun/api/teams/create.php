<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Teams as Teams;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

// Only allow POST request method
if (Http::return404IfNotPost()) exit;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

if (!Session::$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	Http::responseCode('FORBIDDEN');
	echo json_encode($response);
	return;
}

// Validate input
$name = $_POST['name'];
if (Param::isBlankString($name)) {
	$response['error'] = "Missing required field 'name'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
$name = trim($name);

// Make sure the name is unique
if (Teams::existsWithName($name)) {
	$response['error'] = "There's already a team with that name [$name].";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make the changes
$successful = Teams::add($name);
if (!$successful) {
	$response['error'] = "Unable to create team [$name].";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Get the updated teams
$updatedTeams = Teams::getAll();

// Return the updated teams
$response['data'] = $updatedTeams;
$response['message'] = "Team created [$name].";
Http::responseCode('OK');
echo json_encode($response);
