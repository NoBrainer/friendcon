<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use fun\classes\Constants as Constants;
use fun\classes\util\Http as Http;
use fun\classes\util\Param as Param;

try {
	// Validate input
	$file = isset($_GET['file']) ? Param::asString($_GET['file']) : null;
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
} catch(RuntimeException $e) {
	Http::contentType('JSON');
	$response = [];
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
readfile($filePath);
