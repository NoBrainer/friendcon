<?php
session_start();
$userSession = $_SESSION['userSession'];

// Build the forwarding location
if (isset($userSession) && $userSession != "") {
	// Members page, logged out
	$location = "/members/index.php";
} else {
	// WordPress homepage
	$location = "/index.php";
}

session_destroy();
unset($userSession);
header("Location: $location");
