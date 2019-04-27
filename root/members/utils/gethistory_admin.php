<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}
include_once('dbconnect.php');
include_once('checkadmin.php');

if (!$isAdmin) {
    die("GTFO non-admin scum!");
}

// Get the points history
$pointsHistoryResult = $MySQLi_CON->query("SELECT h.*, u1.name AS from_name, u2.name AS to_name, u1.uid AS from_uid, u2.uid AS to_uid, u1.email AS from_email, u2.email AS to_email
	 FROM `points_history` h
	 JOIN `users` u1 ON u1.uid = h.from_uid
	 JOIN `users` u2 ON u2.uid = h.to_uid
	 WHERE h.from_uid != h.to_uid
	 ORDER BY h.timestamp DESC");
if (!$pointsHistoryResult)
    die("Points history query failed [DB-1]");
$historyList = array();
while ($row = $pointsHistoryResult->fetch_array()) {
    array_push($historyList, $row);
}
$pointsHistoryResult->free_result();

// Build the history array string
$length = count($historyList);
$i = 0;
$str = "[";
while ($i < $length) {
    $hRow = $historyList[$i];
    $i++;

    $toName = $hRow['to_name'];
    $fromName = $hRow['from_name'];
    $toUid = $hRow['to_uid'];
    $fromUid = $hRow['from_uid'];

    // Handle EVERYONE
    if ($toUid == -1) {
        $toName = "Everyone";
    }
    if ($fromUid == -1) {
        $fromName = "Everyone";
    }

    // Handle admin shenanigans
    $isAdminAction = $hRow['is_admin_action'];
    if ($isAdminAction == 1) {
        if ($toUid == $userSession) {
            if ($fromUid == $userSession) {
                // I changed myself as admin
                $fromName = "ADMIN (me)";
            } else {
                // An admin changed me
                $fromName = "ADMIN ({$fromName})"; //mask the admin's name
            }
        }
    }
    if ($str != "[") {
        // Add comma if previous items were added
        $str = "{$str},";
    }

    // Print a row
    $str = "{$str}{\"timestamp\":\"{$hRow['timestamp']}\",\"fromName\":\"{$fromName}\",\"toName\":\"{$toName}\",\"numPoints\":{$hRow['num_points']},\"isAdminAction\":{$isAdminAction},\"toEmail\":\"{$hRow['to_email']}\",\"fromEmail\":\"{$hRow['from_email']}\"}";
}
$str = "{$str}]";

// Return the history array string
header('Content-Type: application/json');
die($str);
?>