<!-- Navbar -->
<nav class="navbar navbar-expand-sm navbar-light bg-light">
	<!-- Branding -->
	<a class="navbar-brand">FriendCon Listserv</a>

	<!-- Navbar toggler when collapsed -->
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#listservNav" aria-controls="listservNav" aria-expanded="false" aria-label="Toggle navigation">
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
					<a class="nav-link dropdown-toggle" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="fa fa-user-lock"></span>
						<span>User</span>
					</a>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
						<a class="dropdown-item" href="/fun/game">
							<span class="fa fa-gamepad"></span>
							<span>Game</span>
						</a>
						<a class="dropdown-item" id="navLogoutBtn" href="#">
							<span class="fa fa-sign-out-alt"></span>
							<span>Log Out</span>
						</a>
					</div>
				</div>
			<?php } else { ?>
				<a class="nav-item" data-toggle="modal" data-target="#navLoginModal">
					<span class="fa fa-user-lock"></span>
					<span>User</span>
				</a>
			<?php } ?>
		</div>
	</div>
</nav>

<!-- Navbar login modal -->
<div class="modal fade" id="navLoginModal" tabindex="-1" role="dialog" aria-labelledby="navLoginModalTitle">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<form id="navLoginForm">
				<div class="modal-header">
					<h5 class="modal-title" id="navLoginModalTitle">Admin Login</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input type="email" class="form-control" placeholder="Email address" id="navEmail" required/>
					</div>
					<div class="form-group">
						<input type="password" class="form-control" placeholder="Password" id="navPassword" required/>
					</div>
					<div class="form-group row">
						<a class="col" href="/fun/login/forgotPassword" target="_blank" style="text-align:right">Forgot Your Password?</a>
					</div>
					<div id="navMessage"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-outline-primary">
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
