<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');
include('../internal/checkAdmin.php');

$returnAll = boolval(isset($_GET['all']));

// Only return all uploads if the user is an admin
if (!isset($userSession) || $userSession == "" || !$isGameAdmin) {
	$returnAll = false;
}

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Get the data
$query = "SELECT u.*, s.state, c.published FROM uploads u " .
		"JOIN uploadState s ON u.state = s.value " .
		"JOIN challenges c ON u.challengeIndex = c.challengeIndex" .
		($returnAll ? "" : " WHERE u.state > 0 AND c.published = 1");
$result = $mysqli->query($query);

// Build the data array
$uploads = [];
while ($row = getNextRow($result)) {
	// Build and append the entry
	$uploads[] = [
			'file'           => "" . $row['file'],
			'challengeIndex' => intval($row['challengeIndex']),
			'teamIndex'      => intval($row['teamIndex']),
			'state'          => "" . $row['state'],
			'rotation'       => intval($row['rotation']),
			'uploadTime'     => stringToDate($row['uploadTime']),
			'published'      => boolval($row['published'])
	];
}

// Update the response
$response['data'] = $uploads;
http_response_code(HTTP['OK']);
echo json_encode($response);
