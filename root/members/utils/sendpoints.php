<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to main homepage
    header("Location: /");
    exit;
}
include('dbconnect.php');

// Get the submit data
$fromUid = $userSession;
$toUid = $MySQLi_CON->real_escape_string($_POST['to_uid']);
$sendNumPoints = $MySQLi_CON->real_escape_string($_POST['num_points']);

if (!isset($toUid) || !isset($sendNumPoints) || !isset($fromUid)) {
    // Short-circuit if not given the proper data
    header("Location: /");
    exit;
}

// Check the 'from' points
$result = $MySQLi_CON->query("SELECT u.upoints FROM users u WHERE u.uid={$userSession}");
if (!$result) {
    die("Sending points failed [DB-1]");
}
$checkPoints = $result->fetch_array();
$result->free_result();
$updatedPoints = $checkPoints['upoints'];

// Input validation
if ($sendNumPoints > $updatedPoints) {
    die("Insufficient Points");
} else if ($fromUid == $toUid) {
    die("Cannot send points to yourself. Nice try, asshole.");
}

// Add an entry in history
$historyQuery = "INSERT INTO points_history(from_uid, to_uid, num_points)
	 VALUES ({$fromUid}, {$toUid}, {$sendNumPoints})";
$MySQLi_CON->query($historyQuery);

// Send the points
$sendQuery = "UPDATE users from_u, users to_u
	 SET from_u.upoints = from_u.upoints - {$sendNumPoints}, to_u.upoints = to_u.upoints + {$sendNumPoints}
	 WHERE from_u.uid = {$fromUid} AND to_u.uid = {$toUid}";
if ($MySQLi_CON->query($sendQuery)) {
    die("SUCCESS");
} else {
    die("Error sending points [DB-2]");
}
?>