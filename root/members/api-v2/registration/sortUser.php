<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/secrets/initDB.php');
include('../internal/checkAdmin.php'); //includes functions.php
include('../internal/constants.php');

// Setup the content-type and response template
header($CONTENT_JSON);
$response = [];

if (!$isAdmin) {
    http_response_code($HTTP_FORBIDDEN);
    return;
}

// Get parameters from the url
if (isset($_GET['uid'])) {
    $uid = trim($_GET['uid']);
} else {
    $response["error"] = "'uid' required to sort user";
    http_response_code($HTTP_BAD_REQUEST);
    echo json_encode($response);
    return;
}

// Setting the user to a specific house instead of random sorting
if (isset($_GET['housename'])) {
    // Get the parameter from the url
    $housename = trim($_GET['housename']);

    // Get the house id from the house name
    $houseQuery = "SELECT h.houseid FROM house h WHERE h.housename = ?";
    $houseResult = executeSqlForResult($MySQLi_CON, $houseQuery, 's', $housename);
    if (hasRows($houseResult)) {
        $row = getNextRow($houseResult);
    } else {
        $response["error"] = "Invalid value provided for 'housename'";
        http_response_code($HTTP_BAD_REQUEST);
        echo json_encode($response);
        return;
    }
    $houseid = $row["houseid"];

    // Make the update call
    $updateQuery = "UPDATE users u SET u.houseid = ? WHERE u.uid = ?";
    $info = executeSqlForInfo($MySQLi_CON, $updateQuery, 'ii', $houseid, $uid);
    if ($info["matched"] == 0) {
        $response["error"] = "Failed to set house [DB-1]";
        http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    } else {
        // Success
        $response["data"] = $housename;
        http_response_code($HTTP_OK);
    }

    echo json_encode($response);
    return;
}

// See if the user already has a house
$userHouseQuery = "SELECT u.houseid, h.housename FROM users u JOIN house h ON u.houseid = h.houseid WHERE u.uid = ?";
$userHouseResult = executeSqlForResult($MySQLi_CON, $userHouseQuery, 'i', $uid);
if (hasRows($userHouseResult)) {
    $row = getNextRow($userHouseResult);
} else {
    $response["error"] = "Error checking user house [SORT-2]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}
$houseid = $row["houseid"];
$housename = $row["housename"];
if ($houseid != $UNSORTED) {
    // Already sorted, so just return the housename
    $response["data"] = $housename;
    http_response_code($HTTP_OK);
    echo json_encode($response);
    return;
}

// Get the total counts for each house
$houseQuery = "SELECT u.houseid, COUNT(*) AS `count` FROM users u WHERE u.houseid != 0 GROUP BY u.houseid";
$houseResult = $MySQLi_CON->query($houseQuery);
if (!$houseResult) {
    $response["error"] = "House query failed [SORT-3]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}

//TODO: clean up the logic for randomly picking a house... GROSS

$houseList = [];
$hasHouse1 = 0;
$hasHouse2 = 0;
$hasHouse3 = 0;
$hasHouse4 = 0;
while ($row = $houseResult->fetch_array()) {
    $houseId = $row["houseid"];
    switch($houseId) {
        case 1:
            $hasHouse1 = 1;
            break;
        case 2:
            $hasHouse2 = 1;
            break;
        case 3:
            $hasHouse3 = 1;
            break;
        case 4:
            $hasHouse4 = 1;
            break;
    }
    $houseList[] = $row;
}
$houseResult->free_result();

// Make sure all houses are included in $houseList
if (!$hasHouse1) {
    $row = ["houseid" => "1", "count" => 0];
    $houseList[] = $row;
}
if (!$hasHouse2) {
    $row = ["houseid" => "2", "count" => 0];
    $houseList[] = $row;
}
if (!$hasHouse3) {
    $row = ["houseid" => "3", "count" => 0];
    $houseList[] = $row;
}
if (!$hasHouse4) {
    $row = ["houseid" => "4", "count" => 0];
    $houseList[] = $row;
}

// Go through and remove houses if their member count is too high
$lowestCount = -1;
$lowestHouseId = -1;
$lowestIndex = -1;
$threshold = 1;
$i = 0;
while ($i < count($houseList)) {
    $row = $houseList[$i];

    $count = $row["count"];
    $houseid = $row["houseid"];

    if ($lowestCount == -1) {
        // Set the lowest house
        $lowestCount = $count;
        $lowestHouseId = $houseid;
        $lowestIndex = $i;
        $i++;
    } else if ($count < $lowestCount) {
        // Update the lowest house and restart
        $lowestCount = $count;
        $lowestHouseId = $houseid;
        $lowestIndex = $i;
        $i = 0;
    } else if ($count > $lowestCount + $threshold) {
        // Remove houses that are too high
        array_splice($houseList, $i, 1);
    } else {
        // Proceed without doing anything
        $i++;
    }
}

// Pick a house
$pickedHouseId = -1;
$numCandidates = count($houseList);
if ($numCandidates == 1) {
    // Pick the only remaining candidate
    $pickedHouseId = $houseList[0]["houseid"];
} else if ($numCandidates > 1) {
    // Pick a random one of the candidates
    $randomRow = $houseList[array_rand($houseList)];
    $pickedHouseId = $randomRow["houseid"];
} else {
    // No candidates, so pick from all four houses
    $pickedHouseId = rand(1, 4);
}

// Build the update query to only affect a user that is unsorted AND checked-in
$updateQuery = "UPDATE users u SET u.houseid = ? WHERE u.uid = ? AND u.houseid = 0 AND u.isPresent = 1";

// Make the update call
$info = executeSqlForInfo($MySQLi_CON, $updateQuery, 'ii', $pickedHouseId, $uid);
if ($info["matched"] === 0) {
    $response["error"] = "Sorting user failed [SORT-4]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    echo json_encode($response);
    return;
}

// Get the house name
$houseNameQuery = "SELECT h.housename FROM house h WHERE h.houseid = ?";
$houseNameResult = executeSqlForResult($MySQLi_CON, $houseNameQuery, 'i', $pickedHouseId);

if (hasRows($houseNameResult)) {
    // Success
    $row = getNextRow($houseNameResult);
    $response["data"] = $row["housename"];
    http_response_code($HTTP_OK);
} else {
    $response["error"] = "Unexpected error updating house [SORT-5]";
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
}
echo json_encode($response);
