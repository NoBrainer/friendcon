<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include('../secrets/dbconnect.php');
include('../util/check_app_state.php');

// Get the submit data
$orderId = $MySQLi_CON->real_escape_string($_POST['orderId']);

if (!isset($orderId) || $orderId == "") {
    exit("No order id provided");
}

include('../secrets/paypal.php');
include('functions.php');

// Get the access token
$accessToken = getAccessToken($PAYPAL_OAUTH_API_URL, $PAYPAL_CLIENT_ID, $PAYPAL_SECRET);
if (!$accessToken) {
    exit("Error with registration authentication.");
}

// Check if the order has been completed with PayPal
if (isOrderComplete($PAYPAL_ORDER_API_URL, $orderId, $accessToken)) {
    $errorMessage = saveOrder($MySQLi_CON, $userSession, $orderId, $conYear);
    if ($errorMessage) {
        exit($errorMessage);
    } else {
        exit("Registration complete!");
    }
} else {
    exit("Error with registration.");
}
?>