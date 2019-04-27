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
$result = $MySQLi_CON->query("SELECT req.status_id
	 FROM points_request req
	 WHERE req.target_uid={$targetUid} AND req.source_uid={$sourceUid} AND req.status_id=0");
if (!$result)
    die("Rejecting request failed [DB-1]");
$checkRequest = $result->fetch_array();
$result->free_result();
$statusId = $checkRequest['status_id'];
if (!isset($statusId)) {
    die("Request does not exist");
}

// Update the status id to REJECTED(2)
$updateQuery = "UPDATE points_request req
	 SET status_id=2
	 WHERE req.target_uid={$targetUid} AND req.source_uid={$sourceUid} AND req.status_id=0";
if ($MySQLi_CON->query($updateQuery)) {
    die("SUCCESS");
} else {
    die("Error rejecting request [DB-2]");
}
?>