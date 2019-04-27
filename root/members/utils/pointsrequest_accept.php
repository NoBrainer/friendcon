<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include_once('dbconnect.php');

// Get the submit data
$targetUid = $userSession;
$sourceUid = $MySQLi_CON->real_escape_string($_POST['source_uid']);

if (!isset($sourceUid) || !isset($targetUid)) {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}

// Check if the request exists
$requestResult = $MySQLi_CON->query("SELECT req.status_id, req.num_points
	 FROM points_request req
	 WHERE req.target_uid={$targetUid} AND req.source_uid={$sourceUid} AND req.status_id=0");
if (!$requestResult)
    die("Accepting request failed [DB-1]");
$checkRequest = $requestResult->fetch_array();
$requestResult->free_result();
$statusId = $checkRequest['status_id'];
$numPoints = $checkRequest['num_points'];
if ($numPoints < 0) {
    die("Cannot accept request with negative points");
}
if (!isset($statusId)) {
    die("Request does not exist");
}

// Check if the source has enough points
$pointsResult = $MySQLi_CON->query("SELECT u.upoints
	 FROM users u
	 WHERE u.uid={$targetUid}");
if (!$pointsResult)
    die("Checking points for request failed [DB-2]");
$checkPoints = $pointsResult->fetch_array();
$pointsResult->free_result();
$targetPoints = $checkPoints['upoints'];
if (!isset($targetPoints)) {
    die("Checking points for request failed [DB-3]");
} else if ($targetPoints < $numPoints) {
    die("Not enough points to accept request");
}

// Update the status id to ACCEPTED(1)
$updateQuery = "UPDATE points_request req
	 SET status_id=1
	 WHERE req.target_uid={$targetUid} AND req.source_uid={$sourceUid} AND req.status_id=0";
$MySQLi_CON->query($updateQuery);

// Add an entry in history
$historyQuery = "INSERT INTO points_history(from_uid, to_uid, num_points)
	 VALUES ({$targetUid}, {$sourceUid}, {$numPoints})";
$MySQLi_CON->query($historyQuery);

// Send the points
$sendQuery = "UPDATE users from_u, users to_u
	 SET from_u.upoints = from_u.upoints - {$numPoints}, to_u.upoints = to_u.upoints + {$numPoints}
	 WHERE from_u.uid = {$targetUid} AND to_u.uid = {$sourceUid}";
if ($MySQLi_CON->query($sendQuery)) {
    die("SUCCESS");
} else {
    die("Error sending requested points [DB-4]");
}
?>