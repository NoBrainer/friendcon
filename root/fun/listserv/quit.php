<?php
$pageTitle = "Unsubscribe";
$navTab = "QUIT";
$requireAdmin = false;
include('head.php');
?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div class="container-fluid" id="content">
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Unsubscribe from FriendCon Emails</h5>
			<form id="unsubscribeForm">
				<div class="form-group" id="captchaWrapper"></div>
				<div class="input-group mb-3">
					<input class="form-control" type="email" id="email" placeholder="Email address" aria-label="Email address" maxlength="254" required>
					<span class="input-group-append">
						<button class="btn btn-outline-primary" type="submit" id="submitButton" disabled>Unsubscribe</button>
					</span>
				</div>
				<div class="form-group">
					<div id="unsubscribeMessage"></div>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		const $unsubscribeForm = $('#unsubscribeForm');
		const $email = $('#email');
		const $submitButton = $('#submitButton');
		const $unsubscribeMessage = $('#unsubscribeMessage');

		trackStats("LOAD/fun/listserv/quit");
		renderCaptchaCheckbox();
		setupHandlers();

		function renderCaptchaCheckbox() {
			renderCaptchaV2Checkbox(
				function onClick(e) {
					enableSubmitButton(true);
				},
				function onExpire(e) {
					enableSubmitButton(false);
					clearMessage($unsubscribeMessage);
				});
		}

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
				enableSubmitButton(false);
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
					},
					complete: () => {
						// Reset the CAPTCHA checkbox after each submit
						grecaptcha.reset();
					}
				});
			});
		}

		function enableSubmitButton(enabled) {
			$submitButton.prop('disabled', !enabled);
		}
	});
</script>
</body>
