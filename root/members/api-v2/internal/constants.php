<?php

// Include the private config file
include($_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/config.php');

// Header for Content-Type
const CONTENT = [
		'JSON'   => 'Content-Type: application/json',
		'STREAM' => 'Content-Type: application/octet-stream',
		'TEXT'   => 'Content-Type: text/plain; charset=utf-8'
];

// HTTP Response Codes
const HTTP = [
		'OK'                    => 200,
		'NOT_MODIFIED'          => 304,
		'PERMANENT_REDIRECT'    => 308,
		'BAD_REQUEST'           => 400,
		'NOT_AUTHORIZED'        => 401,
		'FORBIDDEN'             => 403,
		'NOT_FOUND'             => 404,
		'INTERNAL_SERVER_ERROR' => 500
];

// Team/house constants
const TEAM = [
		'UNSORTED' => 0
];
