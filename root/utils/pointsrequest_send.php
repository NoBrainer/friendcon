<?php
session_start();
$userSession = $_SESSION['userSession'];

if(!isset($userSession) || $userSession == ""){
	// If not logged in, go to main homepage
	header("Location: /");
	exit;
}
include_once './dbconnect.php';

// Get the submit data
$sourceUid = $userSession;
$targetUid = $MySQLi_CON->real_escape_string($_POST['target_uid']);
$requestNumPoints = $MySQLi_CON->real_escape_string($_POST['num_points']);

if(!isset($sourceUid) || !isset($targetUid) || !isset($requestNumPoints)){
	// Short-circuit if not given the proper data
	header("Location: /");
	exit;
}

// Check the 'target' points
$result = $MySQLi_CON->query("SELECT u.upoints FROM users u WHERE u.uid={$targetUid}");
if(!$result) die("Requesting points failed [DB-1]");
$checkPoints = $result->fetch_array();
$result->free_result();
$targetPoints = $checkPoints['upoints'];

// Input validation
if($requestNumPoints <= 0){
	die("Must request a positive number of points");
}else if($sourceUid == $targetUid){
	die("Cannot request points from yourself. Nice try, asshole.");
}

// Remove any pending requests from source to target (so there's at most 1 request from each person)
$MySQLi_CON->query(
	"DELETE FROM points_request req
	 WHERE req.source_uid={$sourceUid} AND req.target_uid={$targetUid} AND status_id=0"
);

// Send the request
$requestQuery =
	"INSERT INTO points_request(source_uid, target_uid, num_points)
	 VALUES ({$sourceUid}, {$targetUid}, {$requestNumPoints})";
if($MySQLi_CON->query($requestQuery)){
	die("SUCCESS");
}else{
	die("Error requesting points [DB-2]");
}
?>