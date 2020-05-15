<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Teams as Teams;
use util\Http as Http;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Get the teams
$teams = Teams::getAll();

$response['data'] = $teams;
Http::responseCode('OK');
echo json_encode($response);
