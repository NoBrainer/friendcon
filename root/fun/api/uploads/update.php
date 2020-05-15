<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Uploads as Uploads;
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

$file = $_POST['file'];
$state = $_POST['state'];

$hasFile = isset($file) && is_string($file) && !empty($file);
$hasState = isset($state) && is_string($state) && !empty($state);

// Validate input
if (!$hasFile) {
	$response['error'] = "Missing required field 'file'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!$hasState) {
	Http::responseCode('NOT_MODIFIED');
	return;
} else if (!Uploads::exists($file)) {
	$response['error'] = "No file found.";
	Http::responseCode('NOT_FOUND');
	echo json_encode($response);
	return;
}

// Get the value of the state string
$stateValue = Uploads::getStateValue($state);
if (is_null($stateValue)) {
	$response['error'] = "Invalid value provided for 'state'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make the change
$successful = Uploads::updateState($file, $stateValue);
if ($successful) {
	$response['message'] = "Set file [$file] to $state.";
	Http::responseCode('OK');
} else {
	$response['error'] = "Unexpected error occurred.";
	Http::responseCode('INTERNAL_SERVER_ERROR');
}

// Send the JSON
echo json_encode($response);
