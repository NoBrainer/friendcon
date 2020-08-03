<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\dao\Uploads as Uploads;
use fun\classes\util\Http as Http;
use fun\classes\util\Session as Session;

if (Http::return404IfNotGet()) exit;
Http::contentType('JSON');
$response = [];

try {
	// Make sure non-admins only get the published uploads
	$publishedOnly = Session::$isGameAdmin ? !isset($_GET['all']) : true;

	// Return the uploads
	$response['data'] = Uploads::getAll($publishedOnly);
	Http::responseCode('OK');
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
