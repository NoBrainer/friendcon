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
$debug = isset($_POST['debug']);
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
Captcha::initialize();
$url = "https://www.google.com/recaptcha/api/siteverify?secret=" . Captcha::$CAPTCHA_SECRET_V3_KEY . "&response=$token";
$recaptchaResponse = json_decode(file_get_contents($url));
Captcha::unsetCaptchaSecrets();

// Parse through the response
$success = $recaptchaResponse->success;
$hostname = $recaptchaResponse->hostname;
$score = $recaptchaResponse->score;

if ($debug) {
	$response['recaptcha'] = $recaptchaResponse;
}

// Respond based on the reCAPTCHA response
if (!$success) {
	$response['error'] = "Invalid token.";
	Http::responseCode('BAD_REQUEST');
} else if ($hostname !== "friendcon.com") {
	$response['error'] = "Invalid hostname for website.";
	Http::responseCode('BAD_REQUEST');
} else if ($action !== $recaptchaResponse->action) {
	$response['error'] = "Invalid action. Different than action used to generate token.";
	Http::responseCode('BAD_REQUEST');
} else if ($score < 0.5) {
	$response['message'] = "Weak reCAPTCHA score [$score]";
	Http::responseCode('NOT_AUTHORIZED');
} else {
	$response['message'] = "Acceptable reCAPTCHA score [$score]";
	Http::responseCode('OK');
}
echo json_encode($response);
