<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Admins as Admins;
use fun\classes\util\{General as General, Http as Http, Param as Param, Session as Session};

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	// The user must be logged out
	if (Session::$isLoggedIn) {
		$response['error'] = "Must log out to send password token.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Validate input
	$email = isset($_POST['email']) ? Param::asString($_POST['email']) : null;
	if (Param::isBlankString($email)) {
		$response['error'] = "Missing required field 'email'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure an admin exists with email
	$admin = Admins::getByEmail($email, true);
	if (is_null($admin)) {
		$response['error'] = "Invalid email address [$email].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Setup the email
	$token = Admins::getResetToken($admin);
	$subject = "FriendCon Password Reset";
	$link = General::linkHtml('link', "https://friendcon.com/fun/admin/resetPassword?token=$token&email=$email");
	$lines = [
			"Click this $link to reset your password.",
			"If you did not request a password reset, ignore this email."
	];

	// Send the email
	$successful = General::sendEmailFromBot($email, $subject, $lines);
	if (!$successful) {
		$response['error'] = "Error sending the reset email.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	} else {
		$response['message'] = "Reset email sent.";
		Http::responseCode('OK');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
