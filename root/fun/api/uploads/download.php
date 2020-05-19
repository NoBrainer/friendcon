<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Http as Http;
use util\Param as Param;

// Only allow POST request method
if (Http::return404IfNotPost()) exit;

// Validate input
$file = $_GET['file'];
if (Param::isBlankString($file)) {
	Http::contentType('TEXT');
	Http::responseCode('BAD_REQUEST');
	echo "Missing field 'file'";
	return;
}

// Build the path from the uploads directory
$filePath = sprintf("%s/%s", Constants::uploadsDir(), $file);

// Handle missing file
if (!file_exists($filePath)) {
	Http::contentType('TEXT');
	Http::responseCode('NOT_FOUND');
	echo sprintf("No file found [%s].", $file);
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
