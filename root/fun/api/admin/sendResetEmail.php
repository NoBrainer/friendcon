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

// Make sure an admin exists with email
$query = "SELECT * FROM admins WHERE email = ?";
$result = executeSqlForResult($mysqli, $query, 's', $email);
if (!hasRows($result, 1)) {
	$response['error'] = "Invalid email address [$email]";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Use the password hash as the token
$row = getNextRow($result);
$token = $row['hash'];
//TODO: instead generate a token in the database
//TODO: keep track of attempts and throttle 3 times per 5 minutes (or something like that)

// Setup the email
$to = $email;
$subject = "FriendCon Password Reset";
$link = linkHtml('link', "https://friendcon.com/fun/login/resetPassword?token=$token&email=$email");
$lines = [
		"Click this $link to reset your password.",
		"If you did not request a password reset, ignore this email."
];

// Send the email
$successful = sendEmailFromBot($to, $subject, $lines);
if (!$successful) {
	$response['error'] = "Error sending the reset email";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
} else {
	$response['message'] = "Reset email sent";
	http_response_code(HTTP['OK']);
}
echo json_encode($response);
