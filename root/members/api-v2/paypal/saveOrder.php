<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/initDB.php');
include('../internal/checkAppState.php');
include('../internal/constants.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Get the submit data
$orderId = trim($_POST['orderId']);

if (!isset($orderId) || $orderId == "") {
	$response['error'] = "No order id provided";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

include('../internal/initPaypal.php');
include('../internal/functions.php');

// Get the access token
$accessToken = getAccessToken(PAYPAL['OAUTH_API_URL'], PAYPAL['CLIENT_ID'], $PAYPAL_SECRET);
unset($PAYPAL_SECRET); //Remove sensitive info from memory
if (!$accessToken) {
	$response['error'] = "Error with registration authentication.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
	return;
} else if (startsWith($accessToken, "Error:")) {
	$response['error'] = $accessToken;
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
	return;
}

// Check if the order has been completed with PayPal
$complete = isOrderComplete(PAYPAL['ORDER_API_URL'], $orderId, $accessToken);
if (is_string($complete)) {
	$response['error'] = $complete;
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
} else if ($complete) {
	$errorMessage = saveOrder($mysqli, $userSession, $conYear, $orderId);
	if ($errorMessage) {
		$response['error'] = $errorMessage;
		http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	} else {
		$response['data'] = "Registration complete!";
		http_response_code(HTTP['OK']);
	}
} else {
	$response['error'] = "Error with registration.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}

echo json_encode($response);
