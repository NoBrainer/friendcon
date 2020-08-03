<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Uploads as Uploads;
use fun\classes\util\Http as Http;
use fun\classes\util\Param as Param;
use fun\classes\util\Session as Session;

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
	$file = isset($_POST['file']) ? Param::asString($_POST['file']) : null;
	if (Param::isBlankString($file)) {
		$response['error'] = "Missing required field 'file'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Uploads::exists($file)) {
		$response['error'] = "File does not exist [$file].";
		Http::responseCode('NOT_FOUND');
		echo json_encode($response);
		return;
	}

	$successful = Uploads::rotate($file);
	if ($successful) {
		$response['message'] = "Image rotated.";
		Http::responseCode('OK');

		// Get the updated uploads
		$response['uploads'] = Uploads::getAll(false);
	} else {
		$response['error'] = "Failed to rotate image [$file].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
