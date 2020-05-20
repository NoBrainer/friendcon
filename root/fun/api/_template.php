<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Http as Http;

if (Http::return404IfNotPost()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Validate input
	// ...

	$response['data'] = [];
	Http::responseCode('OK');
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
