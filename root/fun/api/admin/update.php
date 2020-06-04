<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Admins as Admins;
use dao\Listserv as Listserv;
use util\Http as Http;
use util\Param as Param;
use util\Session as Session;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isAdmin) {
		$response['error'] = "You must be an admin to update other admins.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Validate input
	$uid = isset($_POST['uid']) ? Param::asInteger($_POST['uid']) : null;
	$name = isset($_POST['name']) ? Param::asString($_POST['name']) : null;
	$email = isset($_POST['email']) ? Param::asString($_POST['email']) : null;
	$isGameAdmin = isset($_POST['gameAdmin']) ? Param::asBoolean($_POST['gameAdmin']) : null;
	$isSiteAdmin = isset($_POST['siteAdmin']) ? Param::asBoolean($_POST['siteAdmin']) : null;
	if (is_null($uid)) {
		$response['error'] = "Missing required field 'uid'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (Param::isBlankString($name)) {
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

	// Make sure an admin exists with the provided uid
	if (!Admins::exists($uid)) {
		$response['error'] = "No admin with uid [$uid].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	$admin = Admins::get($uid);

	// Prevent anyone from updating the email
	if ($email !== $admin['email']) {
		$response['error'] = "No one is allowed to update an admin email. Instead, add a new admin.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Prevent non-site/game admins from modifying game privileges
	if (!Session::$isGameAdmin && !Session::$isSiteAdmin && $isGameAdmin !== $admin['gameAdmin']) {
		$response['error'] = "You lack the permission to modify 'gameAdmin' status.";
		$response['sent'] = $isGameAdmin;
		$response['prev'] = $admin['gameAdmin'];
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Prevent non-site admins from modifying site privileges
	if (!Session::$isSiteAdmin && $isSiteAdmin !== $admin['siteAdmin']) {
		$response['error'] = "You lack the permission to modify 'siteAdmin' status.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Make the changes
	$successful = Admins::update($uid, $name, $email, $isSiteAdmin, $isGameAdmin);
	if ($successful) {
		// Return the updated admin
		$response['data'] = Admins::get($uid);
		$response['message'] = "Admin updated.";
		Http::responseCode('OK');
	} else {
		Http::responseCode('NOT_MODIFIED');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
