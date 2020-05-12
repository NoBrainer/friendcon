<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');
include('../internal/checkAdmin.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

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
	$userListQuery = "SELECT u.uid, u.email, u.name, u.upoints, h.housename, IF(u.isRegistered = -1, 1, 0) AS isHouse" .
			" FROM users u" .
			" JOIN house h ON u.houseid = h.houseid" .
			" WHERE u.isPresent = 1 OR u.isRegistered = -1" .
			" ORDER BY h.housename ASC, isHouse DESC, u.upoints DESC, u.name ASC";
} else if (isset($forCheckIn) && $isAdmin) {
	$userListQuery = "SELECT u.uid, u.email, u.name, u.isPresent, u.isRegistered, h.housename" .
			" FROM users u" .
			" JOIN house h ON u.houseid = h.houseid" .
			" ORDER BY u.name ASC, u.isRegistered DESC";
} else if (isset($forTeamSort) && $isAdmin) {
	$userListQuery = "SELECT u.uid, u.email, u.name, u.isPresent, u.isRegistered, h.housename, h.houseid" .
			" FROM users u" .
			" JOIN house h ON u.houseid = h.houseid" .
			" WHERE u.isPresent = 1" .
			" ORDER BY h.housename ASC";
} else if (isset($forEmailList) && $isAdmin) {
	$userListQuery = "SELECT u.email FROM users u";
} else {
	$userListQuery = "SELECT u.name, u.email, h.housename" .
			" FROM users u" .
			" JOIN house h ON u.houseid = h.houseid" .
			" WHERE u.isRegistered = 1";//TODO: modify to only include present?
}

// Get the list of users
$userListResult = $mysqli->query($userListQuery);
if (!$userListQuery) {
	$response['error'] = "User list query failed [DB-1]";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	return;
}

// Build an array of users
$users = [];
while ($row = getNextRow($userListResult)) {
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

	// Build the user entry
	$user = [];
	if (isset($uid)) $user['uid'] = intval($uid);
	if (isset($email)) $user['email'] = "$email";
	if (isset($name)) $user['name'] = "$name";
	if (isset($upoints)) $user['upoints'] = intval($upoints);
	if (isset($housename)) $user['housename'] = "$housename";
	if (isset($houseid)) $user['houseid'] = "$houseid";
	if (isset($favoriteAnimal)) $user['favoriteAnimal'] = "$favoriteAnimal";
	if (isset($favoriteBooze)) $user['favoriteBooze'] = "$favoriteBooze";
	if (isset($favoriteNerdism)) $user['favoriteNerdism'] = "$favoriteNerdism";
	if (isset($isPresent)) $user['isPresent'] = boolval($isPresent);
	if (isset($isRegistered)) $user['isRegistered'] = boolval($isRegistered);
	if (isset($isHouse)) $user['isHouse'] = boolval($isHouse);

	// Add the entry
	$users[] = $user;
}

$response['data'] = $users;
http_response_code(HTTP['OK']);
echo json_encode($response);
