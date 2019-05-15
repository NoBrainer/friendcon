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
$requestList = array();
while ($row = $pointsRequestResult->fetch_array()) {
    array_push($requestList, $row);
}
$pointsRequestResult->free_result();

// Build the request array string
$length = count($requestList);
$i = 0;
$str = "[";
while ($i < $length) {
    $reqRow = $requestList[$i];
    $i++;
    if ($str != "[") {
        // Add comma if previous items were added
        $str = "{$str},";
    }

    // Print a row of json
    $str = "{$str}{\"timestamp\":\"{$reqRow['timestamp']}\",\"targetUid\":\"{$reqRow['target_uid']}\",\"sourceUid\":\"{$reqRow['source_uid']}\",\"numPoints\":{$reqRow['num_points']},\"sourceName\":\"{$reqRow['source_name']}\"}";
}
$str = "{$str}]";

// Return the request array string
header('Content-Type: application/json');
die($str);
?>