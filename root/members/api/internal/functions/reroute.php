<?php
/**
 * Check the current URL. If it is not https, forward to https.
 *
 * @return boolean - whether or not to forward
 */
function forwardHttps() {
	if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
		header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 308);
		return true;
	}
	return false;
}

/**
 * Go to /members/home.php if the user is logged in.
 *
 * @return boolean - whether or not to forward
 */
function forwardHomeIfLoggedIn() {
	return forwardIfLoggedIn('/members/home.php');
}

/**
 * Go to a path if the user is logged in. Example path: "/members/home.php"
 *
 * @param string path - where to forward
 * @return boolean - whether or not to forward
 */
function forwardIfLoggedIn($path) {
	$userSession = $_SESSION['userSession'];
	if (isset($userSession) && $userSession != "") {
		header("Location: " . $path, true);
		return true;
	}
	return false;
}

/**
 * Go to /members/index.php if the user is logged out.
 *
 * @return boolean - whether or not to forward
 */
function forwardIndexIfLoggedOut() {
	return forwardIfLoggedOut('/members/index.php');
}

/**
 * Go to a path if the user is logged out. Example path: "/members/index.php"
 *
 * @param string path - where to forward
 * @return boolean - whether or not to exit
 */
function forwardIfLoggedOut($path) {
	$userSession = $_SESSION['userSession'];
	if (!isset($userSession) || $userSession == "") {
		header("Location: " . $path, true);
		return true;
	}
	return false;
}
