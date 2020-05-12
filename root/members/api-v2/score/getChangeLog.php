<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');
include('../internal/checkAdmin.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

if (!isset($userSession) || $userSession == "" || !$isGameAdmin) {
	$response['error'] = "You are not an admin! GTFO.";
	http_response_code(HTTP['FORBIDDEN']);
	echo json_encode($response);
	return;
}

// Get the change log entries
$result = $mysqli->query("SELECT * FROM scoreChanges");
$entries = [];
while ($row = getNextRow($result)) {
	$entry = [
			'updateTime'     => stringToDate($row['updateTime']),
			'teamIndex'      => intval($row['teamIndex']),
			'delta'          => intval($row['delta']),
			'challengeIndex' => null
	];

	// Handle the optional challengeIndex
	$challengeIndex = $row['challengeIndex'];
	if (isset($challengeIndex) && !is_null($challengeIndex && is_numeric($challengeIndex))) {
		$entry['challengeIndex'] = intval($challengeIndex);
	}

	$entries[] = $entry;
}
$response['data'] = $entries;
http_response_code(HTTP['OK']);
echo json_encode($response);
