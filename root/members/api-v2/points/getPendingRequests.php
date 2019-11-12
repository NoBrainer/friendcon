<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/secrets/initDB.php');
include('../internal/constants.php');
include('../internal/functions.php');

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

// Get the points request rows
$query = "SELECT r.*, u.name AS source_name" .
        " FROM `points_request` r" .
        " JOIN `users` u ON u.uid = r.source_uid" .
        " WHERE r.target_uid = ? AND r.status_id = 0"; //target=me AND status=PENDING
$pointsRequestResult = prepareSqlForResult($MySQLi_CON, $query, 'i', $userSession);
if (!hasRows($pointsRequestResult)) {
    $response["data"] = [];
    http_response_code($HTTP_OK);
    echo json_encode($response);
    return;
}

// Build the request array
$requestArr = [];
while ($row = getNextRow($pointsRequestResult)) {
    // Build the request entry
    $entry = [
            "timestamp"  => "{$row['timestamp']}",
            "targetUid"  => "{$row['target_uid']}",
            "sourceUid"  => "{$row['source_uid']}",
            "numPoints"  => $row['num_points'],
            "sourceName" => "{$row['source_name']}"
    ];

    // Add the entry
    $requestArr[] = $entry;
}
$response["data"] = $requestArr;

// Return the JSON
http_response_code($HTTP_OK);
echo json_encode($response);
