<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}
include('dbconnect.php');
include('checkadmin.php');

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
if (!$pointsHistoryResult) {
    die("Points history query failed [DB-1]");
}
$historyList = [];
while ($row = $pointsHistoryResult->fetch_array()) {
    $historyList[] = $row;
}
$pointsHistoryResult->free_result();

// Build the history array string
$length = count($historyList);
$i = 0;
$rowArray = [];
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

    // Build an array of attributes
    $attrArray = [];
    $attrArray[] = "{\"timestamp\":\"{$hRow['timestamp']}\"";
    $attrArray[] = "{\"fromName\":\"{$fromName}\"";
    $attrArray[] = "{\"toName\":\"{$toName}\"";
    $attrArray[] = "{\"numPoints\":\"{$hRow['num_points']}\"";
    $attrArray[] = "{\"isAdminAction\":\"{$isAdminAction}\"";
    $attrArray[] = "{\"toEmail\":\"{$hRow['to_email']}\"";
    $attrArray[] = "{\"fromEmail\":\"{$hRow['from_email']}\"";

    // Add the row to the array
    $rowArray[] = "{" . join(",", $attrArray) . "}";
}
$json = "[" . join(",", $rowArray) . "]";

// Return the history array string
header('Content-Type: application/json');
die($json);
?>