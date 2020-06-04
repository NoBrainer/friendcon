<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Admins as Admins;
use dao\Listserv as Listserv;
use util\General as General;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isAdmin) {
		$response['error'] = "You must be an admin to create other admins.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Validate input
	$name = isset($_POST['name']) ? Param::asString($_POST['name']) : null;
	$email = isset($_POST['email']) ? Param::asString($_POST['email']) : null;
	$isGameAdmin = isset($_POST['gameAdmin']) ? Param::asBoolean($_POST['gameAdmin']) : null;
	$isSiteAdmin = isset($_POST['siteAdmin']) ? Param::asBoolean($_POST['siteAdmin']) : null;
	if (Param::isBlankString($name)) {
		$response['error'] = "Missing required field 'name'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($email)) {
		$response['error'] = "Missing required field 'email'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Listserv::isValidEmail($email)) {
		$response['error'] = "Field 'email' either contains invalid special characters or is too long.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the email is unique
	if (Admins::existsWithEmail($email)) {
		$response['error'] = "There's already an admin with that email [$email].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Only allow site/game admins to give game permissions
	if ($isGameAdmin && !Session::$isGameAdmin && !Session::$isSiteAdmin) {
		$response['error'] = "You lack the permission to create a game admin.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Only allow site admins to give site permissions
	if ($isSiteAdmin && !Session::$isSiteAdmin) {
		$response['error'] = "You lack the permission to create a site admin.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Make the changes
	$successful = Admins::add($name, $email, $isSiteAdmin, $isGameAdmin);
	if (!$successful) {
		$response['error'] = "Unable to create admin.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
		echo json_encode($response);
		return;
	}

	// Send an email for the new admin to reset their password
	$token = Admins::getResetTokenByEmail($email);
	$subject = "Welcome FriendCon Admin";
	$resetLink = General::linkHtml('link', "https://friendcon.com/fun/admin/resetPassword?token=$token&email=$email");
	$loginLink = General::linkHtml('login', "https://friendcon.com/fun/admin");
	$lines = [
			"Click this $resetLink to reset your password. Once done, you can $loginLink as an admin!"
	];
	$sentEmail = General::sendEmailFromBot($email, $subject, $lines);

	// Return the new admin
	$response['data'] = Admins::getByEmail($email);
	$response['sentEmail'] = Param::asBoolean($sentEmail);
	$response['message'] = "Admin created [$email]. They should receive an email to reset their password.";
	Http::responseCode('OK');
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
