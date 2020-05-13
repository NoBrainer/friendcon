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
			<h5 class="card-title">Unsubscribe from FriendCon Emails</h5>
			<form id="unsubscribeForm">
				<div class="input-group mb-3">
					<input type="email" class="form-control" id="email" placeholder="Email address" aria-label="Email address" required>
					<span class="input-group-append">
						<button type="submit" class="btn btn-outline-primary">Unsubscribe</button>
					</span>
				</div>
				<div class="form-group">
					<div id="unsubscribeMessage"></div>
				</div>
			</form>
		</div>
	</div>
</div>

<!--  HTML Templates -->
<div class="templates" style="display:none"></div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";

	$(document).ready(() => {
		const $unsubscribeForm = $('#unsubscribeForm');
		const $email = $('#email');
		const $unsubscribeMessage = $('#unsubscribeMessage');

		trackStats("LOAD/fun/listserv/quit");
		setupHandlers();

		function setupHandlers() {
			$unsubscribeForm.off('submit').submit((e) => {
				clearMessage($unsubscribeMessage);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($unsubscribeForm[0].checkValidity() === false) {
					return;
				}

				const email = $email.val().trim();

				// Prevent certain characters in the email address
				if (email.match(/[\s,<>()]/) || email !== _.escape(email)) {
					errorMessage($unsubscribeMessage, "Invalid email address.");
					return;
				}

				// Build request data
				const formData = new FormData();
				formData.append('email', email);

				// Make the change
				infoMessage($unsubscribeMessage, "Unsubscribing...");
				trackStats("UNSUBSCRIBE/fun/listserv/quit");
				$.ajax({
					type: 'POST',
					url: "/fun/api/listserv/quit.php",
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($unsubscribeMessage, resp.message);
					},
					error: (jqXHR) => {
						errorMessage($unsubscribeMessage, getErrorMessageFromResponse(jqXHR));
					}
				});
			});
		}
	});
</script>
</body>
