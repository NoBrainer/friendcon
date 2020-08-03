<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Teams as Teams;
use fun\classes\util\Http as Http;

if (Http::return404IfNotGet()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Return the teams
	$response['data'] = Teams::getAll();
	Http::responseCode('OK');
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
