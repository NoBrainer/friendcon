<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// The user must be logged out
if (isset($userSession) && $userSession !== "") {
	$response['error'] = "Must log out to reset password";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

$email = $_POST['email'];
$token = $_POST['token'];
$password = $_POST['password'];

// Validate input
if (!isset($email) || !is_string($email) || empty(trim($email))) {
	$response['error'] = "Missing required field 'email'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!isset($token) || !is_string($token) || empty(trim($token))) {
	$response['error'] = "Missing required field 'token'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (!isset($password) || !is_string($password) || empty(trim($password))) {
	$response['error'] = "Missing required field 'password'.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Make sure an admin exists with email and token
$query = "SELECT * FROM admins WHERE email = ? AND hash = ?";
$result = executeSqlForResult($mysqli, $query, 'ss', $email, $token);
if (!hasRows($result, 1)) {
	$response['error'] = "Invalid email/token pair.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Set the new password hash
$query = "UPDATE admins SET hash = ? WHERE email = ?";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, 'ss', md5($password), $email);

if ($affectedRows === 1) {
	$response['message'] = "Password successfully updated.";
	http_response_code(HTTP['OK']);

	// Send an email to the admin
	$to = $email;
	$subject = "FriendCon Password Reset";
	$lines = [
			"Your password has been reset. If you did not do this, please contact us at: admin@friendcon.com"
	];
	sendEmailFromBot($to, $subject, $lines);
} else {
	$response['error'] = "Password not updated.";
	http_response_code(HTTP['BAD_REQUEST']);
}

echo json_encode($response);



