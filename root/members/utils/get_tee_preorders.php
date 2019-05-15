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
$str = "{";
$str = "{$str}\"S\":{$numSmall},";
$str = "{$str}\"M\":{$numMedium},";
$str = "{$str}\"L\":{$numLarge},";
$str = "{$str}\"XL\":{$numXL},";
$str = "{$str}\"2XL\":{$num2XL},";
$str = "{$str}\"3XL\":{$num3XL},";
$str = "{$str}\"4XL\":{$num4XL},";
$str = "{$str}\"5XL\":{$num5XL},";
$str = "{$str}\"SPaid\":{$numSmallPaid},";
$str = "{$str}\"MPaid\":{$numMediumPaid},";
$str = "{$str}\"LPaid\":{$numLargePaid},";
$str = "{$str}\"XLPaid\":{$numXLPaid},";
$str = "{$str}\"2XLPaid\":{$num2XLPaid},";
$str = "{$str}\"3XLPaid\":{$num3XLPaid},";
$str = "{$str}\"4XLPaid\":{$num4XLPaid},";
$str = "{$str}\"5XLPaid\":{$num5XLPaid}";
$str = "{$str}}";

// Return the JSON string
header('Content-Type: application/json');
die($str);
?>