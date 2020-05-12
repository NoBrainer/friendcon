<?php
include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

try {
	// Make sure the form was submitted with required fields
	if ($_SERVER["REQUEST_METHOD"] != "POST") {
		$response['error'] = "Must send data with a POST.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	} else if (!isset($_POST['teamIndex'])) {
		$response['error'] = "Missing required field 'teamIndex'.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	} else if (!isset($_POST['challengeIndex'])) {
		$response['error'] = "Missing required field 'challengeIndex'.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}

	// Validate input
	$teamIndex = ctype_digit($_POST['teamIndex']) ? intval($_POST['teamIndex']) : -1;
	$challengeIndex = ctype_digit($_POST['challengeIndex']) ? intval($_POST['challengeIndex']) : -1;
	if ($teamIndex < 0 || $teamIndex > 999) {
		$response['error'] = "Field 'team' must be a positive 1-3 digit number.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	} else if ($challengeIndex < 0 || $challengeIndex > 999) {
		$response['error'] = "Field 'challenge' must be a positive 1-3 digit number.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}

	// Check for undefined, multiple files, or a $_FILES corruption attack
	if (!isset($_FILES["fileUpload"]) || !isset($_FILES['fileUpload']['error']) ||
			is_array($_FILES['fileUpload']['error'])) {
		$response['error'] = "Invalid parameters.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}

	// Check error value
	switch($_FILES['fileUpload']['error']) {
		case UPLOAD_ERR_OK:
			break; //No error
		case UPLOAD_ERR_NO_FILE:
			$response['error'] = "No file sent.";
			http_response_code(HTTP['BAD_REQUEST']);
			echo json_encode($response);
			return;
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			$response['error'] = "Exceeded file size limit, 5MB.";
			http_response_code(HTTP['BAD_REQUEST']);
			echo json_encode($response);
			return;
		default:
			$response['error'] = "Unknown error with file";
			http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
			echo json_encode($response);
			return;
	}

	// Check file size
	if ($_FILES['fileUpload']['size'] > 5 * 1024 * 1024) {
		$response['error'] = "Exceeded file size limit, 5MB.";
		http_response_code(HTTP['BAD_REQUEST']);
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
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}

	// Make sure the challenge is still accepting submissions
	$query = "SELECT * FROM challenges WHERE challengeIndex = ?";
	$result = executeSqlForResult($mysqli, $query, 'i', $challengeIndex);
	if (!hasRows($result)) {
		$response['error'] = "No challenge with challengeIndex [$challengeIndex].";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}
	$row = getNextRow($result);
	$now = new DateTime();
	$startTime = stringToDate($row['startTime']);
	$endTime = stringToDate($row['endTime']);
	if ($startTime && new DateTime($startTime) > $now) {
		$response['error'] = "This challenge is not yet accepting submissions.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}
	if ($endTime && new DateTime($endTime) < $now) {
		$response['error'] = "This challenge is no longer accepting submissions.";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}

	// Make sure the team exists
	$query = "SELECT * FROM teams WHERE teamIndex = ?";
	$result = executeSqlForResult($mysqli, $query, 'i', $teamIndex);
	if (!hasRows($result)) {
		$response['error'] = "No team with teamIndex [$teamIndex].";
		http_response_code(HTTP['BAD_REQUEST']);
		echo json_encode($response);
		return;
	}

	// Build a unique file name and move the file from the temporary directory
	$newFile = sprintf('%s_%s.%s', time(), sha1_file($_FILES['fileUpload']['tmp_name']), $extension);
	$newFilePath = sprintf('%s/%s', UPLOADS_DIR, $newFile);
	if (!move_uploaded_file($_FILES['fileUpload']['tmp_name'], $newFilePath)) {
		$response['error'] = "Failed to move uploaded file.";
		http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
		echo json_encode($response);
		return;
	}

	// Track the file in the database
	$query = "INSERT INTO uploads(`teamIndex`, `challengeIndex`, `file`) VALUES (?, ?, ?)";
	$successful = executeSql($mysqli, $query, 'iis', $teamIndex, $challengeIndex, $newFile);
	if ($successful) {
		// Success
		$response['message'] = "File is successfully uploaded!";
		http_response_code(HTTP['OK']);
	} else {
		$response['error'] = "File saved but metadata not saved in database.";
		http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
}
echo json_encode($response);
