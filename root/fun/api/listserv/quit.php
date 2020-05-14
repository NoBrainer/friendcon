<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

$email = $_POST['email'];
$hasEmail = isset($email) && is_string($email) && !empty($email) && !empty(trim($email));

// Validate input
if (!$hasEmail) {
	$response['error'] = "Missing required field 'email'";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (preg_match('/[\s,<>()]/', $name)) {
	$response['error'] = "Field 'email' contains invalid special characters.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
$email = trim($email);

// If the email is already off the listserv, we're done
$result = Sql::executeSqlForResult("SELECT * FROM listserv WHERE email = ?", 's', $email);
if ($result->num_rows === 0) {
	$response['message'] = "Already unsubscribed [$email].";
	Http::responseCode('OK');
	echo json_encode($response);
	return;
}

// Remove the email
$affectedRows = Sql::executeSqlForAffectedRows("DELETE FROM listserv WHERE email = ?", 's', $email);
if ($affectedRows === 1) {
	$response['message'] = "Successfully unsubscribed [$email].";
	Http::responseCode('OK');

	// Notify the person via email
	$to = $email;
	$subject = "Unsubscribed from FriendCon Listserv";
	$link = General::linkHtml('resubscribe', 'https://friendcon.com/subscribe');
	$lines = [
			"You're now unsubscribed from FriendCon. Listservs are not for everyone, but if you ever change your " .
			"mind, you can always $link."
	];
	General::sendEmailFromBot($to, $subject, $lines);
} else {
	$response['error'] = "Error unsubscribing [$email].";
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
