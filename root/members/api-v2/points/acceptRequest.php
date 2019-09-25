<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/secrets/initDB.php');
include('../internal/constants.php');
include('../internal/functions.php');

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

// Get the submit data
$targetUid = $userSession;
$sourceUid = trim($_POST['source_uid']);

if (!$targetUid) {
    $response["error"] = "Login is required";
    http_response_code($HTTP_NOT_AUTHORIZED);
    echo json_encode($response);
    return;
}

if (!$sourceUid) {
    $response["error"] = "source_uid is required";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Check if the request exists
$query = "SELECT req.status_id, req.num_points
        FROM points_request req
        WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
$requestResult = executeSqlForResult($MySQLi_CON, $query, 'ii', $targetUid, $sourceUid);
if (!hasRows($requestResult)) {
    $response["error"] = "Accepting request failed [DB-1]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}
$checkRequest = getNextRow($requestResult);
$statusId = $checkRequest['status_id'];
$numPoints = $checkRequest['num_points'];
if ($numPoints < 0) {
    $response["error"] = "Cannot accept request with negative points";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}
if (!isset($statusId)) {
    $response["error"] = "Request does not exist";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Check if the source has enough points
$query = "SELECT u.upoints FROM users u WHERE u.uid = ?";
$pointsResult = executeSqlForResult($MySQLi_CON, $query, 'i', $targetUid);
if (!hasRows($pointsResult)) {
    $response["error"] = "Checking points for request failed [DB-2]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}
$checkPoints = getNextRow($pointsResult);
$targetPoints = $checkPoints['upoints'];
if (!isset($targetPoints)) {
    $response["error"] = "Checking points for request failed [DB-3]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
} else if ($targetPoints < $numPoints) {
    $response["error"] = "Not enough points to accept request";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Update the status id to ACCEPTED(1)
$updateQuery = "UPDATE points_request req
        SET status_id = 1
        WHERE req.target_uid = ? AND req.source_uid = ? AND req.status_id = 0";
executeSql($MySQLi_CON, $updateQuery, 'ii', $targetUid, $sourceUid);

// Add an entry in history
$historyQuery = "INSERT INTO points_history(from_uid, to_uid, num_points) VALUES (?, ?, ?)";
executeSql($MySQLi_CON, $historyQuery, 'iii', $targetUid, $sourceUid, $numPoints);

// Send the points
$sendQuery = "UPDATE users from_u, users to_u
        SET from_u.upoints = from_u.upoints - ?, to_u.upoints = to_u.upoints + ?
        WHERE from_u.uid = ? AND to_u.uid = ?";
$info = executeSqlForInfo($MySQLi_CON, $sendQuery, 'iiii', $numPoints, $numPoints, $targetUid, $sourceUid);
if ($info["matched"] > 0) {
    http_response_code($HTTP_OK);
} else {
    $response["error"] = "Error sending requested points [DB-4]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
}

// Return the JSON
echo json_encode($response);
