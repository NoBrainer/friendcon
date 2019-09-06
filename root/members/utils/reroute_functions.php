<?php

/**
 * Check the current URL. If it is not https, forward to https.
 *
 * @return boolean - whether or not to exit
 */
function forwardHttps() {
    if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
        header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
        return true;
    }
    return false;
}

/**
 * Go to /members/home.php if the user is logged in.
 *
 * @return boolean - whether or not to exit
 */
function forwardHomeIfLoggedIn() {
    $userSession = $_SESSION['userSession'];
    if (isset($userSession) && $userSession != "") {
        header("Location: /members/home.php", true);
        return true;
    }
    return false;
}

/**
 * Go to /members/index.php if the user is logged out.
 *
 * @return boolean - whether or not to exit
 */
function forwardIndexIfLoggedOut() {
    $userSession = $_SESSION['userSession'];
    if (!isset($userSession) || $userSession == "") {
        header("Location: /members/index.php", true);
        return true;
    }
    return false;
}

?>