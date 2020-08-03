<?php
$pageTitle = "Reset Password";
$requireAdmin = false;
$forwardAdmin = true;
include('head.php');

use fun\classes\util\Param as Param;

$email = isset($_GET['email']) ? Param::asString($_GET['email']) : null;
$token = isset($_GET['token']) ? Param::asString($_GET['token']) : null;

// Use this awkward way of forwarding since we're in the middle of the HTML
if (Param::isBlankString($email) || Param::isBlankString($token)) {
	echo '<script>window.location = "/fun/admin/forgotPassword";</script>';
}
?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div class="container-fluid" id="content">
	<div class="container-sm card mb-3 mt-3 maxWidth-sm">
		<div class="card-body">
			<form id="resetPasswordForm">
				<h5 class="card-title">Reset Password</h5>
				<div class="form-group">
					<input class="form-control" type="email" id="email" placeholder="Email" value="<?php echo $email; ?>" maxlength="254" required disabled/>
				</div>
				<div class="form-group">
					<input class="form-control" type="text" id="token" placeholder="Token from email" value="<?php echo $token; ?>" required disabled/>
				</div>
				<div class="form-group">
					<input class="form-control" type="password" id="newPassword" placeholder="New password" required/>
				</div>
				<div class="form-group">
					<input class="form-control" type="password" id="newPasswordDupe" placeholder="New password (again)" required/>
				</div>
				<div class="form-group">
					<div id="captchaWrapper"></div>
				</div>
				<div class="form-group">
					<button class="btn btn-outline-primary" type="submit" id="resetPassword" disabled>
						<span class="fa fa-sign-in-alt"></span>
						<span>Reset Password</span>
					</button>
				</div>
				<div class="form-group row">
					<a class="col text-right" href="/fun/admin/forgotPassword">
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

		trackStats("LOAD/fun/admin/resetPassword");
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
			trackStats("SUBMIT/fun/admin/resetPassword");
			enableSubmitButton(false);
			$.ajax({
				type: 'POST',
				url: '/fun/api/admin/access/resetPassword.php',
				data: formData,
				async: false,
				cache: false,
				contentType: false,
				processData: false,
				success: (resp) => {
					successMessage($message, resp.message);
					setTimeout(() => $('#navLoginModal').modal('show'), 1000);
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