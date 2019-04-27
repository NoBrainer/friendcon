<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include_once('dbconnect.php');
include_once('checkadmin.php');

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
    $userListQuery = "SELECT u.uid, u.email, u.name, u.isPresent, u.isRegistered, u.isPaid, h.housename
		 FROM users u
		 JOIN house h ON u.houseid = h.houseid
		 ORDER BY u.name ASC, u.isRegistered DESC";
} else if (isset($forTeamSort) && $isAdmin) {
    $userListQuery = "SELECT u.uid, u.email, u.name, u.isPresent, u.isRegistered, u.isPaid, h.housename, h.houseid
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
$userList = array();
while ($row = $userListResult->fetch_array()) {
    array_push($userList, $row);
}
$userListResult->free_result();

// Build an array of users
$length = count($userList);
$i = 0;
$str = "[";
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
    $isPaid = $row['isPaid'];
    $isPresent = $row['isPresent'];
    $isRegistered = $row['isRegistered'];
    $isHouse = $row['isHouse'];

    if ($str != "[") {
        // Add comma if previous items were added
        $str = "{$str},";
    }

    // Row start
    $str = "{$str}{";

    // Print each attribute
    $attrsPrinted = 0;
    if (isset($uid)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"uid\":{$uid}";
        $attrsPrinted++;
    }
    if (isset($email)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"email\":\"{$email}\"";
        $attrsPrinted++;
    }
    if (isset($name)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"name\":\"{$name}\"";
        $attrsPrinted++;
    }
    if (isset($upoints)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"upoints\":{$upoints}";
        $attrsPrinted++;
    }
    if (isset($housename)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"housename\":\"{$housename}\"";
        $attrsPrinted++;
    }
    if (isset($houseid)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"houseid\":\"{$houseid}\"";
        $attrsPrinted++;
    }
    if (isset($favoriteAnimal)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"favoriteAnimal\":\"{$favoriteAnimal}\"";
        $attrsPrinted++;
    }
    if (isset($favoriteBooze)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"favoriteBooze\":\"{$favoriteBooze}\"";
        $attrsPrinted++;
    }
    if (isset($favoriteNerdism)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"favoriteNerdism\":\"{$favoriteNerdism}\"";
        $attrsPrinted++;
    }
    if (isset($isPaid)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"isPaid\":{$isPaid}";
        $attrsPrinted++;
    }
    if (isset($isPresent)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"isPresent\":{$isPresent}";
        $attrsPrinted++;
    }
    if (isset($isRegistered)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"isRegistered\":{$isRegistered}";
        $attrsPrinted++;
    }
    if (isset($isHouse)) {
        if ($attrsPrinted > 0)
            $str = "{$str},"; //add comma
        $str = "{$str}\"isHouse\":{$isHouse}";
        $attrsPrinted++;
    }

    // Row end
    $str = "{$str}}";
}
$str = "{$str}]";

// Return the history array string
header('Content-Type: application/json');
die($str);
?>