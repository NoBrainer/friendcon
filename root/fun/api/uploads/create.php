<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

try {
	// Make sure the form was submitted with required fields
	if ($_SERVER["REQUEST_METHOD"] != "POST") {
		$response['error'] = "Must send data with a POST.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!isset($_POST['teamIndex'])) {
		$response['error'] = "Missing required field 'teamIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!isset($_POST['challengeIndex'])) {
		$response['error'] = "Missing required field 'challengeIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Validate input
	$teamIndex = ctype_digit($_POST['teamIndex']) ? intval($_POST['teamIndex']) : -1;
	$challengeIndex = ctype_digit($_POST['challengeIndex']) ? intval($_POST['challengeIndex']) : -1;
	if ($teamIndex < 0 || $teamIndex > 999) {
		$response['error'] = "Field 'team' must be a positive 1-3 digit number.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if ($challengeIndex < 0 || $challengeIndex > 999) {
		$response['error'] = "Field 'challenge' must be a positive 1-3 digit number.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Check for undefined, multiple files, or a $_FILES corruption attack
	if (!isset($_FILES["fileUpload"]) || !isset($_FILES['fileUpload']['error']) ||
			is_array($_FILES['fileUpload']['error'])) {
		$response['error'] = "Invalid parameters.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Check error value
	switch($_FILES['fileUpload']['error']) {
		case UPLOAD_ERR_OK:
			break; //No error
		case UPLOAD_ERR_NO_FILE:
			$response['error'] = "No file sent.";
			Http::responseCode('BAD_REQUEST');
			echo json_encode($response);
			return;
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			$response['error'] = "Exceeded file size limit, 5MB.";
			Http::responseCode('BAD_REQUEST');
			echo json_encode($response);
			return;
		default:
			$response['error'] = "Unknown error with file";
			Http::responseCode('INTERNAL_SERVER_ERROR');
			echo json_encode($response);
			return;
	}

	// Check file size
	if ($_FILES['fileUpload']['size'] > 5 * 1024 * 1024) {
		$response['error'] = "Exceeded file size limit, 5MB.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Check the MIME Type
	$fileInfo = new finfo(FILEINFO_MIME_TYPE);
	$allowedMimeTypes = [
			"jpg"  => "image/jpg",
			"jpeg" => "image/jpeg",
			"gif"  => "image/gif",
			"png"  => "image/png"
	];
	$extension = array_search($fileInfo->file($_FILES['fileUpload']['tmp_name']), $allowedMimeTypes, true);
	if ($extension === false) {
		$response['error'] = "Invalid file format. Must be jpg, jpeg, gif, or png.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the challenge is still accepting submissions
	$query = "SELECT * FROM challenges WHERE challengeIndex = ?";
	$result = Sql::executeSqlForResult($query, 'i', $challengeIndex);
	if (!Sql::hasRows($result)) {
		$response['error'] = "No challenge with challengeIndex [$challengeIndex].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	$row = Sql::getNextRow($result);
	$now = new DateTime();
	$startTime = General::stringToDate($row['startTime']);
	$endTime = General::stringToDate($row['endTime']);
	if ($startTime && new DateTime($startTime) > $now) {
		$response['error'] = "This challenge is not yet accepting submissions.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	if ($endTime && new DateTime($endTime) < $now) {
		$response['error'] = "This challenge is no longer accepting submissions.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the team exists
	$query = "SELECT * FROM teams WHERE teamIndex = ?";
	$result = Sql::executeSqlForResult($query, 'i', $teamIndex);
	if (!Sql::hasRows($result)) {
		$response['error'] = "No team with teamIndex [$teamIndex].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Build a unique file name and move the file from the temporary directory
	$newFile = sprintf('%s_%s.%s', time(), sha1_file($_FILES['fileUpload']['tmp_name']), $extension);
	$newFilePath = sprintf('%s/%s', UPLOADS_DIR, $newFile);
	if (!move_uploaded_file($_FILES['fileUpload']['tmp_name'], $newFilePath)) {
		$response['error'] = "Failed to move uploaded file.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
		echo json_encode($response);
		return;
	}

	// Track the file in the database
	$query = "INSERT INTO uploads(`teamIndex`, `challengeIndex`, `file`) VALUES (?, ?, ?)";
	$successful = Sql::executeSql($query, 'iis', $teamIndex, $challengeIndex, $newFile);
	if ($successful) {
		// Success
		$response['message'] = "File is successfully uploaded!";
		Http::responseCode('OK');
	} else {
		$response['error'] = "File saved but metadata not saved in database.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}
echo json_encode($response);
