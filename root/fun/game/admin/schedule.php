<?php
$pageTitle = "Schedule Challenges";
$navTab = "ADMIN";
$subNavPage = "SCHEDULE";
$requireAdmin = true;
?>
<?php include('../head.php'); ?>
<body>
<?php include('../nav.php'); ?>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-fluid card mb-3 maxWidth-md">
		<div class="card-body">
			<h5 class="card-title"><?php echo $pageTitle; ?></h5>
			<p>How does this work?</p>
			<ul>
				<li>
					<span>Players can submit</span>
					<a href="/fun/game/admin/uploads">uploads</a>
					<span>for challenges within the scheduled time range.</span>
				</li>
				<li>Challenges without a start are immediately available.</li>
				<li>Challenges without an end do not automatically end.</li>
				<li>You cannot delete a challenge if it has any pending/approved uploads.</li>
			</ul>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-md">
		<div class="card-body">
			<div id="tableWrapper" class="table-responsive"></div>
		</div>
	</div>
</div>

<!-- Modal -->
<div id="challengeModal" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="modalForm">
				<div class="modal-header">
					<h5 class="modal-title"></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Description:</span>
							</div>
							<input type="text" class="form-control" placeholder="Description" aria-label="Edit Description" id="modalDescription" required>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group date" id="modalStartPicker" data-target-input="nearest">
							<span class="input-group-prepend">
								<span class="input-group-text">Start Time:</span>
							</span>
							<input type="text" class="form-control datetimepicker-input" data-target="#modalStartPicker" placeholder="NONE">
							<span class="input-group-append" data-toggle="datetimepicker" data-target="#modalStartPicker">
								<span class="input-group-text">
									<i class="fa fa-calendar"></i>
								</span>
							</span>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group date" id="modalEndPicker" data-target-input="nearest">
							<span class="input-group-prepend">
								<span class="input-group-text">End Time:</span>
							</span>
							<input type="text" class="form-control datetimepicker-input" data-target="#modalEndPicker" placeholder="NONE">
							<span class="input-group-append" data-toggle="datetimepicker" data-target="#modalEndPicker">
								<span class="input-group-text">
									<i class="fa fa-calendar"></i>
								</span>
							</span>
						</div>
					</div>
					<div class="form-group">
						<div id="modalMessage"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="input-group mr-auto w-auto" id="deleteSection">
						<button type="button" class="btn btn-outline-danger form-control" id="deleteChallengeBtn" disabled>Delete</button>
						<span class="input-group-append">
							<label class="sr-only" for="confirmDelete">Confirm Delete</label>
							<div class="input-group-text">
								<input type="checkbox" id="confirmDelete" aria-label="Checkbox for confirming challenge delete">
							</div>
						</span>
					</div>
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-outline-primary" id="modalSubmitBtn"></button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- HTML Templates -->
<div class="templates" style="display:none">
	<div id="datePicker">
		<div class="input-group date" data-target-input="nearest">
			<input type="text" class="form-control datetimepicker-input"/>
			<div class="input-group-append" data-toggle="datetimepicker">
				<div class="input-group-text">
					<i class="fa fa-calendar"></i>
				</div>
			</div>
		</div>
	</div>
	<div id="editRowButton">
		<a class="fa fa-edit editChallenge" data-toggle="modal" data-target="#challengeModal" aria-label="Edit Challenge"></a>
	</div>
	<div id="tableScaffold">
		<table class="table">
			<thead>
				<tr>
					<th class="border-0">
						<a class="fa fa-plus-square" id="addNewChallenge" data-toggle="modal" data-target="#challengeModal" aria-label="Create Challenge"></a>
					</th>
					<th class="border-0">Description</th>
					<th class="border-0">Start</th>
					<th class="border-0">End</th>
				</tr>
			</thead>
		</table>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";

	$(document).ready(() => {
		const $tableWrapper = $('#tableWrapper');
		const $modal = $('#challengeModal');
		const $modalTitle = $modal.find('.modal-title');
		const $modalForm = $('#modalForm');
		const $modalDescription = $('#modalDescription');
		const $modalStartPicker = $('#modalStartPicker');
		const $modalEndPicker = $('#modalEndPicker');
		const $modalMessage = $('#modalMessage');
		const $modalDeleteSection = $('#deleteSection');
		const $modalDeleteBtn = $('#deleteChallengeBtn');
		const $modalConfirmDelete = $('#confirmDelete');
		const $modalSubmitBtn = $('#modalSubmitBtn');

		trackStats("LOAD/fun/game/admin/schedule");
		loadData({asAdmin: true}).done(render);
		setupDatePicker($modalStartPicker);
		setupDatePicker($modalEndPicker);

		function render() {
			renderTable();
			setupHandlers();
		}

		function renderTable() {
			$tableWrapper.html(scheduleTable());
		}

		function setupHandlers() {
			let prevDescription;
			let prevStartTime;
			let prevEndTime;

			// Enable the submit button on modal show
			$modal.off('show.bs.modal').on('show.bs.modal', (e) => enableSubmitButton(true));

			// Focus on the first input once the modal is shown
			$modal.off('shown.bs.modal').on('shown.bs.modal', (e) => $modalDescription.focus());

			// Keep the delete button disabled unless the confirm checkbox is checked
			$modalConfirmDelete.off().change((e) => {
				enableDeleteButton($modalConfirmDelete.is(':checked'));
			});

			// Edit challenge click handler
			$('.editChallenge').off().click((e) => {
				const $btn = $(e.currentTarget);
				const challengeIndex = $btn.attr('challengeIndex');
				const challenge = getChallenge(challengeIndex);

				// Setup the modal
				clearMessage($modalMessage);
				$modalTitle.text("Edit Challenge");
				$modalDeleteSection.show();
				enableDeleteButton(false);
				$modalSubmitBtn.text("Save");
				$modalSubmitBtn.removeClass('new');

				// Save the starting state
				prevDescription = challenge.description;
				prevStartTime = challenge.startTime;
				prevEndTime = challenge.endTime;

				// Set the starting state
				$modalForm.attr('challengeIndex', challenge.challengeIndex);
				$modalDescription.val(prevDescription);
				$modalStartPicker.datetimepicker('date', datePickerValue(prevStartTime));
				$modalEndPicker.datetimepicker('date', datePickerValue(prevEndTime));
			});

			// New challenge click handler
			$('#addNewChallenge').off().click((e) => {
				// Setup the modal
				clearMessage($modalMessage);
				$modalTitle.text("New Challenge");
				$modalDeleteSection.hide();
				enableDeleteButton(false);
				$modalSubmitBtn.text("Create");
				$modalSubmitBtn.addClass('new');

				// Set the starting state
				$modalForm.attr('challengeIndex', null);
				$modalDescription.val(null);
				$modalStartPicker.datetimepicker('date', null);
				$modalEndPicker.datetimepicker('date', null);
			});

			// Delete challenge click handler
			$modalDeleteBtn.off().click((e) => {
				enableDeleteButton(false);
				enableSubmitButton(false);

				const challengeIndex = $modalForm.attr('challengeIndex');
				const challenge = getChallenge(challengeIndex);

				// Optimistically make changes
				removeChallenge(challengeIndex);
				render();

				// Build request data
				const formData = new FormData();
				formData.append('challengeIndex', challengeIndex);

				// Make the change
				trackStats("DELETE/fun/game/admin/schedule");
				$.ajax({
					type: 'POST',
					url: '/fun/api/challenges/delete.php',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($modalMessage, resp.message);

						// Close the modal in 2 seconds
						setTimeout(() => $modal.modal('hide'), 2000);
					},
					error: (jqXHR) => {
						enableSubmitButton(true);
						errorMessage($modalMessage, getErrorMessageFromResponse(jqXHR));

						// Revert changes
						addChallenge(challenge);
						resortChallenges();
						render();
					}
				});
			});

			// Add/Edit challenge submit handler
			$modalForm.off('submit').submit((e) => {
				clearMessage($modalMessage);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($modalForm[0].checkValidity() === false) {
					return;
				}

				enableDeleteButton(false);
				enableSubmitButton(false);
				const isNew = $modalSubmitBtn.hasClass('new');

				let challengeIndex;
				let challenge;

				const description = $modalDescription.val().trim();
				const startTime = getDateStringFromPicker($modalStartPicker);
				const endTime = getDateStringFromPicker($modalEndPicker);

				if (isNew) {
					challenge = getTempChallenge();
					challengeIndex = challenge.challengeIndex;
				} else {
					challengeIndex = $modalForm.attr('challengeIndex');
					challenge = getChallenge(challengeIndex);
				}

				// Optimistically make changes
				challenge.description = description;
				challenge.startTime = startTime;
				challenge.endTime = endTime;
				if (isNew) addChallenge(challenge);
				resortChallenges();
				render();

				// Build request data
				const formData = new FormData();
				if (!isNew) formData.append('challengeIndex', challengeIndex);
				formData.append('description', description);
				formData.append('startTime', startTime);
				formData.append('endTime', endTime);

				// Make the change
				trackStats((isNew ? 'CREATE' : 'EDIT') + "/fun/game/admin/schedule");
				$.ajax({
					type: 'POST',
					url: (isNew ? "/fun/api/challenges/create.php" : "/fun/api/challenges/update.php"),
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					statusCode: {
						200: (resp) => {
							successMessage($modalMessage, resp.message);
							challenge.challengeIndex = resp.data.challengeIndex;
							challenge.description = resp.data.description;
							challenge.startTime = resp.data.startTime;
							challenge.endTime = resp.data.endTime;
							challenge.published = resp.data.published;
							resortChallenges();
							render();

							// Close the modal in 2 seconds
							setTimeout(() => $modal.modal('hide'), 2000);
						},
						304: () => {
							successMessage($modalMessage, "No changes");

							// Close the modal in 2 seconds
							setTimeout(() => $modal.modal('hide'), 2000);
						}
					},
					error: (jqXHR) => {
						enableSubmitButton(true);
						errorMessage($modalMessage, getErrorMessageFromResponse(jqXHR));

						// Revert changes
						if (isNew) {
							removeChallenge(challengeIndex);
						} else {
							challenge.description = prevDescription;
							challenge.startTime = prevStartTime;
							challenge.endTime = prevEndTime;
						}
						resortChallenges();
						render();
					}
				});
			});
		}

		function setupDatePicker($picker) {
			$picker.datetimepicker({
				buttons: {showClear: true, showClose: true, showToday: true},
				format: DATE_FORMAT_DISPLAY,
				icons: {today: 'fa fa-calendar-day', clear: 'fa fa-trash-alt', close: 'fa fa-check'},
				maxDate: false,
				minDate: false,
				sideBySide: true,
				toolbarPlacement: 'top'
			});
		}

		function scheduleTable() {
			const $table = $($('#tableScaffold').html());
			_.each(challenges, (challenge) => {
				$table.append(challengeRow(challenge));
			});
			return $table;
		}

		function challengeRow(challenge) {
			const objArr = [
				{ele: editRowButton(challenge)},
				{ele: challenge.description, className: 'description'},
				{ele: dateDisplayFormat(challenge.startTime), className: 'small startTime'},
				{ele: dateDisplayFormat(challenge.endTime), className: 'small endTime'}
			];
			return tr(objArr);
		}

		function editRowButton(challenge) {
			const $btn = $($('#editRowButton').html());
			$btn.attr('challengeIndex', challenge.challengeIndex);
			return $btn;
		}

		function enableSubmitButton(enabled) {
			$modalSubmitBtn.prop('disabled', !enabled);
		}

		function enableDeleteButton(enabled) {
			$modalConfirmDelete.prop('checked', enabled);
			$modalDeleteBtn.prop('disabled', !enabled);
		}
	});
</script>
</body>
</html>
