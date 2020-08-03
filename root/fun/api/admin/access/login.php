<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Admins as Admins;
use fun\classes\util\Http as Http;
use fun\classes\util\Param as Param;
use fun\classes\util\Session as Session;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Validate input
	$email = isset($_POST['email']) ? Param::asString($_POST['email']) : null;
	$password = isset($_POST['password']) ? Param::asString($_POST['password']) : null;
	if (Session::$isLoggedIn) {
		$response['error'] = "Already logged in.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($email)) {
		$response['error'] = "Missing email address.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($password)) {
		$response['error'] = "Missing password.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Check for the admin
	$admin = Admins::getByEmail($email, true);
	if (is_null($admin)) {
		$response['error'] = "No admin with this email.";
		Http::responseCode('NOT_FOUND');
		echo json_encode($response);
		return;
	}

	// Make sure the password hashes match
	if (Admins::checkPassword($admin, $password)) {
		$response['data'] = $admin['uid'];
		Session::login($admin['uid']);
		Http::responseCode('OK');
	} else {
		$response['error'] = "Wrong password.";
		Http::responseCode('NOT_AUTHORIZED');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
