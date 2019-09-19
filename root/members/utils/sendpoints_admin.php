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
include_once('sql_functions.php');

if (!$isAdmin) {
    die("You are not an admin! GTFO.");
}

// Get the submit data
$fromUid = $userSession;
$toUid = $MySQLi_CON->real_escape_string($_POST['target_uid']);
$sendNumPoints = $MySQLi_CON->real_escape_string($_POST['num_points']);

if (!isset($toUid) || !isset($sendNumPoints) || !isset($fromUid)) {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}

// Add an entry in history
$historyQuery = "INSERT INTO points_history(from_uid, to_uid, num_points, is_admin_action) VALUES (?, ?, ?, ?)";
$historyResult = prepareSqlForResult($MySQLi_CON, $historyQuery, 'iiii', $fromUid, $toUid, $sendNumPoints, $isAdmin);

if ($toUid == -1) {
    // Send the points to everyone (without affecting the admin's points)
    $sendQuery = "UPDATE users u SET u.upoints = u.upoints + {$sendNumPoints} WHERE u.isPresent = 1";
    $sendResult = prepareSqlForResult($MySQLi_CON, $sendQuery, 'i', $sendNumPoints);
} else {
    // Send the points (without affecting the admin's points)
    $sendQuery = "UPDATE users u SET u.upoints = u.upoints + ? WHERE u.uid = ?";
    $sendResult = prepareSqlForResult($MySQLi_CON, $sendQuery, 'ii', $sendNumPoints, $toUid);
}
if ($sendResult) {
    die("SUCCESS");
} else {
    die("Error sending points [DB-2]");
}
?>