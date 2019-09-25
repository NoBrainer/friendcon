<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/secrets/initDB.php');
include('../internal/constants.php');
include('../internal/functions.php');

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

// Check the user points
$query = "SELECT u.upoints FROM users u WHERE u.uid = ?";
$result = executeSqlForResult($MySQLi_CON, $query, 'i', $userSession);
if (!hasRows($result)) {
    $response["error"] = "Error getting points [DB-1]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}
$row = getNextRow($result);
$response["points"] = $row['upoints'];

// Return the points
http_response_code($HTTP_OK);
echo json_encode($response);
