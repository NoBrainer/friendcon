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
			<button type="button" class="btn btn-outline-primary btn-sm float-right mb-1" id="copyEmails">
				<span class="fa fa-clipboard"></span>
				<span>Copy</span>
			</button>
			<h5 class="card-title">FriendCon Listserv</h5>
			<label for="emailList" class="sr-only">Email List</label>
			<textarea class="form-control form-control-sm" rows="5" id="emailList" readonly></textarea>
		</div>
	</div>

	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<button type="button" class="btn btn-outline-primary btn-sm float-right mb-1" id="copyFooter">
				<span class="fa fa-clipboard"></span>
				<span>Copy</span>
			</button>
			<h5 class="card-title">Email Footer Template</h5>
			<label for="footerTemplate" class="sr-only">Email Footer Template</label>
			<textarea class="form-control form-control-sm" rows="3" id="footerTemplate" readonly></textarea>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";

	$(document).ready(() => {
		const $copyEmails = $('#copyEmails');
		const $emailList = $('#emailList');
		const $copyFooter = $('#copyFooter');
		const $footerTemplate = $('#footerTemplate');

		trackStats("LOAD/fun/listserv/show");
		render();

		function render() {
			renderEmailList();
			renderUnsubscribeTemplate();
			setupHandlers();
		}

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

		function renderUnsubscribeTemplate() {
			$footerTemplate.html("Don't want these emails? Unsubscribe: https://friendcon.com/unsubscribe");
		}

		function setupHandlers() {
			// Select all text on focus for textareas
			$('textarea').off('focus').focus((e) => {
				$(e.currentTarget).select();
			});

			// Copy emails to clipboard button
			$copyEmails.off('click').click((e) => {
				$emailList.select();
				document.execCommand('copy');
			});

			// Copy footer to clipboard button
			$copyFooter.off('click').click((e) => {
				$footerTemplate.select();
				document.execCommand('copy');
			});
		}
	});
</script>
</body>
