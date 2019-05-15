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

// Get parameters from the url

if (isset($_GET['forAdmin'])) {
    $forAdmin = 1;
} else if (isset($_GET['forCheckIn'])) {
    $forCheckIn = 1;
} else if (isset($_GET['forTeamSort'])) {
    $forTeamSort = 1;
} else if (isset($_GET['forEmailList'])) {
    $forEmailList = 1;
}

if (isset($forAdmin) && $isAdmin) {
    $userListQuery = "SELECT u.uid, u.email, u.name, u.upoints, h.housename, IF(u.isRegistered = -1, 1, 0) AS isHouse
		 FROM users u
		 JOIN house h ON u.houseid = h.houseid
		 WHERE u.isPresent = 1 OR u.isRegistered = -1
		 ORDER BY h.housename ASC, isHouse DESC, u.upoints DESC, u.name ASC";
} else if (isset($forCheckIn) && $isAdmin) {
    $userListQuery = "SELECT u.uid, u.email, u.name, u.isPresent, u.isRegistered, h.housename
		 FROM users u
		 JOIN house h ON u.houseid = h.houseid
		 ORDER BY u.name ASC, u.isRegistered DESC";
} else if (isset($forTeamSort) && $isAdmin) {
    $userListQuery = "SELECT u.uid, u.email, u.name, u.isPresent, u.isRegistered, h.housename, h.houseid
		 FROM users u
		 JOIN house h ON u.houseid = h.houseid
		 WHERE u.isPresent = 1
		 ORDER BY h.housename ASC";
} else if (isset($forEmailList) && $isAdmin) {
    $userListQuery = "SELECT u.email FROM users u";
} else {
    $userListQuery = "SELECT u.name, u.email, h.housename
		 FROM users u
		 JOIN house h ON u.houseid = h.houseid
		 WHERE u.isRegistered = 1";//TODO: modify to only include present
}

// Get the list of users
$userListResult = $MySQLi_CON->query($userListQuery);
if (!$userListResult)
    die("User list query failed [DB-1]");
$userList = [];
while ($row = $userListResult->fetch_array()) {
    $userList[] = $row;
}
$userListResult->free_result();

// Build an array of users
$length = count($userList);
$i = 0;
$rowArray = [];
while ($i < $length) {
    $row = $userList[$i];
    $i++;

    $uid = $row['uid'];
    $email = $row['email'];
    $name = $row['name'];
    $upoints = $row['upoints'];
    $housename = $row['housename'];
    $houseid = $row['houseid'];
    $favoriteAnimal = $row['favoriteAnimal'];
    $favoriteBooze = $row['favoriteBooze'];
    $favoriteNerdism = $row['favoriteNerdism'];
    $isPresent = $row['isPresent'];
    $isRegistered = $row['isRegistered'];
    $isHouse = $row['isHouse'];

    // Build an array of attributes
    $attrArray = [];
    if (isset($uid)) $attrArray[] = "\"uid\":{$uid}";
    if (isset($email)) $attrArray[] = "\"email\":\"{$email}\"";
    if (isset($name)) $attrArray[] = "\"name\":\"{$name}\"";
    if (isset($upoints)) $attrArray[] = "\"upoints\":{$upoints}";
    if (isset($housename)) $attrArray[] = "\"housename\":\"{$housename}\"";
    if (isset($houseid)) $attrArray[] = "\"houseid\":\"{$houseid}\"";
    if (isset($favoriteAnimal)) $attrArray[] = "\"favoriteAnimal\":\"{$favoriteAnimal}\"";
    if (isset($favoriteBooze)) $attrArray[] = "\"favoriteBooze\":\"{$favoriteBooze}\"";
    if (isset($favoriteNerdism)) $attrArray[] = "\"favoriteNerdism\":\"{$favoriteNerdism}\"";
    if (isset($isPresent)) $attrArray[] = "\"isPresent\":{$isPresent}";
    if (isset($isRegistered)) $attrArray[] = "\"isRegistered\":{$isRegistered}";
    if (isset($isHouse)) $attrArray[] = "\"isHouse\":{$isHouse}";

    // Add the row to the array
    $rowArray[] = "{" . join(",", $attrArray) . "}";
}
$json = "[" . join(",", $rowArray) . "]";

// Return the history array string
header('Content-Type: application/json');
die($json);
?>