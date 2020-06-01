<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\DangerZone as DangerZone;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

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
	$precaution = isset($_POST['precaution']) ? Param::asString($_POST['precaution']) : null;
	if (is_null($precaution) || $precaution !== "RESET") {
		$response['error'] = "Bad request to reset game data.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make the changes
	$successful = DangerZone::resetGameData();
	if (!$successful) {
		$response['error'] = "Unable to reset game data.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
		echo json_encode($response);
		return;
	}

	$response['message'] = "Done resetting game data.";
	Http::responseCode('OK');
} catch(Exception $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
