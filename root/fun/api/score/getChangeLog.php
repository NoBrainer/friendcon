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

// Get the change log entries
$result = Sql::executeSqlForResult("SELECT * FROM scoreChanges");
$entries = [];
while ($row = Sql::getNextRow($result)) {
	$entry = [
			'updateTime'     => General::stringToDate($row['updateTime']),
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
Http::responseCode('OK');
echo json_encode($response);
