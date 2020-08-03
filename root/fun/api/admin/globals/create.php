<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Globals as Globals;
use fun\classes\util\Http as Http;
use fun\classes\util\Param as Param;
use fun\classes\util\Session as Session;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isSiteAdmin) {
		$response['error'] = "You must be a site admin to create global variables.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Validate input
	$name = isset($_POST['name']) ? Param::asString($_POST['name']) : null;
	$type = isset($_POST['type']) ? Param::asString($_POST['type']) : null;
	$value = isset($_POST['value']) ? Param::asString($_POST['value']) : null;
	$description = isset($_POST['description']) ? Param::asString($_POST['description']) : null;
	if (Param::isBlankString($name)) {
		$response['error'] = "Missing required field 'name'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($type)) {
		$response['error'] = "Missing required field 'type'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Globals::isValidType($type)) {
		$response['error'] = "Invalid 'type' [$type]. Expected (boolean|integer|string).";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	$value = Globals::asType($value, $type);
	if (is_null($value)) {
		$response['error'] = "Invalid 'value'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the name is unique
	if (Globals::exists($name)) {
		$response['error'] = "There's already a global variable with that name [$name].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make the changes
	$successful = Globals::create($name, $value, $type, $description);
	if (!$successful) {
		$response['error'] = "Unable to create global variable [$name].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
		echo json_encode($response);
		return;
	}

	// Return the new global
	$response['data'] = Globals::get($name);
	$response['message'] = "Global variable created [$name].";
	Http::responseCode('OK');
} catch(Exception $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
