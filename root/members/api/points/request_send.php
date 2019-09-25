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
$sourceUid = $userSession;
$targetUid = $MySQLi_CON->real_escape_string($_POST['target_uid']);
$requestNumPoints = $MySQLi_CON->real_escape_string($_POST['num_points']);

if (!isset($sourceUid) || !isset($targetUid) || !isset($requestNumPoints)) {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}

// Check the 'target' points
$query = "SELECT u.upoints FROM users u WHERE u.uid = ?";
$result = prepareSqlForResult($MySQLi_CON, $query, 'i', $targetUid);
if (!$result) {
    die("Requesting points failed [DB-1]");
}
$checkPoints = $result->fetch_array();
$result->free_result();
$targetPoints = $checkPoints['upoints'];

// Input validation
if ($requestNumPoints <= 0) {
    die("Must request a positive number of points");
} else if ($sourceUid == $targetUid) {
    die("Cannot request points from yourself. Nice try, asshole.");
}

// Remove any pending requests from source to target (so there's at most 1 request from each person)
$deleteQuery = "DELETE FROM points_request req WHERE req.source_uid = ? AND req.target_uid = ? AND status_id = 0";
$deleteResult = prepareSqlForResult($MySQLi_CON, $deleteQuery, 'ii', $sourceUid, $targetUid);

// Send the request
$requestQuery = "INSERT INTO points_request(source_uid, target_uid, num_points) VALUES (?, ?, ?)";
$requestResult = prepareSqlForResult($MySQLi_CON, $requestQuery, 'iii', $sourceUid, $targetUid, $requestNumPoints);
if ($requestResult) {
    die("SUCCESS");
} else {
    die("Error requesting points [DB-2]");
}
?>