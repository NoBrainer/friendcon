<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Listserv as Listserv;
use util\Http as Http;
use util\Session as Session;

if (Http::return404IfNotGet()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isAdmin) {
		$response['error'] = "You are not an admin! GTFO.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Return a string with a list of email addresses
	$response['data'] = Listserv::getListString();
	Http::responseCode('OK');
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
