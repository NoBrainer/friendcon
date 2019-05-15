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
include('check_app_state.php');

// Reject non-admins
if (!$isAdmin) {
    die("Admins only. GTFO.");
}

// Initialize counts
$numSmall = 0;
$numSmallPaid = 0;
$numMedium = 0;
$numMediumPaid = 0;
$numLarge = 0;
$numLargePaid = 0;
$numXL = 0;
$numXLPaid = 0;
$num2XL = 0;
$num2XLPaid = 0;
$num3XL = 0;
$num3XLPaid = 0;
$num4XL = 0;
$num4XLPaid = 0;
$num5XL = 0;
$num5XLPaid = 0;

// Run the query and traverse each row to build up the counts
$result = $MySQLi_CON->query("SELECT s.teeSize, u.isRegistered
	 FROM registration_stats s
	 JOIN users u ON u.uid = s.uid
	 WHERE s.conYear = {$conYear} AND s.reserveTee = 1");
$row = $result->fetch_array();
while ($row) {
    $size = $row['teeSize'];
    $isRegistered = $row['isRegistered'];
    if ($size == 'S') {
        $numSmall++;
        $numSmallPaid += ($isRegistered ? 1 : 0);
    } else if ($size == 'M') {
        $numMedium++;
        $numMediumPaid += ($isRegistered ? 1 : 0);
    } else if ($size == 'L') {
        $numLarge++;
        $numLargePaid += ($isRegistered ? 1 : 0);
    } else if ($size == 'XL') {
        $numXL++;
        $numXLPaid += ($isRegistered ? 1 : 0);
    } else if ($size == '2XL') {
        $num2XL++;
        $num2XLPaid += ($isRegistered ? 1 : 0);
    } else if ($size == '3XL') {
        $num3XL++;
        $num3XLPaid += ($isRegistered ? 1 : 0);
    } else if ($size == '4XL') {
        $num4XL++;
        $num4XLPaid += ($isRegistered ? 1 : 0);
    } else if ($size == '5XL') {
        $num5XL++;
        $num5XLPaid += ($isRegistered ? 1 : 0);
    }
    $row = $result->fetch_array();
}
$result->free_result();

// Build JSON with the counts
$attrArray = [];
$attrArray[] = "\"S\":$numSmall";
$attrArray[] = "\"M\":$numMedium";
$attrArray[] = "\"L\":$numLarge";
$attrArray[] = "\"XL\":$numXL";
$attrArray[] = "\"2XL\":$num2XL";
$attrArray[] = "\"3XL\":$num3XL";
$attrArray[] = "\"4XL\":$num4XL";
$attrArray[] = "\"5XL\":$num5XL";
$attrArray[] = "\"SPaid\":$numSmallPaid";
$attrArray[] = "\"MPaid\":$numMediumPaid";
$attrArray[] = "\"LPaid\":$numLargePaid";
$attrArray[] = "\"XLPaid\":$numXLPaid";
$attrArray[] = "\"2XLPaid\":$num2XLPaid";
$attrArray[] = "\"3XLPaid\":$num3XLPaid";
$attrArray[] = "\"4XLPaid\":$num4XLPaid";
$attrArray[] = "\"5XLPaid\":$num5XLPaid";
$json = "{" . join(",", $attrArray) . "}";

// Return the JSON string
header('Content-Type: application/json');
die($json);
?>