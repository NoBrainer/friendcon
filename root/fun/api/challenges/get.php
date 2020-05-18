<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
use util\Http as Http;
use util\Session as Session;

// Make sure non-admins only get the current challenges
$currentOnly = Session::$isGameAdmin ? !isset($_GET['all']) : true;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Return the challenges
$response['data'] = Challenges::getAll($currentOnly);
Http::responseCode('OK');
echo json_encode($response);
