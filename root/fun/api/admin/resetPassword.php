<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Admins as Admins;
use util\General as General;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	// The user must be logged out
	if (Session::$isLoggedIn) {
		$response['error'] = "Must log out to reset password.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Validate input
	$email = $_POST['email'];
	$token = $_POST['token'];
	$password = $_POST['password'];
	if (Param::isBlankString($email)) {
		$response['error'] = "Missing required field 'email'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($token)) {
		$response['error'] = "Missing required field 'token'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($password)) {
		$response['error'] = "Missing required field 'password'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure an admin exists with email and token
	if (!Admins::existsWithResetToken($email, $token)) {
		$response['error'] = "Invalid email/token pair.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Set the new password hash
	$affectedRows = Admins::updatePassword($email, $password);
	if ($affectedRows === 1) {
		$response['message'] = "Password successfully updated.";
		Http::responseCode('OK');

		// Send an email to the admin
		$to = $email;
		$subject = "FriendCon Password Reset";
		$lines = [
				"Your password has been reset. If you did not do this, please contact us at: admin@friendcon.com"
		];
		General::sendEmailFromBot($to, $subject, $lines);
	} else {
		$response['error'] = "Password not updated.";
		Http::responseCode('BAD_REQUEST');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
