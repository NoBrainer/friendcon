<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

$email = $_POST['email'];
$hasEmail = isset($email) && is_string($email) && !empty($email) && !empty(trim($email));

// Validate input
if (!$hasEmail) {
	$response['error'] = "Missing required field 'email'";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
} else if (preg_match('/[\s,<>()]/', $name)) {
	$response['error'] = "Field 'email' contains invalid special characters.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}
$email = trim($email);

// If the email is already off the listserv, we're done
$result = executeSqlForResult($mysqli, "SELECT * FROM listserv WHERE email = ?", 's', $email);
if ($result->num_rows === 0) {
	$response['message'] = "Already unsubscribed [$email].";
	http_response_code(HTTP['OK']);
	echo json_encode($response);
	return;
}

// Remove the email
$affectedRows = executeSqlForAffectedRows($mysqli, "DELETE FROM listserv WHERE email = ?", 's', $email);
if ($affectedRows === 1) {
	$response['message'] = "Successfully unsubscribed [$email].";
	http_response_code(HTTP['OK']);

	// Notify the person via email
	$to = $email;
	$subject = "Unsubscribed from FriendCon Listserv";
	$link = linkHtml('resubscribe', 'https://friendcon.com/subscribe');
	$lines = [
			"You're now unsubscribed from FriendCon. Listservs are not for everyone, but if you ever change your " .
			"mind, you can always $link."
	];
	sendEmailFromBot($to, $subject, $lines);
} else {
	$response['error'] = "Error unsubscribing [$email].";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
