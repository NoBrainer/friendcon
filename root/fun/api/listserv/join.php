<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Listserv as Listserv;
use fun\classes\util\Http as Http;
use fun\classes\util\Param as Param;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Validate input
	$email = isset($_POST['email']) ? Param::asString($_POST['email']) : null;
	if (Param::isBlankString($email)) {
		$response['error'] = "Missing required field 'email'";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Listserv::isValidEmail($email)) {
		$response['error'] = "Field 'email' either contains invalid special characters or is too long.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// If the email is already on the listserv, we're done
	if (Listserv::exists($email)) {
		$response['message'] = "Already subscribed [$email].";
		Http::responseCode('OK');
		echo json_encode($response);
		return;
	}

	// Add the email
	$successful = Listserv::add($email);
	if ($successful) {
		$response['message'] = "Successfully subscribed [$email].";
		$response['emailSent'] = Listserv::notifySubscribed($email);
		Http::responseCode('OK');
	} else {
		$response['error'] = "Error subscribing [$email].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
