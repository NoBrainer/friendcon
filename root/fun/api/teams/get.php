<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Teams as Teams;
use util\Http as Http;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Return the teams
$response['data'] = Teams::getAll();
Http::responseCode('OK');
echo json_encode($response);
