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

// Get the submit data
$targetUid = $userSession;
$sourceUid = $MySQLi_CON->real_escape_string($_POST['source_uid']);

if (!isset($sourceUid) || !isset($targetUid)) {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}

// Check if the request exists
$query = "SELECT req.status_id, req.num_points
        FROM points_request req
        WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
$requestResult = prepareSqlForResult($MySQLi_CON, $query, 'ii', $targetUid, $sourceUid);
if (!$requestResult) {
    die("Accepting request failed [DB-1]");
}
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
$query = "SELECT u.upoints FROM users u WHERE u.uid = ?";
$pointsResult = prepareSqlForResult($MySQLi_CON, $query, 'i', $targetUid);
if (!$pointsResult) {
    die("Checking points for request failed [DB-2]");
}
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
        SET status_id = 1
        WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
$updateResult = prepareSqlForResult($MySQLi_CON, $updateQuery, 'ii', $targetUid, $sourceUid);

// Add an entry in history
$historyQuery = "INSERT INTO points_history(from_uid, to_uid, num_points) VALUES (?, ?, ?)";
$historyResult = prepareSqlForResult($MySQLi_CON, $historyQuery, 'iii', $targetUid, $sourceUid, $numPoints);

// Send the points
$sendQuery = "UPDATE users from_u, users to_u
        SET from_u.upoints = from_u.upoints - ?, to_u.upoints = to_u.upoints + ?
        WHERE from_u.uid = ? AND to_u.uid = ?";
$sendResult = prepareSqlForResult($MySQLi_CON, $sendQuery, 'iiii', $numPoints, $numPoints, $targetUid, $sourceUid);
if ($sendResult) {
    die("SUCCESS");
} else {
    die("Error sending requested points [DB-4]");
}
?>