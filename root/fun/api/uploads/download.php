<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Http as Http;

// Validate input
$fileName = $_GET['file'];
if (!isset($fileName) || empty($fileName)) {
	Http::contentType('TEXT');
	Http::responseCode('BAD_REQUEST');
	echo "Missing field 'file'";
	return;
}

// Build the path from the uploads directory
$filePath = sprintf("%s/%s", UPLOADS_DIR, $fileName);

// Handle missing file
if (!file_exists($filePath)) {
	Http::contentType('TEXT');
	Http::responseCode('NOT_FOUND');
	echo sprintf("No file found [%s]", $fileName);
	return;
}

// Setup HTTP headers
Http::contentDescription('File Transfer');
Http::contentType('STREAM');
Http::contentDisposition('attachment; filename=' . basename($filePath) . '"');
Http::contentLength(filesize($filePath));
Http::cacheControl('public, max-age=52800000'); //cache for 48-hours

Http::responseCode('OK');
readfile($filePath);
