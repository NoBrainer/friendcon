<?php
$pageTitle = "Listserv";
$navTab = "SHOW";
$requireAdmin = true;
?>
<?php include('head.php'); ?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<button type="button" class="btn btn-outline-primary btn-sm float-right mb-1" id="copyToClipboard">
				<span class="fa fa-clipboard"></span>
				<span>Copy</span>
			</button>
			<h5 class="card-title">FriendCon Listserv</h5>
			<label for="emailList" class="sr-only">Email List</label>
			<textarea class="form-control" rows="7" id="emailList" readonly></textarea>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";

	$(document).ready(() => {
		const $copyToClipboard = $('#copyToClipboard');
		const $emailList = $('#emailList');

		trackStats("LOAD/fun/listserv/show");
		renderEmailList();
		setupHandlers();

		function renderEmailList() {
			$.ajax({
				type: 'GET',
				url: '/fun/api/listserv/get.php',
				success: (resp) => {
					$emailList.text(resp.data);
				},
				error: (jqXHR) => {
					$emailList.text("Error loading listserv.");
					console.log(jqXHR);
				}
			});
		}

		function setupHandlers() {
			// Select all text on focus
			$emailList.off('focus').focus((e) => {
				$emailList.select();
			});

			// Copy to clipboard button
			$copyToClipboard.off('click').click((e) => {
				$emailList.select();
				document.execCommand('copy');
			});
		}
	});
</script>
</body>
