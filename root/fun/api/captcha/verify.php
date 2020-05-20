<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Captcha as Captcha;
use util\Http as Http;
use util\Param as Param;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Validate input
	$token = $_POST['token'];
	$action = $_POST['action'];
	if (Param::isBlankString($token)) {
		$response['error'] = "Missing required field 'token'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($action)) {
		$response['error'] = "Missing required field 'action'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Call Google for reCAPTCHA verification
	$verification = Captcha::verify($token);
	$success = $verification->success;
	$hostname = $verification->hostname;
	$score = $verification->score;
	$verifiedAction = $verification->action;

	// Respond based on the reCAPTCHA response
	if (!$success) {
		$response['error'] = "Invalid token.";
		Http::responseCode('BAD_REQUEST');
	} else if ($hostname !== Captcha::HOSTNAME) {
		$response['error'] = "Invalid hostname for website.";
		Http::responseCode('BAD_REQUEST');
	} else if ($action !== $verifiedAction) {
		$response['error'] = "Invalid action. Different than action used to generate token.";
		Http::responseCode('BAD_REQUEST');
	} else if ($score < Captcha::THRESHOLD) {
		$response['message'] = "Weak reCAPTCHA score [$score]";
		Http::responseCode('NOT_AUTHORIZED');
	} else {
		$response['message'] = "Acceptable reCAPTCHA score [$score]";
		Http::responseCode('OK');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
