<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Challenges as Challenges;
use fun\classes\util\Http as Http;
use fun\classes\util\Session as Session;

if (Http::return404IfNotGet()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Make sure non-admins only get the current challenges
	$currentOnly = Session::$isGameAdmin ? !isset($_GET['all']) : true;

	// Return the challenges
	$response['data'] = Challenges::getAll($currentOnly);
	Http::responseCode('OK');
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
