<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Validate input
if (isset($userSession) && $userSession !== "") {
	session_destroy();
	unset($userSession);
	$response['data'] = "Successfully logged out";
	http_response_code(HTTP['OK']);
} else {
	$response['error'] = "Not logged in";
	http_response_code(HTTP['BAD_REQUEST']);
}
echo json_encode($response);
