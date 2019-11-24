<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/secrets/initDB.php');
include('../internal/checkAppState.php');
include('../internal/constants.php');

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

// Get the submit data
$orderId = trim($_POST['orderId']);

if (!isset($orderId) || $orderId == "") {
    $response["error"] = "No order id provided";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

include('../internal/secrets/paypal.php');
include('../internal/functions.php');

// Get the access token
$accessToken = getAccessToken($PAYPAL_OAUTH_API_URL, $PAYPAL_CLIENT_ID, $PAYPAL_SECRET);
if (!$accessToken) {
    $response["error"] = "Error with registration authentication.";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
} else if (startsWith($accessToken, "Error:")) {
    $response["error"] = $accessToken;
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}

// Check if the order has been completed with PayPal
$complete = isOrderComplete($PAYPAL_ORDER_API_URL, $orderId, $accessToken);
if (is_string($complete)) {
    $response["error"] = $complete;
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
} else if ($complete) {
    $errorMessage = saveOrder($mysqli, $userSession, $conYear, $orderId);
    if ($errorMessage) {
        $response["error"] = $errorMessage;
        http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    } else {
        $response["data"] = "Registration complete!";
        http_response_code($HTTP_OK);
    }
} else {
    $response["error"] = "Error with registration.";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
}

echo json_encode($response);
