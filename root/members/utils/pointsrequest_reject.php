<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include('dbconnect.php');
include('sql_functions.php');

// Get the submit data
$targetUid = $userSession;
$sourceUid = $MySQLi_CON->real_escape_string($_POST['source_uid']);

if (!isset($sourceUid) || !isset($targetUid)) {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}

// Check if the request exists
$query = "SELECT req.status_id
        FROM points_request req
        WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
$result = prepareSqlForResult($MySQLi_CON, $query, 'ii', $targetUid, $sourceUid);
if (!$result) {
    die("Rejecting request failed [DB-1]");
}
$checkRequest = $result->fetch_array();
$result->free_result();
$statusId = $checkRequest['status_id'];
if (!isset($statusId)) {
    die("Request does not exist");
}

// Update the status id to REJECTED(2)
$updateQuery = "UPDATE points_request req
        SET status_id = 2
        WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
$updateResult = prepareSqlForResult($MySQLi_CON, $updateQuery, 'ii', $targetUid, $sourceUid);
if ($updateResult) {
    die("SUCCESS");
} else {
    die("Error rejecting request [DB-2]");
}
?>