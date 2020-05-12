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
	$response['error'] = "Must log out to send password token";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

$email = $_POST['email'];

// Validate input
if (!isset($email) || !is_string($email) || empty(trim($email))) {
	$response['error'] = "Missing required field 'email'";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}
$email = trim($email);

// Make sure the email exists
$query = "SELECT * FROM users WHERE email = ?";
$result = executeSqlForResult($mysqli, $query, 's', $email);
if (!hasRows($result, 1)) {
	$response['error'] = "Invalid email address [$email]";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Use the password hash as the token
$row = getNextRow($result);
$token = $row['password']; //TODO: instead generate a token in the database

// Setup the email
$to = $email;
$subject = "FriendCon Password Reset";
$message = "<div>Click <a href='https://friendcon.com/fun/login/resetPassword?token=$token&email=$email' target='_blank'>this link</a> to reset your password.</div>" .
		"<div>If you did not request a password reset, ignore this email.</div>" .
		"<br/>" .
		"<div>&lt;3 FriendCon Bot (BEEP. BOOP)</div>";
$headers = "From: admin@friendcon.com\r\nContent-type:text/html";

// Send the email
$successful = mail($to, $subject, $message, $headers);
if (!$successful) {
	$response['error'] = "Error sending the reset email";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
} else {
	$response['message'] = "Reset email sent";
	http_response_code(HTTP['OK']);
}
echo json_encode($response);
