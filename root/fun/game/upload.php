<?php
$pageTitle = "Game";
$navTab = "UPLOAD";
$subNavPage = null;
$requireAdmin = false;
?>
<?php include('head.php'); ?>
<body>
<?php include('nav.php'); ?>

<!-- Content - Based on: https://www.tutorialrepublic.com/php-tutorial/php-file-upload.php -->
<div id="content" class="container-fluid">
	<div class="container-sm card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Current Challenges</h5>
			<ul class="list-group" id="currentChallenges"></ul>
		</div>
	</div>
	<div class="container-sm card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Submit Photos</h5>
			<form action="/fun/api/uploads/create.php" method="post" enctype="multipart/form-data" id="uploadForm">
				<div class="form-group ">
					<div class="input-group" id="teamDropdownWrapper">
						<div class="input-group-prepend">
							<label class="input-group-text" for="pickTeam">Team:</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group" id="challengeDropdownWrapper">
						<div class="input-group-prepend">
							<label class="input-group-text" for="pickChallenge">Challenge:</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text" id="fileLabel">Upload</span>
						</div>
						<div class="custom-file">
							<input type="file" class="custom-file-input" id="file" aria-describedby="fileLabel" required>
							<label class="d-inline-block text-truncate custom-file-label" for="file">
								<span id="fileLabelText" class="d-inline-block text-truncate">Choose file</span>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group" id="captchaWrapper"></div>
				<div class="form-group">
					<button id="submitButton" type="submit" class="btn btn-outline-secondary" aria-label="submit button" disabled="disabled">
						<span>Submit</span>
					</button>
				</div>
				<div class="form-group">
					<div id="message"></div>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- HTML Templates -->
<div class="templates" style="display:none">
	<div id="challengeListItem">
		<li class="list-group-item">
			<div class="font-weight-bold name"></div>
			<div class="startTimeRow">
				<span>Start:</span>
				<span class="text-monospace startTime"></span>
			</div>
			<div class="endTimeRow">
				<span>End:</span>
				<span class="text-monospace endTime"></span>
			</div>
		</li>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV2Key = "<?php echo CAPTCHA_SITE_V2_KEY; ?>";
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";
	const MAX_FILE_SIZE = 5 * 1024 * 1024; //5MB
	const pickTeamId = 'pickTeam';
	const pickChallengeId = 'pickChallenge';

	$(document).ready(() => {
		const $currentChallenges = $('#currentChallenges');
		const $teamDropdownWrapper = $('#teamDropdownWrapper');
		const $challengeDropdownWrapper = $('#challengeDropdownWrapper');
		const $file = $('#file');
		const $fileLabelText = $('#fileLabelText');
		const $form = $('#uploadForm');
		const $submitButton = $('#submitButton');
		const $message = $('#message');

		trackStats("LOAD/fun/game/upload");
		loadData().done(render);

		function render() {
			renderCurrentChallenges();
			renderCaptchaCheckbox();
			renderTeamsDropdown();
			renderChallengesDropdown();
			setupFormHandlers();
		}

		function renderCurrentChallenges() {
			$currentChallenges.empty();
			_.each(challenges, (challenge) => {
				if (hasChallengeStarted(challenge) && !hasChallengeEnded(challenge)) {
					$currentChallenges.append(challengeListItem(challenge));
				}
			});
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

		function renderTeamsDropdown() {
			const objArr = [{text: "Pick team", value: "-1", selected: true}];
			_.each(teams, (team) => {
				objArr.push({text: team.name, value: team.teamIndex});
			});
			$teamDropdownWrapper.find('.custom-select').remove();
			$teamDropdownWrapper.append(select(objArr, pickTeamId));
		}

		function renderChallengesDropdown() {
			const objArr = [{text: "Pick challenge", value: "-1", selected: true}];
			_.each(challenges, (challenge) => {
				if (hasChallengeStarted(challenge) && !hasChallengeEnded(challenge)) {
					objArr.push({text: challenge.description, value: challenge.challengeIndex});
				}
			});
			$challengeDropdownWrapper.find('select').remove();
			$challengeDropdownWrapper.append(select(objArr, pickChallengeId));
		}

		function setupFormHandlers() {
			// File picker handler
			$file.change((e) => {
				// Clear the message when you change input values
				clearMessage($message);

				// Update the file label text
				const files = $file[0].files;
				if (files.length > 0) {
					$fileLabelText.text(files[0].name);
				}
			});

			// Submit handler
			$form.submit((e) => {
				enableSubmitButton(false);
				clearMessage($message);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($form[0].checkValidity() === false) {
					return;
				}

				// Logic validation
				const teamIndex = $('#' + pickTeamId).val();
				const challengeIndex = $('#' + pickChallengeId).val();
				const files = $file[0].files;
				if (teamIndex < 0) {
					errorMessage($message, "Must pick a team.");
					enableSubmitButton(true);
					return;
				} else if (challengeIndex < 0) {
					errorMessage($message, "Must pick a challenge.");
					enableSubmitButton(true);
					return;
				} else if (files == null || files.length === 0) {
					errorMessage($message, "No file provided.");
					enableSubmitButton(true);
					return;
				} else if (files.length > 1) {
					errorMessage($message, "Must only include one file.");
					enableSubmitButton(true);
					return;
				} else if (files[0].size > MAX_FILE_SIZE) {
					errorMessage($message, "File must be less than 5MB.");
					enableSubmitButton(true);
					return;
				}

				// Build request data
				const formData = new FormData();
				formData.append('teamIndex', teamIndex);
				formData.append('challengeIndex', challengeIndex);
				formData.append('fileUpload', files[0]); //key determines key in $_FILES[key] server-side
				formData.append('MAX_FILE_SIZE', MAX_FILE_SIZE);

				// Make the server call
				trackStats("SUBMIT/fun/game/index");
				$.ajax({
					type: 'POST',
					url: '/fun/api/uploads/create.php',
					data: formData,
					async: false,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($message, resp.message);
					},
					error: (jqXHR) => {
						errorMessage($message, getErrorMessageFromResponse(jqXHR));
					},
					complete: () => {
						// Reset the CAPTCHA checkbox after each upload
						grecaptcha.reset();
						enableSubmitButton(false);
					}
				});
			});
		}

		function enableSubmitButton(enabled) {
			$submitButton.prop('disabled', !enabled);
		}

		function challengeListItem(challenge) {
			const $item = $($('#challengeListItem').html());
			$item.find('.name').text(challenge.description);
			$item.find('.startTime').text(dateDisplayFormat(challenge.startTime, 'whenever'));
			$item.find('.endTime').text(dateDisplayFormat(challenge.endTime, 'whenever'));
			return $item;
		}
	});
</script>
</body>
</html>
