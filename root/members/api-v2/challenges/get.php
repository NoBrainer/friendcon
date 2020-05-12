<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');
include('../internal/checkAdmin.php');

$returnAll = boolval(isset($_GET['all']));

// Only return all challenges if the user is an admin
if (!isset($userSession) || $userSession == "" || !$isGameAdmin) {
	$returnAll = false;
}

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

// Get the data
$isWithinTimeConstraints = "(startTime <= NOW() OR startTime = '0000-00-00 00:00:00') AND (endTime >= NOW() OR endTime = '0000-00-00 00:00:00')";
$query = "SELECT * FROM challenges" . ($returnAll ? "" : " WHERE published = 1 OR ($isWithinTimeConstraints)");
$result = $mysqli->query($query);

// Build the data array
$challenges = [];
while ($row = getNextRow($result)) {
	// Build and append the entry
	$challenges[] = [
			'challengeIndex' => intval($row['challengeIndex']),
			'startTime'      => stringToDate($row['startTime']),
			'endTime'        => stringToDate($row['endTime']),
			'description'    => "" . $row['description'],
			'published'      => boolval($row['published'])
	];
}

$response['data'] = $challenges;
http_response_code(HTTP['OK']);
echo json_encode($response);
