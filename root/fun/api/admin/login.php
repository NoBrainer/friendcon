<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Get data from the request
$email = $_POST['email'];
$password = $_POST['password'];

// Validate input
if (Session::$isLoggedIn) {
	$response['error'] = "Already logged in.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!isset($email) || !is_string($email) || empty($email)) {
	$response['error'] = "Missing email address.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
} else if (!isset($password) || !is_string($password) || empty($password) || empty(trim($password))) {
	$response['error'] = "Missing password.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
$password = trim($password);

// Check for the admin
$query = "SELECT * FROM admins WHERE email = ?";
$result = Sql::executeSqlForResult($query, 's', trim($email));
if (!Sql::hasRows($result, 1)) {
	$response['error'] = "No admin with this email.";
	Http::responseCode('NOT_FOUND');
	echo json_encode($response);
	return;
}

// Make sure the password hashes match
$row = Sql::getNextRow($result);
if (md5($password) === $row['hash']) {
	$response['data'] = $row['uid'];
	Session::login($row['uid']);
	Http::responseCode('OK');
} else {
	$response['error'] = "Wrong password.";
	Http::responseCode('NOT_AUTHORIZED');
}
echo json_encode($response);
