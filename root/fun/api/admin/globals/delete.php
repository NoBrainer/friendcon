<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Globals as Globals;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isSiteAdmin) {
		$response['error'] = "You must be a site admin to delete global variables.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Validate input
	$name = isset($_POST['name']) ? Param::asString($_POST['name']) : null;
	if (Param::isBlankString($name)) {
		$response['error'] = "Missing required field 'name'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the global exists
	if (!Globals::exists($name)) {
		$response['error'] = "No global variable exists with name [$name].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Prevent deleting of game global variables
	if (in_array($name, Globals::GAME_GLOBALS)) {
		$response['error'] = "Unable to delete game global variables since that would cause instability.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Delete the global
	$successful = Globals::delete($name);
	if ($successful) {
		$response['message'] = "Global variable deleted [$name].";
		Http::responseCode('OK');
	} else {
		$response['error'] = "Unable to delete global variable [$name].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(Exception $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
