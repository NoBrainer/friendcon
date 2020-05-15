<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Captcha as Captcha;
use util\Http as Http;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Only accept POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	$response['error'] = "Invalid method. Must be POST.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Validate input
$token = $_POST['token'];
$action = $_POST['action'];
if (!isset($token) || !is_string($token) || empty($token)) {
	$response['error'] = "Missing required field 'token'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!isset($action) || !is_string($action) || empty($action)) {
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
echo json_encode($response);
