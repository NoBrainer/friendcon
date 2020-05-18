<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Http as Http;
use util\Session as Session;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

if (Session::$isLoggedIn) {
	Session::logout();
	$response['data'] = "Successfully logged out.";
	Http::responseCode('OK');
} else {
	$response['error'] = "Not logged in.";
	Http::responseCode('BAD_REQUEST');
}
echo json_encode($response);
