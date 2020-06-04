<?php
include($_SERVER['DOCUMENT_ROOT'] . '/fun/autoloader.php');

use util\Captcha as Captcha;
use util\Http as Http;
use util\Session as Session;

Captcha::initialize();

// Variables used in rendering
$name = Session::$name;
$isLoggedIn = Session::$isLoggedIn;
$isGameAdmin = Session::$isGameAdmin;

// Short-circuit forwarding
if ($requireAdmin && !$isGameAdmin) {
	Http::forward("/fun/game", true);
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo $pageTitle; ?></title>
	<link rel="stylesheet" media="screen" href="/fun/game/game.css">
	<link rel="icon" href="/fun/static/images/favicon.png">

	<!-- JavaScript required before rendering -->
	<script type="text/javascript" src="/fun/static/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="/fun/static/lib/moment/moment.min.js"></script>
	<script type="text/javascript" src="/fun/static/lib/popper/popper.min.js"></script>
	<script type="text/javascript" src="/fun/static/lib/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/fun/static/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
	<script type="text/javascript" src="/fun/static/lib/underscore/underscore.min.js"></script>
	<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?render=<?php echo CAPTCHA_SITE_V3_KEY; ?>"></script>
	<script type="text/javascript" src="/fun/js/utils.js"></script>
	<script type="text/javascript" src="/fun/game/game.js"></script>
	<script type="text/javascript">
		const captchaSiteV2Key = "<?php echo CAPTCHA_SITE_V2_KEY; ?>";
		const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";
	</script>
</head>
