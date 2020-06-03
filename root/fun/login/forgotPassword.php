<?php
$pageTitle = "Forgot Password";
?>
<?php include('head.php'); ?>
<body>

<!-- Content -->
<div class="container-fluid" id="content">
	<div class="container-sm card mb-3 mt-3 maxWidth-sm">
		<div class="card-body">
			<form id="forgotPasswordForm">
				<h5 class="card-title">Forgot Password</h5>
				<div class="form-group">
					<input class="form-control" type="email" id="email" placeholder="Email address" maxlength="254" required/>
				</div>
				<div class="form-group">
					<div id="captchaWrapper"></div>
				</div>
				<div class="form-group">
					<button class="btn btn-outline-primary" type="submit" id="sendResetEmail" disabled>
						<span class="fa fa-sign-in-alt"></span>
						<span>Send Reset Email</span>
					</button>
				</div>
				<div id="message"></div>
			</form>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		const $form = $('#forgotPasswordForm');
		const $email = $('#email');
		const $submitButton = $('#sendResetEmail');
		const $message = $('#message');

		trackStats("LOAD/fun/login/forgotPassword");
		render();

		function render() {
			renderCaptchaCheckbox();
			setupFormHandlers();
		}

		function renderCaptchaCheckbox() {
			renderCaptchaV2Checkbox(
				function onClick(e) {
					enableSubmitButton(true);
				},
				function onExpire(e) {
					enableSubmitButton(false);
					clearMessage($message);
				});
		}

		function setupFormHandlers() {
			$form.off().submit(handleSubmit);
			$email.off().keydown((e) => clearMessage($message));
			$email.focus();
		}

		function handleSubmit(e) {
			clearMessage($message);
			e.preventDefault();
			e.stopPropagation();

			// HTML5 form validation
			if ($form[0].checkValidity() === false) {
				return;
			}

			// Build request data
			const formData = new FormData();
			formData.append('email', $email.val().trim());
			$email.val("");

			// Make the server call
			infoMessage($message, "Sending token...");
			trackStats("SUBMIT/fun/login/forgotPassword");
			enableSubmitButton(false);
			$.ajax({
				type: 'POST',
				url: '/fun/api/admin/sendResetEmail.php',
				data: formData,
				async: false,
				cache: false,
				contentType: false,
				processData: false,
				success: (resp) => {
					// Display the success message then close the window in 3 seconds
					successMessage($message, resp.message + " Closing window in a few seconds...");
					setTimeout(() => {
						window.close();
					}, 3000);
				},
				error: (jqXHR) => {
					errorMessage($message, getErrorMessageFromResponse(jqXHR));
				},
				complete: () => {
					// Reset the CAPTCHA checkbox after each submit
					grecaptcha.reset();
				}
			});
		}

		function enableSubmitButton(enabled) {
			$submitButton.prop('disabled', !enabled);
		}
	});
</script>
</body>
</html>