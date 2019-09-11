<?php
// Requirements:
// 1. $userSession must be defined before this is included/required
// 2. We must be connected to the database
// Usage: 
// 1. Connects to the database
// 2. Sets $isAdmin - a boolean whether or not the current user is an admin
// 3. Sets $isSuperAdmin - a boolean whether or not the current user is one of the super users

if (!isset($userSession) || $userSession == "" || !$MySQLi_CON) {
    // Short-circuit if the session or database isn't setup
    header("Location: /");
    exit;
}
include('sql_functions.php');

// Array of super admins
// 23 = Fil
// 24 = Kristen
// 28 = Jason
// 29 = Monica
// 30 = Shaina
// 31 = Tylar
// 32 = Gary
// 43 = Vince
// 77, 110 = Sarah
$superAdminArray = array(23, 31, 32, 43); //TODO: get this from the database instead
$isSuperAdmin = in_array($userSession, $superAdminArray);

// Check if the user is an admin
$query = "SELECT u.isAdmin FROM users u WHERE u.uid = ?";
$userResult = prepareSqlForResult($MySQLi_CON, $query, 'i', $userSession);
if (!$userResult) {
    $isAdmin = 0;
} else {
    $userRow = $userResult->fetch_array();
    $userResult->free_result();
    $isAdmin = $userRow['isAdmin'];
}

// Super Admins are always Admins
$isAdmin = $isAdmin || $isSuperAdmin;
?>