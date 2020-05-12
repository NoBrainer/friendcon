<?php
$pageTitle = "Unsubscribe";
$navTab = "QUIT";
$requireAdmin = false;
?>
<?php include('head.php'); ?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">[UNSUBSCRIBING UNDER CONSTRUCTION]</h5>
		</div>
	</div>
</div>

<!--  HTML Templates -->
<div class="templates" style="display:none"></div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";

	$(document).ready(() => {
		trackStats("LOAD/fun/listserv/quit");
	});
</script>
</body>
