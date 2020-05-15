<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
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

$name = $_POST['name'];
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];

$hasName = isset($name) && is_string($name) && !empty($name);
$hasStartTime = isset($startTime) && (is_string($startTime) || is_null($startTime));
$hasEndTime = isset($endTime) && (is_string($endTime) || is_null($endTime));

// Input validation
if (!$hasName) {
	$response['error'] = "Missing required field 'name'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
if ($hasStartTime) $startTime = null;
if ($hasEndTime) $endTime = null;

// Make sure the name is unique
if (Challenges::existsWithName($name)) {
	$response['error'] = "There's already a challenge with that name.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make the changes
$successful = Challenges::add($name, $startTime, $endTime);
if (!$successful) {
	$response['error'] = "Unable to create challenge.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Return the new challenge
$response['data'] = Challenges::getByName($name);
$response['message'] = "Challenge created.";
Http::responseCode('OK');
echo json_encode($response);
