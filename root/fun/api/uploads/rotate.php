<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

if (!Session::$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	Http::responseCode('FORBIDDEN');
	echo json_encode($response);
	return;
}

$file = $_POST['file'];
$hasFile = isset($file) && is_string($file) && !empty($file);

// Validate input
if (!$hasFile) {
	$response['error'] = "Missing required field 'file'.";
	Http::responseCode('BAD_REQUEST');
	echo json_encode($response);
	return;
}
try {
	// Use the full path
	$fullPath = sprintf('%s/%s', Constants::uploadsDir(), $file);

	// Create the file in memory, depending on the image extension
	if (preg_match('/\.gif$/i', $fullPath)) {
		$sourceImage = imagecreatefromgif($fullPath);
		$bgColor = imageColorAllocateAlpha($sourceImage, 0, 0, 0, 127);
		$rotatedImage = imagerotate($sourceImage, 270, $bgColor);
		$successful = imagegif($rotatedImage, $fullPath);
	} else if (preg_match('/\.png$/i', $fullPath)) {
		$sourceImage = imagecreatefrompng($fullPath);
		$bgColor = imageColorAllocateAlpha($sourceImage, 0, 0, 0, 127);
		$rotatedImage = imagerotate($sourceImage, 270, $bgColor);
		$successful = imagepng($rotatedImage, $fullPath);
	} else if (preg_match('/\.jpe?g$/i', $fullPath)) {
		$sourceImage = imagecreatefromjpeg($fullPath);
		$bgColor = imageColorAllocateAlpha($sourceImage, 0, 0, 0, 127);
		$rotatedImage = imagerotate($sourceImage, 270, $bgColor);
		$successful = imagejpeg($rotatedImage, $fullPath);
	} else {
		$response['error'] = "Unsupported image type.";
		Http::responseCode('BAD_REQUEST');
		echo json_encode($response);
		return;
	}

	// Free the memory
	imagedestroy($sourceImage);
	imagedestroy($rotatedImage);

	if ($successful) {
		$response['message'] = "Image rotated.";
		Http::responseCode('OK');

		// Get the rotation index
		$result = Sql::executeSqlForResult("SELECT * FROM uploads WHERE file = ?", 's', $file);
		$row = Sql::getNextRow($result);
		$rotation = intval($row['rotation']);

		// Rotate the rotation index 0->1->2->3->0
		if (++$rotation > 3) $rotation = 0;

		// Update the rotation index
		Sql::executeSql("UPDATE uploads SET rotation = ? WHERE file = ?", 'is', $rotation, $file);

		// Add the updated uploads to the response
		$result = Sql::executeSqlForResult("SELECT * FROM uploads");
		$uploads = [];
		while ($row = Sql::getNextRow($result)) {
			$uploads[] = [
					'file'           => "" . $row['file'],
					'challengeIndex' => intval($row['challengeIndex']),
					'teamIndex'      => intval($row['teamIndex']),
					'state'          => "" . $row['state'],
					'rotation'       => intval($row['rotation']),
					'uploadTime'     => General::stringToDate($row['uploadTime']),
					'published'      => boolval($row['published'])
			];
		}
		$response['uploads'] = $uploads;
	} else {
		$response['error'] = "Failed to rotate image [$file].";
		Http::responseCode('INTERNAL_SERVER_ERROR');
	}
} catch(RuntimeException $e) {
	$response['error'] = $e->getMessage();
	Http::responseCode('INTERNAL_SERVER_ERROR');
}

echo json_encode($response);
