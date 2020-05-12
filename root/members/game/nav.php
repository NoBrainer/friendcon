<!-- Navbar -->
<nav class="navbar navbar-expand-sm navbar-light bg-light">
	<!-- Branding -->
	<a class="navbar-brand" href="/game">FriendCon Game</a>

	<!-- Navbar toggler when collapsed -->
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#gameNav" aria-controls="gameNav" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<!-- Navbar contents -->
	<div class="collapse navbar-collapse" id="gameNav">
		<div class="navbar-nav mr-auto">
			<a class="nav-item nav-link<?php echo($navTab === "UPLOAD" ? ' active' : '') ?>" href="/members/game/upload">
				<span>Upload</span>
				<?php if ($navTab === "UPLOAD") { ?>
					<span class="sr-only">(current)</span>
				<?php } ?>
			</a>
			<a class="nav-item nav-link<?php echo($navTab === "ALBUM" ? ' active' : '') ?>" href="/members/game/album">
				<span>Album</span>
				<?php if ($navTab === "ALBUM") { ?>
					<span class="sr-only">(current)</span>
				<?php } ?>
			</a>
			<?php if ($isGameAdmin) { ?>
				<a class="nav-item nav-link<?php echo($navTab === "ADMIN" ? ' active' : '') ?>" href="/members/game/admin">
					<span>Admin</span>
					<?php if ($navTab === "ADMIN") { ?>
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

<?php if ($isGameAdmin && $navTab === "ADMIN") { ?>
	<div class="container-sm card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Admin Pages</h5>
			<div class="btn-group col-12 p-0 mb-1" role="group" aria-label="Admin Management Pages">
				<span class="col-4 p-1 pl-2 pr-2 rounded-left border border-right-0">Manage:</span>
				<a href="/members/game/admin/scores" class="btn btn-secondary col-4 p-1 pl-2 pr-2<?php echo($subNavPage === 'SCORES' ? ' active' : '') ?>">
					<span class="fa fa-coins"></span>
					<span>Scores</span>
				</a>
				<a href="/members/game/admin/uploads" class="btn btn-secondary col-4 p-1 pl-2 pr-2<?php echo($subNavPage === 'UPLOADS' ? ' active' : '') ?>">
					<span class="fa fa-file-image"></span>
					<span>Uploads</span>
				</a>
			</div>
			<div class="btn-group col-12 p-0" role="group" aria-label="Admin Setup Pages">
				<span class="col-4 p-1 pl-2 pr-2 rounded-left border border-right-0">Setup:</span>
				<a class="btn btn-secondary col-4 p-1 pl-2 pr-2<?php echo($subNavPage === 'SCHEDULE' ? ' active' : '') ?>" href="/members/game/admin/schedule">
					<span class="fa fa-calendar"></span>
					<span>Schedule</span>
				</a>
				<a class="btn btn-secondary col-4 p-1 pl-2 pr-2<?php echo($subNavPage === 'TEAMS' ? ' active' : '') ?>" href="/members/game/admin/teams">
					<span class="fa fa-users"></span>
					<span>Teams</span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

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
						<a class="col" href="/members/login/forgotPassword" target="_blank" style="text-align:right">Forgot Your Password?</a>
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

			trackStats("LOGIN/members/game");
			$.ajax({
				type: 'POST',
				url: '/members/api-v2/users/login.php',
				data: formData,
				async: false,
				cache: false,
				contentType: false,
				processData: false,
				success: (resp) => {
					window.location.reload(true);
				},
				error: (jqXHR) => {
					let resp = jqXHR.responseJSON;
					errorMessage($navMessage, resp.error, $navMessage);
				}
			});
		}

		function handleLogoutClick(e) {
			trackStats("LOGOUT/members/game");
			$.ajax({
				type: 'POST',
				url: '/members/api-v2/users/logout.php',
				async: false,
				cache: false,
				success: (resp) => {
					window.location.reload(true);
				},
				error: (jqXHR) => {
					let resp = jqXHR.responseJSON;
					errorMessage($navMessage, resp.error);
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
