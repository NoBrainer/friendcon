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

$name = trim($_POST['name']);
$hasName = isset($name) && is_string($name) && !empty($name);

if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

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
	$response['error'] = "Unable to create team.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Get the updated teams
$updatedTeams = Teams::getAll();

// Return the updated teams
$response['data'] = $updatedTeams;
$response['message'] = "Team created.";
Http::responseCode('OK');
echo json_encode($response);
