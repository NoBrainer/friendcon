<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include('dbconnect.php');

// Get the points request rows
$pointsRequestResult = $MySQLi_CON->query("SELECT r.*, u.name AS source_name
	 FROM `points_request` r
	 JOIN `users` u ON u.uid = r.source_uid
	 WHERE r.target_uid={$userSession} AND r.status_id=0" //target=me AND status=PENDING
);
if (!$pointsRequestResult) {
    header('Content-Type: application/json');
    die("[]");
}
$requestList = [];
while ($row = $pointsRequestResult->fetch_array()) {
    $requestList[] = $row;
}
$pointsRequestResult->free_result();

// Build the request array string
$length = count($requestList);
$i = 0;
$rowArray = [];
while ($i < $length) {
    $reqRow = $requestList[$i];
    $i++;

    // Build an array of attributes
    $attrArray = [];
    $attrArray[] = "\"timestamp\":\"{$reqRow['timestamp']}\"";
    $attrArray[] = "\"targetUid\":\"{$reqRow['target_uid']}\"";
    $attrArray[] = "\"sourceUid\":\"{$reqRow['source_uid']}\"";
    $attrArray[] = "\"numPoints\":\"{$reqRow['num_points']}\"";
    $attrArray[] = "\"sourceName\":\"{$reqRow['source_name']}\"";

    // Add the row to the array
    $rowArray[] = "{" . join(",", $attrArray) . "}";
}
$json = "[" . join(",", $rowArray) . "]";

// Return the request array string
header('Content-Type: application/json');
die($json);
?>