<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

if (!Session::$isAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	Http::responseCode('FORBIDDEN');
	echo json_encode($response);
	return;
}

$emailStr = "";

// Get the listserv emails
$result = Sql::executeSqlForResult("SELECT * FROM listserv");
if (!Sql::hasRows($result)) {
	$emailStr = "Listserv is empty.";
} else {
	// Build the email string
	while ($row = Sql::getNextRow($result)) {
		if (!empty($emailStr)) $emailStr .= ", ";
		$emailStr .= $row['email'];
	}
	if (empty($emailStr)) {
		$emailStr = "Listserv is empty.";
	}
}

$response['data'] = $emailStr;
Http::responseCode('OK');
echo json_encode($response);
