<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    die();
}
include_once('dbconnect.php');
include_once('checkadmin.php');
if (!$isAdmin) {
    // If not admin, go to the main homepage
    die();
}

// Get parameters from the url
if (isset($_GET['uid'])) {
    // Sorting a single user
    $uid = $MySQLi_CON->real_escape_string($_GET['uid']);
} else {
    die();
}

// Setting the user to a specific house instead of random sorting
if (isset($_GET['housename'])) {
    // Get the parameter from the url
    $housename = $MySQLi_CON->real_escape_string($_GET['housename']);

    // Get the house id from the house name
    $houseQuery = "SELECT h.houseid
		 FROM house h
		 WHERE h.housename = '{$housename}'";
    $houseResult = $MySQLi_CON->query($houseQuery);
    if (!$houseResult)
        die("Error checking user house [DB-1]");
    $row = $houseResult->fetch_array();
    $houseResult->free_result();
    $houseid = $row["houseid"];

    // Make the update call
    $updateQuery = "UPDATE users u
		 SET u.houseid = {$houseid}
		 WHERE u.uid = {$uid}";
    $updateResult = $MySQLi_CON->query($updateQuery);
    if (!$updateResult)
        die("Failed to set house [DB-2]");

    // Return the house name
    die($housename);
}

// See if the user already has a house
$userHouseQuery = "SELECT u.houseid, h.housename
	 FROM users u
	 JOIN house h ON u.houseid = h.houseid
	 WHERE u.uid = {$uid}";
$userHouseResult = $MySQLi_CON->query($userHouseQuery);
if (!$userHouseResult)
    die("Error checking user house [DB-3]");
$row = $userHouseResult->fetch_array();
$userHouseResult->free_result();
$houseid = $row["houseid"];
$housename = $row["housename"];
if ($houseid != 0) {
    // Just return the house name since we only want to sort unsorted people
    die($housename);
}

// Get the total counts for each house
$houseQuery = "SELECT u.houseid, COUNT(*) AS `count`
	 FROM users u
	 WHERE u.houseid != 0
	 GROUP BY u.houseid";

$houseResult = $MySQLi_CON->query($houseQuery);
if (!$houseResult)
    die("House query failed [DB-4]");
$houseList = array();
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
    array_push($houseList, $row);
}
$houseResult->free_result();

// Make sure all houses are included in $houseList
if (!$hasHouse1) {
    $row = array("houseid" => "1", "count" => 0);
    array_push($houseList, $row);
}
if (!$hasHouse2) {
    $row = array("houseid" => "2", "count" => 0);
    array_push($houseList, $row);
}
if (!$hasHouse3) {
    $row = array("houseid" => "3", "count" => 0);
    array_push($houseList, $row);
}
if (!$hasHouse4) {
    $row = array("houseid" => "4", "count" => 0);
    array_push($houseList, $row);
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
$updateQuery = "UPDATE users u
	 SET u.houseid = {$pickedHouseId}
	 WHERE u.uid = {$uid} AND u.houseid = 0 AND u.isPresent = 1";

// Make the update call
$updateResult = $MySQLi_CON->query($updateQuery);
if (!$updateResult)
    die("Sorting user failed [DB-5]");

// Get the house name
$houseNameQuery = "SELECT h.housename
	 FROM house h
	 WHERE h.houseid = {$pickedHouseId}";
$houseNameResult = $MySQLi_CON->query($houseNameQuery);
if ($houseNameResult) {
    // Return the name of the house
    $row = $houseNameResult->fetch_array();
    $houseNameResult->free_result();
    $houseName = $row["housename"];
    die($houseName);
} else {
    // Return the house number
    die("House #{$pickedHouseId}");
}
?>