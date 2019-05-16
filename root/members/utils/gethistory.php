<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}
include('dbconnect.php');

// Get the points history
$pointsHistoryResult = $MySQLi_CON->query("SELECT h.*, u1.name AS from_name, u2.name AS to_name, u1.uid AS from_uid, u2.uid AS to_uid, u1.email AS from_email, u2.email AS to_email
	 FROM `points_history` h
	 JOIN `users` u1 ON u1.uid = h.from_uid
	 JOIN `users` u2 ON u2.uid = h.to_uid
	 WHERE h.from_uid != h.to_uid
	 ORDER BY h.timestamp DESC");
if (!$pointsHistoryResult)
    die("Points history query failed [DB-1]");
$historyList = [];
while ($row = $pointsHistoryResult->fetch_array()) {
    $historyList[] = $row;
}
$pointsHistoryResult->free_result();

// Build the array of history entries
$length = count($historyList);
$i = 0;
$entryArr = [];
while ($i < $length) {
    $hRow = $historyList[$i];
    $i++;
    if ($hRow['from_uid'] != $userSession && $hRow['to_uid'] != $userSession) {
        continue; //Ignore rows not involving the current user
    }
    $toName = $hRow['to_name'];
    $fromName = $hRow['from_name'];
    $toUid = $hRow['to_uid'];
    $fromUid = $hRow['from_uid'];

    // Handle admin shenanigans
    $isAdminAction = $hRow['is_admin_action'];
    if ($isAdminAction == 1) {
        if ($toUid == $userSession) {
            if ($fromUid == $userSession) {
                // I changed myself as admin
                $fromName = "ADMIN (me)";
            } else {
                // An admin changed me
                $fromName = "ADMIN"; //mask the admin's name
            }
        } else {
            continue; //Ignore admin rows created by the user
        }
    }

    // Build the history entry
    $entry = [
        "timestamp" => "{$hRow['timestamp']}",
        "fromName" => "$fromName",
        "toName" => "$toName",
        "numPoints" => "{$hRow['num_points']}",
        "isAdminAction" => "$isAdminAction",
        "toEmail" => "{$hRow['to_email']}",
        "fromEmail" => "{$hRow['from_email']}"
    ];

    // Add the entry
    $entryArr[] = $entry;
}

// Return the JSON
header('Content-Type: application/json');
die(json_encode($entryArr));
?>