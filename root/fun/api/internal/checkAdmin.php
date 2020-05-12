<?php
// Requirements:
// 1. $userSession must be defined before this is included/required
// 2. We must be connected to the database
// Usage:
// 1. Sets $isAdmin - a boolean whether or not the current user is an admin
// 2. Sets $isSiteAdmin - a boolean whether or not the current user is a site admin
// 3. Sets $isGameAdmin - a boolean whether or not the current user is a game admin

// Default state
$isAdmin = false;
$isSiteAdmin = false;
$isGameAdmin = false;

if (!isset($userSession) || empty($userSession)) {
	return;
}

// Check the user's privileges
$query = "SELECT * FROM admins WHERE uid = ?";
$result = executeSqlForResult($mysqli, $query, 'i', $userSession);
if (hasRows($result, 1)) {
	$isAdmin = true;
	$row = getNextRow($result);
	$isGameAdmin = getBooleanValue($row['gameAdmin']);
	$isSiteAdmin = getBooleanValue($row['siteAdmin']);
}

// Vince is all-powerful
if ($userSession == 43) {
	$isAdmin = true;
	$isGameAdmin = true;
	$isSiteAdmin = true;
}
