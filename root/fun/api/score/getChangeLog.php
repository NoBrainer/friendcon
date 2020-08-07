<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Score as Score;
use fun\classes\util\{Http as Http, Session as Session};

if (Http::return404IfNotGet()) exit;
Http::contentType('JSON');
$response = [];

try {
	if (!Session::$isGameAdmin) {
		$response['error'] = "You are not an admin! GTFO.";
		Http::responseCode('FORBIDDEN');
		echo json_encode($response);
		return;
	}

	// Return the change log entries
	$response['data'] = Score::getChangeLogEntries();
	Http::responseCode('OK');
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
