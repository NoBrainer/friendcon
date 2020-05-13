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

// If the email is already on the listserv, we're done
$result = executeSqlForResult($mysqli, "SELECT * FROM listserv WHERE email = ?", 's', $email);
if ($result->num_rows > 0) {
	$response['message'] = "Already subscribed [$email].";
	http_response_code(HTTP['OK']);
	echo json_encode($response);
	return;
}

// Add the email
$affectedRows = executeSqlForAffectedRows($mysqli, "INSERT INTO listserv (email) VALUES (?)", 's', $email);
if ($affectedRows === 1) {
	$response['message'] = "Successfully subscribed [$email].";
	http_response_code(HTTP['OK']);

	// Notify the person via email
	$to = $email;
	$subject = "Subscribed to FriendCon Listserv!";
	$link = linkHtml('unsubscribe', 'https://friendcon.com/unsubscribe');
	$lines = [
			"Thanks for subscribing! If you didn't do this, please $link and/or contact admin@friendcon.com."
	];
	sendEmailFromBot($to, $subject, $lines);
} else {
	$response['error'] = "Error subscribing [$email].";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
