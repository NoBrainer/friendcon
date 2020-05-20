<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Listserv as Listserv;
use util\Http as Http;
use util\Param as Param;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Validate input
	$email = $_POST['email'];
	if (Param::isBlankString($email)) {
		$response['error'] = "Missing required field 'email'";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Listserv::isValidEmail($email)) {
		$response['error'] = "Field 'email' contains invalid special characters.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	$email = trim($email);

	// If the email is already off the listserv, we're done
	if (!Listserv::exists($email)) {
		$response['message'] = "Already unsubscribed [$email].";
		Http::responseCode('OK');
		echo json_encode($response);
		return;
	}

	// Remove the email
	$successful = Listserv::delete($email);
	if ($successful) {
		$response['message'] = "Successfully unsubscribed [$email].";
		$response['emailSent'] = Listserv::notifyUnsubscribed($email);
		Http::responseCode('OK');
	} else {
		$response['error'] = "Error unsubscribing [$email].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
