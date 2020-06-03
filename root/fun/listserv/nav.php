<!-- Navbar -->
<nav class="navbar navbar-expand-sm navbar-light bg-light">
	<!-- Branding -->
	<a class="navbar-brand">FriendCon Listserv</a>

	<!-- Navbar toggler when collapsed -->
	<button class="navbar-toggler" type="button" data-target="#listservNav" data-toggle="collapse" aria-controls="listservNav" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<!-- Navbar contents -->
	<div class="collapse navbar-collapse" id="listservNav">
		<div class="navbar-nav mr-auto">
			<a class="nav-item nav-link<?php echo($navTab === "JOIN" ? ' active' : '') ?>" href="/fun/listserv/join">
				<span>Join</span>
				<?php if ($navTab === "JOIN") { ?>
					<span class="sr-only">(current)</span>
				<?php } ?>
			</a>
			<a class="nav-item nav-link<?php echo($navTab === "QUIT" ? ' active' : '') ?>" href="/fun/listserv/quit">
				<span>Quit</span>
				<?php if ($navTab === "QUIT") { ?>
					<span class="sr-only">(current)</span>
				<?php } ?>
			</a>
			<?php if ($isAdmin) { ?>
				<a class="nav-item nav-link<?php echo($navTab === "SHOW" ? ' active' : '') ?>" href="/fun/listserv/show">
					<span>Show</span>
					<?php if ($navTab === "SHOW") { ?>
						<span class="sr-only">(current)</span>
					<?php } ?>
				</a>
			<?php } ?>
		</div>
		<div class="navbar-nav">
			<?php if ($isLoggedIn) { ?>
				<div class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" role="button" id="userDropdown" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
						<span class="fa fa-user-lock"></span>
						<span>User</span>
					</a>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
						<a class="dropdown-item" href="/fun/game">
							<span class="fa fa-gamepad"></span>
							<span>Game</span>
						</a>
						<a class="dropdown-item" href="/fun/admin">
							<span class="fa fa-lock"></span>
							<span>Site Admin</span>
						</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" id="navLogoutBtn" href="#">
							<span class="fa fa-sign-out-alt"></span>
							<span>Log Out</span>
						</a>
					</div>
				</div>
			<?php } else { ?>
				<a class="nav-item" data-target="#navLoginModal" data-toggle="modal">
					<span class="fa fa-user-lock"></span>
					<span>User</span>
				</a>
			<?php } ?>
		</div>
	</div>
</nav>

<!-- Navbar login modal -->
<div class="modal fade" role="dialog" id="navLoginModal" aria-labelledby="navLoginModalTitle" tabindex="-1">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<form id="navLoginForm">
				<div class="modal-header">
					<h5 class="modal-title" id="navLoginModalTitle">Admin Login</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input class="form-control" type="email" id="navEmail" placeholder="Email address" maxlength="254" required/>
					</div>
					<div class="form-group">
						<input class="form-control" type="password" id="navPassword" placeholder="Password" required/>
					</div>
					<div class="form-group row">
						<a class="col" href="/fun/login/forgotPassword" target="_blank" style="text-align:right">Forgot Your Password?</a>
					</div>
					<div id="navMessage"></div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline-secondary" type="button" data-dismiss="modal">Close</button>
					<button class="btn btn-outline-primary" type="submit">
						<span class="fa fa-sign-in-alt"></span>
						<span>Login</span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Navbar JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		let $navEmail = $('#navEmail');
		let $navPassword = $('#navPassword');
		let $navLogoutBtn = $('#navLogoutBtn');
		let $navMessage = $('#navMessage');
		let $navLoginModal = $('#navLoginModal');
		let $navLoginForm = $('#navLoginForm');

		setupHandlers();

		function setupHandlers() {
			$navLoginModal.off().on('shown.bs.modal', onLoginModalShown);
			$navLoginForm.off().submit(handleLoginSubmit);
			$navLogoutBtn.off().click(handleLogoutClick);
			$navEmail.off().keydown((e) => clearMessage($navMessage));
			$navPassword.off().keydown((e) => clearMessage($navMessage));
		}

		function onLoginModalShown(e) {
			$navEmail.focus();
			clearMessage($navMessage);
		}

		function handleLoginSubmit(e) {
			clearMessage($navMessage);
			e.preventDefault();
			e.stopPropagation();

			// HTML5 form validation
			if ($navLoginForm[0].checkValidity() === false) {
				return;
			}

			// Build request data
			let formData = new FormData();
			formData.append('email', getEmailFromForm());
			formData.append('password', getPasswordFromForm());

			trackStats("LOGIN/fun/game");
			$.ajax({
				type: 'POST',
				url: '/fun/api/admin/login.php',
				data: formData,
				async: false,
				cache: false,
				contentType: false,
				processData: false,
				success: (resp) => {
					window.location.reload(true);
				},
				error: (jqXHR) => {
					errorMessage($navMessage, getErrorMessageFromResponse(jqXHR));
				}
			});
		}

		function handleLogoutClick(e) {
			trackStats("LOGOUT/fun/game");
			$.ajax({
				type: 'POST',
				url: '/fun/api/admin/logout.php',
				async: false,
				cache: false,
				success: (resp) => {
					window.location.reload(true);
				},
				error: (jqXHR) => {
					errorMessage($navMessage, getErrorMessageFromResponse(jqXHR));
				}
			});
		}

		function getEmailFromForm() {
			return ($navEmail.val() || "").replace(/\s/g, "");
		}

		function getPasswordFromForm() {
			return ($navPassword.val() || "").trim();
		}
	});
</script>
