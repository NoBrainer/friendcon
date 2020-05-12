<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');
include('../internal/checkAdmin.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

if (!$isAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	http_response_code(HTTP['FORBIDDEN']);
	echo json_encode($response);
	return;
}

$emailStr = "";

// Get the listserv emails
$result = $mysqli->query("SELECT * FROM listserv");
if (!hasRows($result)) {
	$emailStr = "Listserv is empty.";
} else {
	// Build the email string
	while ($row = getNextRow($result)) {
		if (!empty($emailStr)) $emailStr .= ", ";
		$emailStr .= $row['email'];
	}
	if (empty($emailStr)) {
		$emailStr = "Listserv is empty.";
	}
}

$response['data'] = $emailStr;
http_response_code(HTTP['OK']);
echo json_encode($response);
