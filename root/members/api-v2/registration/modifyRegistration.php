<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/secrets/initDB.php');
include('../internal/checkAdmin.php'); //includes functions.php
include('../internal/checkAppState.php');
include('../internal/constants.php');

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

function getBooleanValue($val) {
    return (!isset($val) || $val == 0 || $val == "false") ? 0 : 1;
}

// Get parameters from the url
$hasParamAgreeToTerms = isset($_POST['agreeToTerms']);
$hasParamSetRegistered = isset($_POST['setRegistered']);
$hasParamSetPresent = isset($_POST['setPresent']);
$hasParamToggleRegistered = isset($_POST['toggleRegistered']);
$hasParamTogglePresent = isset($_POST['togglePresent']);
$setRegistered = getBooleanValue($_POST['setRegistered']);
$setPresent = getBooleanValue($_POST['setPresent']);
if (isset($_POST['uid'])) {
    $uid = trim($_POST['uid']);
} else {
    $response["error"] = "Need 'uid' to modify registration";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Admin safeguards
if (!$isAdmin) {
    $modifySomeoneElse = $uid != $userSession;
    $modifyAttendance = $hasParamSetPresent || $hasParamTogglePresent;
    if ($modifySomeoneElse || $modifyAttendance) {
        http_response_code($HTTP_FORBIDDEN);
        return;
    }
}

// Don't proceed unless we have changes to make
if ($hasParamAgreeToTerms || $hasParamToggleRegistered || $hasParamTogglePresent || $hasParamSetRegistered ||
        $hasParamSetPresent) {
    $userQuery = "SELECT u.isPresent, u.isRegistered, u.agreeToTerms FROM users u WHERE u.uid = ?";
} else {
    http_response_code($HTTP_NOT_MODIFIED);
    return;
}

// Get the user
$result = executeSqlForResult($MySQLi_CON, $userQuery, 'i', $uid);
if (hasRows($result)) {
    $user = getNextRow($result);
} else {
    http_response_code($HTTP_NOT_MODIFIED);
    return;
}

// Update the values or fall back to the user's current status
$isPresent = $hasParamSetPresent ? $setPresent : $user['isPresent'];
$isRegistered = $hasParamSetRegistered ? $setRegistered : $user['isRegistered'];
$agreeToTerms = $hasParamAgreeToTerms ? date("Y-m-d H:i:s") : $user['agreeToTerms'];

// Toggle the values based on the POST
if ($hasParamTogglePresent) {
    $isPresent = !getBooleanValue($isPresent);
}
if ($hasParamToggleRegistered) {
    $isRegistered = !getBooleanValue($isRegistered);
}

// Build the query, including agreeToTerms if it was included in the POST
$updateQuery = "UPDATE users u SET u.isPresent = ?, u.isRegistered = ?";
if (!!$hasParamAgreeToTerms) {
    $updateQuery = $updateQuery . ", u.agreeToTerms = NOW()";
}
$updateQuery = $updateQuery . " WHERE u.uid = ?";

// Update the database with the changes
$affectedRows = executeSqlForAffectedRows($MySQLi_CON, $updateQuery, 'iii', $isPresent, $isRegistered, $uid);
if ($affectedRows == null || $affectedRows === 0) {
    $response["error"] = "User registration change failed [DB-1]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}

// Get the user's updated information
$userResult = executeSqlForResult($MySQLi_CON, $userQuery, 'i', $uid);
if (hasRows($userResult)) {
    $user = getNextRow($userResult);
} else {
    $response["error"] = "User registration change failed [DB-2]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}

// Check the user's updated status
$isPresent = $user['isPresent'];
$isRegistered = $user['isRegistered'];
$agreeToTerms = $user['agreeToTerms'];

// Count the `registration_stats` rows for this user that are for this year
$numRows = 0;
$checkQuery = "SELECT * FROM registration_stats s WHERE s.conYear = ? AND s.uid = ?";
$checkResult = executeSqlForResult($MySQLi_CON, $checkQuery, 'ii', $conYear, $uid);
while ($result = getNextRow($result)) {
    $prevOrderId = $result['orderId'];
    $numRows++;
}

if ($isRegistered == 0) {
    if ($prevOrderId == null) {
        // Delete registration stats when users unregister (if there's no orderId)
        $deleteQuery = "DELETE FROM registration_stats WHERE uid = ? AND conYear = ?";
        executeSql($MySQLi_CON, $deleteQuery, 'ii', $uid, $conYear);
        $statsOperation = "DELETE";
        $statsQuery = $deleteQuery;
    } else {
        // If there is an orderId, just update
        $updateQuery = "UPDATE registration_stats s" .
                " SET s.isRegistered = ?, s.isPresent = ?, s.modified = CURRENT_TIMESTAMP()" .
                " WHERE s.uid = ? AND s.conYear = ?";
        executeSql($MySQLi_CON, $updateQuery, 'iiii', $isRegistered, $isPresent, $uid, $conYear);
        $statsOperation = "UPDATE";
        $statsQuery = $updateQuery;
    }
} else if ($numRows == 0) {
    // Update the registration stats

    // Insert a new row for this year's registration stats for this user
    $insertQuery = "INSERT INTO `registration_stats`(`uid`, `conYear`, `isRegistered`, `isPresent`, `orderId`)" .
            " VALUES (?, ?, ?, ?, ?)";
    executeSql($MySQLi_CON, $insertQuery, 'iiiis', $uid, $conYear, $isRegistered, $isPresent, $orderId);
    $statsOperation = "INSERT";
    $statsQuery = $insertQuery;
} else {
    // Update this year's registration stats for this user
    $updateQuery = "UPDATE registration_stats s" .
            " SET s.isRegistered = ?, s.isPresent = ?, s.modified = CURRENT_TIMESTAMP()" .
            " WHERE s.uid = ? AND s.conYear = ?";
    executeSql($MySQLi_CON, $updateQuery, 'iiii', $isRegistered, $isPresent, $uid, $conYear);
    $statsOperation = "UPDATE";
    $statsQuery = $updateQuery;
}

// Build the registration object
$obj = [
        "agreeToTerms"   => "$agreeToTerms",
        "isPresent"      => $isPresent == 1 ? true : false,
        "isRegistered"   => $isRegistered == 1 ? true : false,
        "statsOperation" => "$statsOperation",
        "statsQuery"     => "$statsQuery",
        "uid"            => $uid
];

// Set the starting points
if ($isRegistrationEnabled) {
    if ($setRegistered || ($hasParamToggleRegistered && $isRegistered)) {
        $startingPoints = 20;
        $updatePointsQuery = "UPDATE users u SET u.upoints = ? WHERE u.uid = ? AND u.upoints = 0";
        executeSql($MySQLi_CON, $updatePointsQuery, 'ii', $startingPoints, $uid);
    }
}

$response["data"] = $obj;
http_response_code($HTTP_OK);
echo json_encode($response);
