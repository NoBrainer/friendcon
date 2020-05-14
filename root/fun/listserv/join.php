<?php
$pageTitle = "Subscribe";
$navTab = "JOIN";
$requireAdmin = false;
?>
<?php include('head.php'); ?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Subscribe to FriendCon Emails</h5>
			<form id="subscribeForm">
				<div class="form-group" id="captchaWrapper"></div>
				<div class="input-group mb-3">
					<input type="email" class="form-control" id="email" placeholder="Email address" aria-label="Email address" required>
					<span class="input-group-append">
						<button type="submit" class="btn btn-outline-primary" id="submitButton" disabled>Subscribe</button>
					</span>
				</div>
				<div class="form-group">
					<div id="subscribeMessage"></div>
				</div>
			</form>
		</div>
	</div>
</div>

<!--  HTML Templates -->
<div class="templates" style="display:none"></div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		const $subscribeForm = $('#subscribeForm');
		const $email = $('#email');
		const $submitButton = $('#submitButton');
		const $subscribeMessage = $('#subscribeMessage');

		trackStats("LOAD/fun/listserv/join");
		renderCaptchaCheckbox();
		setupHandlers();

		function renderCaptchaCheckbox() {
			renderCaptchaV2Checkbox(
				function onClick(e) {
					enableSubmitButton(true);
				},
				function onExpire(e) {
					enableSubmitButton(false);
					clearMessage($subscribeMessage);
				});
		}

		function setupHandlers() {
			$subscribeForm.off('submit').submit((e) => {
				clearMessage($subscribeMessage);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($subscribeForm[0].checkValidity() === false) {
					return;
				}

				const email = $email.val().trim();

				// Prevent certain characters in the email address
				if (email.match(/[\s,<>()]/) || email !== _.escape(email)) {
					errorMessage($subscribeMessage, "Invalid email address.");
					return;
				}

				// Build request data
				const formData = new FormData();
				formData.append('email', email);

				// Make the change
				infoMessage($subscribeMessage, "Subscribing...");
				trackStats("SUBSCRIBE/fun/listserv/join");
				enableSubmitButton(false);
				$.ajax({
					type: 'POST',
					url: "/fun/api/listserv/join.php",
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($subscribeMessage, resp.message);
					},
					error: (jqXHR) => {
						errorMessage($subscribeMessage, getErrorMessageFromResponse(jqXHR));
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
