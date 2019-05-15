<?php
include('dbconnect.php');

// Check the house points
$result = $MySQLi_CON->query("SELECT	sum(IF(u.houseid = 0, u.upoints, 0)) AS unsorted_points,
			sum(IF(u.houseid = 1, u.upoints, 0)) AS baratheon_points,
			sum(IF(u.houseid = 2, u.upoints, 0)) AS lannister_points,
			sum(IF(u.houseid = 3, u.upoints, 0)) AS martel_points,
			sum(IF(u.houseid = 4, u.upoints, 0)) AS stark_points,
			sum(IF(u.houseid = 5, u.upoints, 0)) AS maesters_points,
			sum(IF(u.houseid = 6, u.upoints, 0)) AS the_faith_points
	 FROM	users u
	 WHERE	u.isRegistered = 1 OR u.isRegistered = -1" //houses are -1
);
if (!$result) {
    die("Error getting points [DB-1]");
}
$row = $result->fetch_array();
$result->free_result();

$unsorted_points = $row['unsorted_points'];
$stark_points = $row['stark_points'];
$lannister_points = $row['lannister_points'];
$martel_points = $row['martel_points'];
$baratheon_points = $row['baratheon_points'];
$maesters_points = $row['maesters_points'];
$the_faith_points = $row['the_faith_points'];

// Build the json
$attrArray = [];
$attrArray[] = "\"unsorted\":{$unsorted_points}";
$attrArray[] = "\"stark\":{$stark_points}";
$attrArray[] = "\"lannister\":{$lannister_points}";
$attrArray[] = "\"martel\":{$martel_points}";
$attrArray[] = "\"baratheon\":{$baratheon_points}";
$attrArray[] = "\"maesters\":{$maesters_points}";
$attrArray[] = "\"the_faith\":{$the_faith_points}";
$json = "{" . join(",", $attrArray) . "}";

// Return the json
header('Content-Type: application/json');
die("{$json}");
?>