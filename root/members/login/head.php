<?php
session_start();
$userSession = $_SESSION['userSession'];
$isLoggedIn = isset($userSession) && $userSession !== "";

include($_SERVER['DOCUMENT_ROOT'] . '/members/api/internal/constants.php');
include($_SERVER['DOCUMENT_ROOT'] . '/members/api/internal/functions.php');
include($_SERVER['DOCUMENT_ROOT'] . '/members/api/internal/initDB.php');
include($_SERVER['DOCUMENT_ROOT'] . '/members/api/internal/initCaptcha.php');

// Remove sensitive info from memory
unset($CAPTCHA_SECRET_V2_KEY);
unset($CAPTCHA_SECRET_V3_KEY);

// Short-circuit forwarding
if (forwardHttps()) {
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo $pageTitle; ?></title>
	<link rel="stylesheet" media="screen" href="/members/login/login.css">
	<link rel="icon" href="/members/images/favicon.png">

	<!-- JavaScript required before rendering -->
	<script type="text/javascript" src="/members/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="/members/lib/popper/popper.min.js"></script>
	<script type="text/javascript" src="/members/lib/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/members/lib/underscore/underscore.min.js"></script>
	<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?render=<?php echo CAPTCHA_SITE_V3_KEY; ?>"></script>
	<script type="text/javascript" src="/members/js/utils.js"></script>
</head>
