<?php
// Usage:
// 1. Connects to the database
// 2. Checks for admin
// 3. Accepts any combination of these parameters:
//		- conDay (0 < Number < 32)
//		- conMonth (0 < Number < 13)
//		- conYear (Number > 1000)
//		- enableRegistration|disableRegistration (no value)
//		- enablePoints|disablePoints (no value)
//		- premiumDueDateDisplay (String)
//		- premiumLastMonth (0 < Number < 13)
//		- premiumLastDay (0 < Number < 32)
//		- teeDueDateDisplay (String)
//		- teeLastMonth (0 < Number < 13)
//		- teeLastDay (0 < Number < 32)
//		- teePrice (0 < Number < 1000)

session_start();
$userSession = $_SESSION['userSession'];

include('internal/secrets/initDB.php');
include('internal/checkAdmin.php');
include('internal/checkAppState.php');
include('internal/constants.php');

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

if (!$isAdmin) {
    $response["error"] = "Must be admin to set app state";
    http_response_code($HTTP_FORBIDDEN);
    echo json_encode($response);
    return;
}

// Get parameters from the url
if (isset($_POST['conDay']) && $_POST['conDay'] > 0 && $_POST['conDay'] < 32) {
    $conDay = $_POST['conDay'];
}
if (isset($_POST['conMonth']) && $_POST['conMonth'] > 0 && $_POST['conMonth'] < 13) {
    $conMonth = $_POST['conMonth'];
}
if (isset($_POST['conYear']) && $_POST['conYear'] > 1000) {
    $conYear = $_POST['conYear'];
}
if (isset($_POST['badgePrice'])) {
    $badgePrice = $_POST['badgePrice'];
}
if (isset($_POST['enableRegistration'])) {
    $isRegistrationEnabled = 1;
} else if (isset($_POST['disableRegistration'])) {
    $isRegistrationEnabled = 0;
}
if (isset($_POST['enablePoints'])) {
    $isPointsEnabled = 1;
} else if (isset($_POST['disablePoints'])) {
    $isPointsEnabled = 0;
}

// Count the rows with the provided year
$numRows = 0;
$checkQuery = "SELECT s.conYear FROM app_state s WHERE s.conYear = ?";
$checkResult = executeSqlForResult($MySQLi_CON, $checkQuery, 'i', $conYear);
while ($checkResult->fetch_array()) {
    $numRows++;
}
$checkResult->free_result();

if (!$checkResult || $numRows == 0) {
    // Insert a new row
    $insertQuery = "INSERT INTO `app_state`(`conMonth`, `conDay`, `conYear`, `badgePrice`, `registrationEnabled`,
            `pointsEnabled`) VALUES (?, ?, ?, ?, ?, ?)";
    $insertResult = executeSqlForResult($MySQLi_CON, $insertQuery, 'iiisii', $conMonth, $conDay, $conYear,
            $badgePrice, $isRegistrationEnabled, $isPointsEnabled);
    $response["data"] = "Added entry for {$conYear}!";
} else {
    // Update an existing row
    $updateQuery = "UPDATE app_state s
            SET s.conDay = ?, s.conMonth = ?, s.conYear = ?, s.badgePrice = ?, s.registrationEnabled = ?,
                s.pointsEnabled = ?
            WHERE s.conYear = ?";
    $stmt = prepareSqlStatement($MySQLi_CON, $updateQuery, 'iiisiii', $conDay, $conMonth, $conYear, $badgePrice,
            $isRegistrationEnabled, $isPointsEnabled, $conYear);
    $stmt->execute();

    if ($stmt->affected_rows == null) {
        http_response_code($HTTP_NOT_MODIFIED);
        return;
    } else if ($stmt->affected_rows === 1) {
        $response["data"] = "Updated entry for {$conYear}!";
    } else {
        $response["error"] = "App state change failed [DB-2]";
        http_response_code($HTTP_INTERNAL_SERVER_ERROR);
        echo json_encode($response);
        return;
    }
}

http_response_code($HTTP_OK);
echo json_encode($response);
