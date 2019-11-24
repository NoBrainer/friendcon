<?php
// Requirements:
// 1. $userSession must be defined before this is included/required
// 2. We must be connected to the database
// Usage:
// 1. Sets $conDay, $conMonth, $conYear, $isRegistrationEnabled, $isPointsEnabled,
//    $premiumLastMonth, $premiumLastDay, $premiumDueDateDisplay, $stillSellingPremium,
//    $teeLastMonth, $teeLastDay, $teeDueDateDisplay, $stillReservingTees, $teePrice

// Check if app state
$query = "SELECT * FROM app_state ORDER BY conYear DESC LIMIT 1";
$result = $mysqli->query($query);
if (!$result) {
    $conDay = 26;
    $conMonth = 5;
    $conYear = 2018;
    $isRegistrationEnabled = 0;
    $isPointsEnabled = 0;
    $badgePrice = '40.00';
    $premiumLastMonth = 5;
    $premiumLastDay = 12;
    $premiumDueDateDisplay = "May 12th (EST)";
    $teeLastMonth = 5;
    $teeLastDay = 12;
    $teeDueDateDisplay = "May 12th (EST)";
    $teePrice = 15;
} else {
    $row = $result->fetch_array();
    $result->free_result();
    $conDay = $row['conDay'];
    $conMonth = $row['conMonth'];
    $conYear = $row['conYear'];
    $isRegistrationEnabled = $row['registrationEnabled'];
    $isPointsEnabled = $row['pointsEnabled'];
    $badgePrice = "{$row['badgePrice']}";
    $premiumLastMonth = $row['premiumLastMonth'];
    $premiumLastDay = $row['premiumLastDay'];
    $premiumDueDateDisplay = $row['premiumDueDateDisplay'];
    $teeLastMonth = $row['teeLastMonth'];
    $teeLastDay = $row['teeLastDay'];
    $teeDueDateDisplay = $row['teeDueDateDisplay'];
    $teePrice = $row['teePrice'];
}

$now = new DateTime("now");

// Figure out if premium badges are still on sale
$lastDayForPremium = new DateTime("now");
$lastDayForPremium->setDate($conYear, $premiumLastMonth, $premiumLastDay + 1);
$stillSellingPremium = $now < $lastDayForPremium ? 1 : 0;

// Figure out if tees can still be reserved
$lastDayForTees = new DateTime("now");
$lastDayForTees->setDate($conYear, $teeLastMonth, $teeLastDay + 1);
$stillReservingTees = $now < $lastDayForTees ? 1 : 0;

// Testing overrides
//$isVince = $userSession == 43;
//$isRegistrationEnabled = $isVince;
