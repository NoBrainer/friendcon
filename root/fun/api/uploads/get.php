<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Uploads as Uploads;
use util\Http as Http;
use util\Session as Session;

$publishedOnly = !isset($_GET['all']);

// Only return all uploads if the user is an admin
if (!Session::$isGameAdmin) {
	$publishedOnly = true;
}

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

$response['data'] = Uploads::getAll($publishedOnly);
Http::responseCode('OK');
echo json_encode($response);
