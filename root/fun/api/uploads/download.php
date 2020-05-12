<?php

include('../internal/constants.php');

// Validate input
$fileName = $_GET['file'];
if (!isset($fileName) || empty($fileName)) {
	header(CONTENT['TEXT']);
	http_response_code(HTTP['BAD_REQUEST']);
	echo "Missing field 'file'";
	return;
}

// Build the path from the uploads directory
$filePath = sprintf("%s/%s", UPLOADS_DIR, $fileName);

// Handle missing file
if (!file_exists($filePath)) {
	header(CONTENT['TEXT']);
	http_response_code(HTTP['NOT_FOUND']);
	echo sprintf("No file found [%s]", $fileName);
	return;
}

// Setup HTTP headers
header('Content-Description: File Transfer');
header(CONTENT['STREAM']);
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=52800000'); //cache for 48-hours

http_response_code(HTTP['OK']);
readfile($filePath);
