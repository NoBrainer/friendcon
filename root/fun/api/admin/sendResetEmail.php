<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Admins as Admins;
use util\General as General;
use util\Http as Http;
use util\Session as Session;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// The user must be logged out
if (Session::$isLoggedIn) {
	$response['error'] = "Must log out to send password token";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

$email = $_POST['email'];

// Validate input
if (!isset($email) || !is_string($email) || empty(trim($email))) {
	$response['error'] = "Missing required field 'email'";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
$email = trim($email);

// Make sure an admin exists with email
$admin = Admins::getByEmail($email);
if (is_null($admin)) {
	$response['error'] = "Invalid email address [$email]";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}


// Setup the email
$token = Admins::getResetToken($admin);
$to = $email;
$subject = "FriendCon Password Reset";
$link = General::linkHtml('link', "https://friendcon.com/fun/login/resetPassword?token=$token&email=$email");
$lines = [
		"Click this $link to reset your password.",
		"If you did not request a password reset, ignore this email."
];

// Send the email
$successful = General::sendEmailFromBot($to, $subject, $lines);
if (!$successful) {
	$response['error'] = "Error sending the reset email";
	Http::responseCode('INTERNAL_SERVER_ERROR');
} else {
	$response['message'] = "Reset email sent";
	Http::responseCode('OK');
}
echo json_encode($response);
