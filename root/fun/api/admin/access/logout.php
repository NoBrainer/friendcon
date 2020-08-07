<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\util\{Http as Http, Session as Session};

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (Session::$isLoggedIn) {
		Session::logout();
		$response['data'] = "Successfully logged out.";
		Http::responseCode('OK');
	} else {
		$response['error'] = "Not logged in.";
		Http::responseCode('BAD_REQUEST');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
