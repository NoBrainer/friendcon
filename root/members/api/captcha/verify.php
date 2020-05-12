<?php
include('../internal/constants.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Only accept POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	$response['error'] = "Invalid method. Must be POST.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Validate input
$token = $_POST['token'];
$action = $_POST['action'];
$debug = isset($_POST['debug']);
if (!isset($token) || !is_string($token) || empty($token)) {
	$response['error'] = "Missing required field 'token'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!isset($action) || !is_string($action) || empty($action)) {
	$response['error'] = "Missing required field 'action'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Call Google for reCAPTCHA verification
include('../internal/initCaptcha.php');
$url = "https://www.google.com/recaptcha/api/siteverify?secret=$CAPTCHA_SECRET_V3_KEY&response=$token";
$recaptchaResponse = json_decode(file_get_contents($url));

// Remove sensitive info from memory
unset($CAPTCHA_SECRET_V2_KEY);
unset($CAPTCHA_SECRET_V3_KEY);

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
	http_response_code(HTTP['BAD_REQUEST']);
} else if ($hostname !== "friendcon.com") {
	$response['error'] = "Invalid hostname for website.";
	http_response_code(HTTP['BAD_REQUEST']);
} else if ($action !== $recaptchaResponse->action) {
	$response['error'] = "Invalid action. Different than action used to generate token.";
	http_response_code(HTTP['BAD_REQUEST']);
} else if ($score < 0.5) {
	$response['message'] = "Weak reCAPTCHA score [$score]";
	http_response_code(HTTP['NOT_AUTHORIZED']);
} else {
	$response['message'] = "Acceptable reCAPTCHA score [$score]";
	http_response_code(HTTP['OK']);
}
echo json_encode($response);
