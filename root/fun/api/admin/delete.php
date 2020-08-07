<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Admins as Admins;
use fun\classes\util\{General as General, Http as Http, Param as Param, Session as Session};

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isSiteAdmin) {
		$response['error'] = "You must be a site admin to delete other admins.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Validate input
	$uid = isset($_POST['uid']) ? Param::asInteger($_POST['uid']) : null;
	if (is_null($uid)) {
		$response['error'] = "Missing required field 'uid'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the challenge exists
	if (!Admins::exists($uid)) {
		$response['error'] = "No admin with uid [$uid].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Delete the admin
	$successful = Admins::delete($uid);
	if ($successful) {
		// Send an email for the admin about their account being deleted
		$subject = "FriendCon Admin Account Deleted";
		$lines = [
				"Your admin account as been deleted. If you think this is a mistake, contact admin@friendcon.com."
		];
		$response['sentEmail'] = General::sendEmailFromBot($email, $subject, $lines);

		$response['message'] = "Admin deleted.";
		Http::responseCode('OK');
	} else {
		$response['error'] = "Unable to delete admin.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
