<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/secrets/initDB.php');
include('../internal/constants.php');
include('../internal/checkAdmin.php'); //includes functions.php

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

if (!$userSession) {
    $response["error"] = "Login is required";
    http_response_code($HTTP_NOT_AUTHORIZED);
    echo json_encode($response);
    return;
} else if (!$isAdmin) {
    $response["error"] = "You are not an admin! GTFO.";
    http_response_code($HTTP_FORBIDDEN);
    echo json_encode($response);
    return;
}

// Get the submit data
$fromUid = $userSession;
$toUid = trim($_POST['target_uid']);
$sendNumPoints = intval($_POST['num_points']);

if (!$toUid) {
    $response["error"] = "target_uid is required";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
} else if ($sendNumPoints == 0) {
    $response["error"] = "Must send a non-zero number of points";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Add an entry in history
$historyQuery = "INSERT INTO points_history(from_uid, to_uid, num_points, is_admin_action) VALUES (?, ?, ?, ?)";
executeSql($MySQLi_CON, $historyQuery, 'iiii', $fromUid, $toUid, $sendNumPoints, $isAdmin);

if ($toUid == -1) {
    // Send the points to everyone (without affecting the admin's points)
    $sendQuery = "UPDATE users u SET u.upoints = u.upoints + {$sendNumPoints} WHERE u.isPresent = 1";
    $info = executeSqlForInfo($MySQLi_CON, $sendQuery, 'i', $sendNumPoints);
} else {
    // Send the points (without affecting the admin's points)
    $sendQuery = "UPDATE users u SET u.upoints = u.upoints + ? WHERE u.uid = ?";
    $info = executeSqlForInfo($MySQLi_CON, $sendQuery, 'ii', $sendNumPoints, $toUid);
}
if ($info["matched"] > 0) {
    http_response_code($HTTP_OK);
} else {
    $response["error"] = "Error sending points [DB-2]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
}

// Return the JSON
echo json_encode($response);
