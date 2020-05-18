<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Uploads as Uploads;
use util\Http as Http;
use util\Session as Session;

// Make sure non-admins only get the published uploads
$publishedOnly = Session::$isGameAdmin ? !isset($_GET['all']) : true;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Return the uploads
$response['data'] = Uploads::getAll($publishedOnly);
Http::responseCode('OK');
echo json_encode($response);
