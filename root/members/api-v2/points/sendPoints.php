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
$fromUid = $userSession;
$toUid = trim($_POST['to_uid']);
$sendNumPoints = intval($_POST['num_points']);

if (!$fromUid) {
    $response["error"] = "Login is required";
    http_response_code($HTTP_NOT_AUTHORIZED);
    echo json_encode($response);
    return;
}

if (!$toUid) {
    $response["error"] = "to_uid is required";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
} else if ($sendNumPoints < 1) {
    $response["error"] = "Must send a positive number of points";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
} else if ($fromUid == $toUid) {
    $response["error"] = "Cannot send points to yourself. Nice try, asshole.";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Check the 'from' points
$query = "SELECT u.upoints FROM users u WHERE u.uid = ?";
$info = executeSqlForInfo($MySQLi_CON, $deleteQuery, 'i', $userSession);
if ($info["matched"] < 1) {
    $response["error"] = "Sending points failed [DB-1]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}

// Add an entry in history
$historyQuery = "INSERT INTO points_history(from_uid, to_uid, num_points) VALUES (?, ?, ?)";
executeSql($MySQLi_CON, $historyQuery, 'iii', $fromUid, $toUid, $sendNumPoints);

// Send the points
$sendQuery = "UPDATE users from_u, users to_u
        SET from_u.upoints = from_u.upoints - ?, to_u.upoints = to_u.upoints + ?
        WHERE from_u.uid = ? AND to_u.uid = ?";
$info = executeSqlForInfo($MySQLi_CON, $sendQuery, 'iiii', $sendNumPoints, $sendNumPoints, $fromUid, $toUid);
if ($info["matched"] > 0) {
    http_response_code($HTTP_OK);
} else {
    $response["error"] = "Error sending points [DB-2]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
}

// Return the JSON
echo json_encode($response);
