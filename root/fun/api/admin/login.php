<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Get data from the request
$email = $_POST['email'];
$password = $_POST['password'];

// Validate input
if (isset($userSession) && $userSession != "") {
	$response['error'] = "Already logged in";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!isset($email) || !is_string($email) || empty($email)) {
	$response['error'] = "Missing email address";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!isset($password) || !is_string($password) || empty($password) || empty(trim($password))) {
	$response['error'] = "Missing password";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}
$password = trim($password);

// Check for the admin
$query = "SELECT * FROM admins WHERE email = ?";
$result = executeSqlForResult($mysqli, $query, 's', trim($email));
if (!hasRows($result, 1)) {
	$response['error'] = "No admin with this email";
	http_response_code(HTTP['NOT_FOUND']);
	echo json_encode($response);
	return;
}

// Make sure the password hashes match
$row = getNextRow($result);
if (md5($password) === $row['hash']) {
	$response['data'] = $row['uid'];
	$_SESSION['userSession'] = $row['uid'];
	http_response_code(HTTP['OK']);
} else {
	$response['error'] = "Wrong password";
	http_response_code(HTTP['NOT_AUTHORIZED']);
}
echo json_encode($response);
