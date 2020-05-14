<?php

use util\Http as Http;

$pageTitle = "Reset Password";

// Variables used in rendering
$email = $_GET['email'];
$token = $_GET['token'];

// Require an email and token
if (!isset($email) || !is_string($email) || empty($email) || !isset($token) || !is_string($token) || empty($token)) {
	Http::contentType('TEXT');
	Http::responseCode('BAD_REQUEST');
	echo "BAD_REQUEST";
	return;
}
?>
<?php include('head.php'); ?>
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
					<a class="col" href="/fun/login/forgotPassword" style="text-align:right">
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
	$(document).ready(() => {
		const $form = $('#resetPasswordForm');
		const $email = $('#email');
		const $token = $('#token');
		const $password = $('#newPassword');
		const $passwordDupe = $('#newPasswordDupe');
		const $submitButton = $('#resetPassword');
		const $message = $('#message');

		trackStats("LOAD/fun/login/resetPassword");
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
			infoMessage($message, "Resetting...");
			trackStats("SUBMIT/fun/login/resetPassword");
			enableSubmitButton(false);
			$.ajax({
				type: 'POST',
				url: '/fun/api/admin/resetPassword.php',
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