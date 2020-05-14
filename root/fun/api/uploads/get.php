<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

$returnAll = boolval(isset($_GET['all']));

// Only return all uploads if the user is an admin
if (!Session::$isGameAdmin) {
	$returnAll = false;
}

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Get the data
$query = "SELECT u.*, s.state, c.published FROM uploads u " .
		"JOIN uploadState s ON u.state = s.value " .
		"JOIN challenges c ON u.challengeIndex = c.challengeIndex" .
		($returnAll ? "" : " WHERE u.state > 0 AND c.published = 1");
$result = Sql::executeSqlForResult($query);

// Build the data array
$uploads = [];
while ($row = Sql::getNextRow($result)) {
	// Build and append the entry
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

// Update the response
$response['data'] = $uploads;
Http::responseCode('OK');
echo json_encode($response);
