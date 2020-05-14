<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\General as General;
use util\Http as Http;
use util\Session as Session;
use util\Sql as Sql;

$returnAll = boolval(isset($_GET['all']));

// Only return all challenges if the user is an admin
if (!Session::$isGameAdmin) {
	$returnAll = false;
}

// Setup the content-type and response template
Http::contentType('JSON');
$response = [];

// Get the data
$isWithinTimeConstraints = "(startTime <= NOW() OR startTime = '0000-00-00 00:00:00') AND (endTime >= NOW() OR endTime = '0000-00-00 00:00:00')";
$query = "SELECT * FROM challenges" . ($returnAll ? "" : " WHERE published = 1 OR ($isWithinTimeConstraints)");
$result = Sql::executeSqlForResult($query);

// Build the data array
$challenges = [];
while ($row = Sql::getNextRow($result)) {
	// Build and append the entry
	$challenges[] = [
			'challengeIndex' => intval($row['challengeIndex']),
			'startTime'      => General::stringToDate($row['startTime']),
			'endTime'        => General::stringToDate($row['endTime']),
			'name'           => "" . $row['name'],
			'published'      => boolval($row['published'])
	];
}

$response['data'] = $challenges;
Http::responseCode('OK');
echo json_encode($response);
