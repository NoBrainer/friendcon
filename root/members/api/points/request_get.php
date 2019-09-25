<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include('../secrets/dbconnect.php');
include_once('../util/sql_functions.php');

// Get the points request rows
$query = "SELECT r.*, u.name AS source_name
        FROM `points_request` r
        JOIN `users` u ON u.uid = r.source_uid
        WHERE r.target_uid = ? AND r.status_id = 0"; //target=me AND status=PENDING
$pointsRequestResult = prepareSqlForResult($MySQLi_CON, $query, 'i', $userSession);
if (!$pointsRequestResult) {
    header('Content-Type: application/json');
    die("[]");
}
$requestList = [];
while ($row = $pointsRequestResult->fetch_array()) {
    $requestList[] = $row;
}
$pointsRequestResult->free_result();

// Build the request array
$length = count($requestList);
$i = 0;
$requestArr = [];
while ($i < $length) {
    $reqRow = $requestList[$i];
    $i++;

    // Build the request entry
    $entry = [
            "timestamp"  => "{$reqRow['timestamp']}",
            "targetUid"  => "{$reqRow['target_uid']}",
            "sourceUid"  => "{$reqRow['source_uid']}",
            "numPoints"  => $reqRow['num_points'],
            "sourceName" => "{$reqRow['source_name']}"
    ];

    // Add the entry
    $requestArr[] = $entry;
}

// Return the JSON
header('Content-Type: application/json');
die(json_encode($requestArr));
?>