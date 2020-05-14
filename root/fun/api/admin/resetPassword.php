<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// The user must be logged out
if (Session::$isLoggedIn) {
	$response['error'] = "Must log out to reset password";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

$email = $_POST['email'];
$token = $_POST['token'];
$password = $_POST['password'];

// Validate input
if (!isset($email) || !is_string($email) || empty(trim($email))) {
	$response['error'] = "Missing required field 'email'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!isset($token) || !is_string($token) || empty(trim($token))) {
	$response['error'] = "Missing required field 'token'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!isset($password) || !is_string($password) || empty(trim($password))) {
	$response['error'] = "Missing required field 'password'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Make sure an admin exists with email and token
$query = "SELECT * FROM admins WHERE email = ? AND hash = ?";
$result = Sql::executeSqlForResult($query, 'ss', $email, $token);
if (!Sql::hasRows($result, 1)) {
	$response['error'] = "Invalid email/token pair.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}

// Set the new password hash
$query = "UPDATE admins SET hash = ? WHERE email = ?";
$affectedRows = Sql::executeSqlForAffectedRows($query, 'ss', md5($password), $email);

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

echo json_encode($response);



