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
$sourceUid = $userSession;
$targetUid = trim($_POST['target_uid']);
$requestNumPoints = intval($_POST['num_points']);

if (!$sourceUid) {
    $response["error"] = "Login is required";
    http_response_code($HTTP_NOT_AUTHORIZED);
    echo json_encode($response);
    return;
}

if (!$targetUid) {
    $response["error"] = "target_uid is required";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
} else if ($requestNumPoints < 1) {
    $response["error"] = "Must request a positive number of points";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
} else if ($sourceUid == $targetUid) {
    $response["error"] = "Cannot request points from yourself. Nice try, asshole.";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Check the 'target' points
$query = "SELECT u.upoints FROM users u WHERE u.uid = ?";
$info = executeSqlForInfo($MySQLi_CON, $query, 'i', $targetUid);
if ($info["matched"] < 1) {
    $response["error"] = "Requesting points failed [DB-1]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}

// Remove any pending requests from source to target (so there's at most 1 request from each person)
$deleteQuery = "DELETE FROM points_request req WHERE req.source_uid = ? AND req.target_uid = ? AND status_id = 0";
executeSql($MySQLi_CON, $deleteQuery, 'ii', $sourceUid, $targetUid);

// Send the request
$requestQuery = "INSERT INTO points_request(source_uid, target_uid, num_points) VALUES (?, ?, ?)";
$info = executeSqlForInfo($MySQLi_CON, $requestQuery, 'iii', $sourceUid, $targetUid, $requestNumPoints);
if ($info["matched"] > 0) {
    http_response_code($HTTP_OK);
} else {
    $response["error"] = "Error requesting points [DB-2]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
}

// Return the JSON
echo json_encode($response);
