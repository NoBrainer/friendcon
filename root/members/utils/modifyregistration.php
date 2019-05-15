<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include('dbconnect.php');
include('checkadmin.php');
include('check_app_state.php');

function getBooleanValue($val) {
    if (!isset($val) || $val == 0 || $val == "false") {
        return 0;
    } else {
        return 1;
    }
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
    $uid = $MySQLi_CON->real_escape_string($_POST['uid']);
} else {
    die("Need 'uid' to modify registration");
}

// Admin safeguards
if (!$isAdmin) {
    if ($uid != $userSession) {
        die("Only admins can modify others");
    }
    $hasParamSetPresent = 0;
    $hasParamTogglePresent = 0;
}

// Don't proceed unless we have changes to make
if ($hasParamAgreeToTerms || $hasParamToggleRegistered || $hasParamTogglePresent || $hasParamSetRegistered || $hasParamSetPresent) {
    $userQuery = "SELECT u.isPresent, u.isRegistered, u.agreeToTerms
		 FROM users u
		 WHERE u.uid = {$uid}";
} else {
    die("No changes made");
}

// Get the user
$result = $MySQLi_CON->query($userQuery);
$user = $result->fetch_array();
$result->free_result();

// Update the values or fall back to the user's current status
$isPresent = $hasParamSetPresent ? $setPresent : $user['isPresent'];
$isRegistered = $hasParamSetRegistered ? $setRegistered : $user['isRegistered'];
$agreeToTerms = $hasParamAgreeToTerms ? date("Y-m-d H:i:s") : $user['agreeToTerms'];

// Toggle the values based on the POST
if ($hasParamTogglePresent) {
    $isPresent = ($isPresent ? 0 : 1);
}
if ($hasParamToggleRegistered) {
    $isRegistered = ($isRegistered ? 0 : 1);
}

// Include agreeToTerms in the query if it was included in the POST
$agreeQueryPiece = !!$hasParamAgreeToTerms ? ", u.agreeToTerms = NOW()" : "";

// Update the database with the changes
$updateQuery = "UPDATE users u
	 SET u.isPresent = {$isPresent}, u.isRegistered = {$isRegistered}{$agreeQueryPiece}
	 WHERE u.uid = {$uid}";
$updateResult = $MySQLi_CON->query($updateQuery);
if (!$updateResult) {
    die("User registration change failed [DB-1]");
}

// Get the user's updated information
$result = $MySQLi_CON->query($userQuery);
$user = $result->fetch_array();
$result->free_result();

// Check the user's updated status
$isPresent = $user['isPresent'];
$isRegistered = $user['isRegistered'];
$agreeToTerms = $user['agreeToTerms'];

// Count the `registration_stats` rows for this user that are for this year
$numRows = 0;
$checkResult = $MySQLi_CON->query("SELECT * FROM registration_stats s
	 WHERE s.conYear = {$conYear} AND s.uid = {$uid}");
while ($result = $checkResult->fetch_array()) {
    $prevOrderId = $result['orderId'];
    $numRows++;
}
$checkResult->free_result();

if ($isRegistered == 0) {
    if ($prevOrderId == null) {
        // Delete registration stats when users unregister (if there's no orderId)
        $deleteQuery = "DELETE FROM registration_stats WHERE uid = {$uid} AND conYear = {$conYear}";
        $MySQLi_CON->query($deleteQuery);
        $statsOperation = "DELETE";
        $statsQuery = $deleteQuery;
    } else {
        // If there is an orderId, just update
        $updateQuery = "UPDATE registration_stats s
		 SET s.isRegistered = {$isRegistered}, s.isPresent = {$isPresent}, s.modified = CURRENT_TIMESTAMP()
		 WHERE s.uid = {$uid} AND s.conYear = {$conYear}";
        $MySQLi_CON->query($updateQuery);
        $statsOperation = "UPDATE";
        $statsQuery = $updateQuery;
    }
} else if (!$checkResult || $numRows == 0) {
    // Update the registration stats

    // Insert a new row for this year's registration stats for this user
    $insertQuery = "INSERT INTO `registration_stats`(`uid`, `conYear`, `isRegistered`, `isPresent`, `orderId`)
		VALUES ({$uid}, {$conYear}, {$isRegistered}, {$isPresent}, {$orderId})";
    $MySQLi_CON->query($insertQuery);
    $statsOperation = "INSERT";
    $statsQuery = $insertQuery;
} else {
    // Update this year's registration stats for this user
    $updateQuery = "UPDATE registration_stats s
		 SET s.isRegistered = {$isRegistered}, s.isPresent = {$isPresent}, s.modified = CURRENT_TIMESTAMP()
		 WHERE s.uid = {$uid} AND s.conYear = {$conYear}";
    $MySQLi_CON->query($updateQuery);
    $statsOperation = "UPDATE";
    $statsQuery = $updateQuery;
}

// Build JSON with the updated data
$str = "{";
$str = "{$str}\"uid\":\"{$uid}\",";
$str = "{$str}\"isPresent\":{$isPresent},";
$str = "{$str}\"isRegistered\":{$isRegistered},";
$str = "{$str}\"agreeToTerms\":\"{$agreeToTerms}\",";
$str = "{$str}\"statsOperation\":\"{$statsOperation}\",";
$str = "{$str}\"statsQuery\":\"{$statsQuery}\"";
$str = "{$str}}";

// Set the starting points
if ($isRegistrationEnabled) {
    if ($setRegistered || ($hasParamToggleRegistered && $isRegistered)) {
        $startingPoints = 20;
        $updatePointsQuery = "UPDATE users u
			 SET u.upoints = {$startingPoints}
			 WHERE u.uid = {$uid} AND u.upoints = 0";
        $MySQLi_CON->query($updatePointsQuery);
    }
}

// Return the JSON string
header('Content-Type: application/json');
die($str);
?>