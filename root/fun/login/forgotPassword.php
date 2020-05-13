<?php
$pageTitle = "Forgot Password";
?>
<?php include('head.php'); ?>
<body>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-sm card mb-3 mt-3 maxWidth-sm">
		<div class="card-body">
			<form id="forgotPasswordForm">
				<h5 class="card-title">Forgot Password</h5>
				<div class="form-group">
					<input type="email" class="form-control" placeholder="Email address" id="email" required/>
				</div>
				<div class="form-group">
					<div id="captchaWrapper"></div>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-outline-primary" id="sendResetEmail" disabled>
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
	const captchaSiteV2Key = "<?php echo CAPTCHA_SITE_V2_KEY; ?>";
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";

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
			grecaptcha.ready(() => { //Ensure that reCAPTCHA is ready
				grecaptcha.render('captchaWrapper', {
					sitekey: captchaSiteV2Key,
					callback: (e) => {
						// Enable the submit button after user checks the box
						enableSubmitButton(true);
					},
					'expired-callback': (e) => {
						// Disable the submit button once the CAPTCHA expires
						enableSubmitButton(false);
						clearMessage($message);
					}
				});
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
			trackStats("SUBMIT/fun/login/forgotPassword");
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