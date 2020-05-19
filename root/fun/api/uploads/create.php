<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use dao\Challenges as Challenges;
use dao\Teams as Teams;
use dao\Uploads as Uploads;
use util\Http as Http;
use util\Param as Param;

// Only allow POST request method
if (Http::return404IfNotPost()) exit;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

try {
	// Validate input
	$teamIndex = Param::asInteger($_POST['teamIndex']);
	$challengeIndex = Param::asInteger($_POST['challengeIndex']);
	if (!Teams::isValidTeamIndex($teamIndex)) {
		$response['error'] = "Missing required field 'teamIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if (!Challenges::isValidChallengeIndex($challengeIndex)) {
		$response['error'] = "Missing required field 'challengeIndex'.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	} else if ($teamIndex < 0 || $teamIndex > 999) {
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
	if (!isset($_FILES["fileUpload"]) || !isset($_FILES['fileUpload']['error']) || is_array($_FILES['fileUpload']['error'])) {
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
			$response['error'] = "Unknown error with file.";
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

	// Check the MIME Type and file extension
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
	$challenge = Challenges::get($challengeIndex);
	if (is_null($challenge)) {
		$response['error'] = "No challenge with challengeIndex [$challengeIndex].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	$now = new DateTime();
	$startTime = $challenge['startTime'];
	$endTime = $challenge['endTime'];
	if (!is_null($startTime) && new DateTime($startTime) > $now) {
		$response['error'] = "This challenge is not yet accepting submissions.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}
	if (!is_null($endTime) && new DateTime($endTime) < $now) {
		$response['error'] = "This challenge is no longer accepting submissions.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Make sure the team exists
	if (!Teams::exists($teamIndex)) {
		$response['error'] = "No team with teamIndex [$teamIndex].";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Build a unique file name and move the file from the temporary directory
	$newFile = sprintf('%s_%s.%s', time(), sha1_file($_FILES['fileUpload']['tmp_name']), $extension);
	$newFilePath = sprintf('%s/%s', Constants::uploadsDir(), $newFile);
	if (!move_uploaded_file($_FILES['fileUpload']['tmp_name'], $newFilePath)) {
		$response['error'] = "Failed to move uploaded file.";
		Http::responseCode('INTERNAL_SERVER_ERROR');
		echo json_encode($response);
		return;
	}

	// Track the file in the database
	$successful = Uploads::add($teamIndex, $challengeIndex, $newFile);
	if ($successful) {
		// Success
		$response['message'] = "File is successfully uploaded.";
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
