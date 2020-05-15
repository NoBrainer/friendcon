<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Listserv as Listserv;
use util\Http as Http;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

$email = $_POST['email'];
$hasEmail = isset($email) && is_string($email) && !empty($email) && !empty(trim($email));

// Validate input
if (!$hasEmail) {
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
echo json_encode($response);
