<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
use util\Http as Http;
use util\Param as Param;
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

// Validate input
$name = $_POST['name'];
$startTime = Param::asTimestamp($_POST['startTime']);
$endTime = Param::asTimestamp($_POST['endTime']);
if (Param::isBlankString($name)) {
	$response['error'] = "Missing required field 'name'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
$name = trim($name);

// Make sure the name is unique
if (Challenges::existsWithName($name)) {
	$response['error'] = "There's already a challenge with that name [$name].";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make the changes
$successful = Challenges::add($name, $startTime, $endTime);
if (!$successful) {
	$response['error'] = "Unable to create challenge [$name].";
	Http::responseCode('INTERNAL_SERVER_ERROR');
	echo json_encode($response);
	return;
}

// Return the new challenge
$response['data'] = Challenges::getByName($name);
$response['message'] = "Challenge created [$name].";
Http::responseCode('OK');
echo json_encode($response);
