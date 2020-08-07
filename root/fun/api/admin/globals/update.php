<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Globals as Globals;
use fun\classes\util\{Http as Http, Param as Param, Session as Session};

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
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
	} else if (is_null($value)) {
		$response['error'] = "Missing required field 'value'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	$value = Globals::asType($value, $type);
	if (is_null($value)) {
		if (Globals::isBooleanType($type)) {
			$examples = " Expected: (true|false|1|0).";
		} else if (Globals::isIntegerType($type)) {
			$examples = " Examples: (-1|0|1|9001).";
		} else {
			$examples = "";
		}
		$response['error'] = "Invalid $type for 'value'.$examples";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the name exists
	if (!Globals::exists($name)) {
		$response['error'] = "There's no global variable with that name [$name].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Check permissions based on the name
	if (!Session::$isGameAdmin && in_array($name, Globals::GAME_GLOBALS)) {
		$response['error'] = "You must be a game admin to update this global variable [$name].";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	} else if (!Session::$isSiteAdmin) {
		$response['error'] = "You must be a site admin to update this global variable [$name].";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Make the changes
	$successful = Globals::update($name, $type, $value, $description);
	if (!$successful) {
		$response['error'] = "Unable to update global variable [$name].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
		echo json_encode($response);
		return;
	}

	// Return the updated global variable
	$response['data'] = Globals::get($name);
	$response['message'] = "Global variable updated [$name].";
	Http::responseCode('OK');
} catch(Exception $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
