<?php
$pageTitle = "Reset Password";
?>
<?php include('head.php'); ?>
<?php
// Require an email and token
$email = $_GET['email'];
$token = $_GET['token'];
if (!isset($email) || !is_string($email) || empty($email) || !isset($token) || !is_string($token) || empty($token)) {
	header(CONTENT['TEXT']);
	http_response_code(HTTP['BAD_REQUEST']);
	echo "BAD_REQUEST";
	return;
}
?>
<body>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-sm card mb-3 mt-3 maxWidth-sm">
		<div class="card-body">
			<form id="resetPasswordForm">
				<h5 class="card-title">Reset Password</h5>
				<div class="form-group">
					<input type="email" class="form-control" placeholder="Email" id="email" value="<?php echo $email; ?>" required disabled/>
				</div>
				<div class="form-group">
					<input type="text" class="form-control" placeholder="Token from email" id="token" value="<?php echo $token; ?>" required disabled/>
				</div>
				<div class="form-group">
					<input type="password" class="form-control" placeholder="New password" id="newPassword" required/>
				</div>
				<div class="form-group">
					<input type="password" class="form-control" placeholder="New password (again)" id="newPasswordDupe" required/>
				</div>
				<div class="form-group">
					<div id="captchaWrapper"></div>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-outline-primary" id="resetPassword" disabled>
						<span class="fa fa-sign-in-alt"></span>
						<span>Reset Password</span>
					</button>
				</div>
				<div class="form-group row">
					<a class="col" href="/members/login/forgotPassword" style="text-align:right">
						<span>Need another password reset email?</span>
					</a>
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
		const $form = $('#resetPasswordForm');
		const $email = $('#email');
		const $token = $('#token');
		const $password = $('#newPassword');
		const $passwordDupe = $('#newPasswordDupe');
		const $submitButton = $('#resetPassword');
		const $message = $('#message');

		trackStats("LOAD/members/login/resetPassword");
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
			$password.off().keydown((e) => clearMessage($message));
			$passwordDupe.off().keydown((e) => clearMessage($message));
			$form.off().submit(handleSubmit);
			$password.focus();
		}

		function handleSubmit(e) {
			clearMessage($message);
			e.preventDefault();
			e.stopPropagation();

			// HTML5 form validation
			if ($form[0].checkValidity() === false) {
				return;
			}

			// Logic validation
			const pass1 = $password.val().trim();
			const pass2 = $passwordDupe.val().trim();
			if (pass1 !== pass2) {
				errorMessage($message, "Passwords do not match");
				return;
			}

			// Build request data
			const formData = new FormData();
			formData.append('token', $token.val().trim());
			formData.append('email', $email.val().trim());
			formData.append('password', $password.val().trim());

			// Make the server call
			trackStats("SUBMIT/members/login/resetPassword");
			$.ajax({
				type: 'POST',
				url: '/members/api/password/reset.php',
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
					const resp = jqXHR.responseJSON;
					errorMessage($message, resp.error);
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