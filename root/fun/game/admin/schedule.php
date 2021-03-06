<?php
$pageTitle = "Schedule Challenges";
$navTab = "ADMIN";
$subNavPage = "SCHEDULE";
$requireAdmin = true;
include('../head.php');
?>
<body>
<?php include('../nav.php'); ?>

<!-- Content -->
<div class="container-fluid" id="content">
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
				<li>Challenges without a start are immediately available for submissions.</li>
				<li>Challenges without an end do not automatically end.</li>
				<li>You cannot delete a challenge if it has any approved uploads.</li>
			</ul>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-md">
		<div class="card-body">
			<div class="table-responsive" id="tableWrapper"></div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal" role="dialog" id="challengeModal" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="modalForm">
				<div class="modal-header">
					<h5 class="modal-title"></h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" style="min-width:100px">Name:</span>
							</div>
							<input class="form-control" type="text" id="modalName" placeholder="Name" aria-label="Edit Name" required>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group date flatpickr" id="modalStartPicker">
							<span class="input-group-prepend">
								<span class="input-group-text" style="min-width:100px">Start Time:</span>
							</span>
							<input class="form-control flatpickr-input" type="text" placeholder="NONE" aria-label="Pick start time" data-input>
							<span class="input-group-append">
								<a class="btn btn-outline-secondary input-button" title="Clear start time" data-clear>&times;</a>
							</span>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group date flatpickr" id="modalEndPicker">
							<span class="input-group-prepend">
								<span class="input-group-text" style="min-width:100px">End Time:</span>
							</span>
							<input class="form-control flatpickr-input" type="text" placeholder="NONE" aria-label="Pick end time" data-input>
							<span class="input-group-append">
								<a class="btn btn-outline-secondary input-button" title="Clear end time" data-clear>&times;</a>
							</span>
						</div>
					</div>
					<div class="form-group">
						<div id="modalMessage"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="input-group mr-auto w-auto" id="deleteSection">
						<button class="btn btn-outline-danger form-control" type="button" id="deleteChallengeBtn" disabled>Delete</button>
						<span class="input-group-append">
							<label class="sr-only" for="confirmDelete">Confirm Delete</label>
							<div class="input-group-text">
								<input type="checkbox" id="confirmDelete" aria-label="Checkbox for confirming challenge delete">
							</div>
						</span>
					</div>
					<button class="btn btn-outline-secondary" type="button" data-dismiss="modal">Cancel</button>
					<button class="btn btn-outline-primary" type="submit" id="modalSubmitBtn"></button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- HTML Templates -->
<div class="templates" aria-hidden="true" style="display:none">
	<div id="editRowButton">
		<a class="fa fa-edit editChallenge" data-target="#challengeModal" data-toggle="modal" aria-label="Edit Challenge"></a>
	</div>
	<div id="tableScaffold">
		<table class="table">
			<thead>
				<tr>
					<th class="border-0">
						<a class="fa fa-plus-square" id="addNewChallenge" data-target="#challengeModal" data-toggle="modal" aria-label="Create Challenge"></a>
					</th>
					<th class="border-0">Name</th>
					<th class="border-0">Start</th>
					<th class="border-0">End</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		const $tableWrapper = $('#tableWrapper');
		const $modal = $('#challengeModal');
		const $modalTitle = $modal.find('.modal-title');
		const $modalForm = $('#modalForm');
		const $modalName = $('#modalName');
		const $modalStartPicker = $('#modalStartPicker');
		const $modalEndPicker = $('#modalEndPicker');
		const $modalMessage = $('#modalMessage');
		const $modalFooter = $modal.find('.modal-footer');
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
			let prevName;
			let prevStartTime;
			let prevEndTime;

			// Focus on the first input once the modal is shown
			$modal.off('shown.bs.modal').on('shown.bs.modal', (e) => {
				$modalFooter.show();
				$modalName.focus();
			});

			// Keep the delete button disabled unless the confirm checkbox is checked
			$modalConfirmDelete.off().change((e) => {
				enableDeleteButton($modalConfirmDelete.is(':checked'));
			});

			// Clear the message as the form changes
			$modalName.keydown(clearModalMessageUnlessEnter);

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
				prevName = challenge.name;
				prevStartTime = challenge.startTime;
				prevEndTime = challenge.endTime;

				// Set the starting state
				$modalForm.attr('challengeIndex', challenge.challengeIndex);
				$modalName.val(prevName);
				setDateStringForPicker($modalStartPicker, prevStartTime);
				setDateStringForPicker($modalEndPicker, prevEndTime);
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
				$modalName.val(null);
				clearDatePicker($modalStartPicker);
				clearDatePicker($modalEndPicker);
			});

			// Delete challenge click handler
			$modalDeleteBtn.off().click((e) => {
				$modalFooter.hide();

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
					},
					error: (jqXHR) => {
						$modalFooter.show();
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

				$modalFooter.hide();
				const isNew = $modalSubmitBtn.hasClass('new');

				let challengeIndex;
				let challenge;

				const name = $modalName.val().trim();
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
				challenge.name = name;
				challenge.startTime = startTime;
				challenge.endTime = endTime;
				if (isNew) addChallenge(challenge);
				resortChallenges();
				render();

				// Build request data
				const formData = new FormData();
				if (!isNew) formData.append('challengeIndex', challengeIndex);
				formData.append('name', name);
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
							if (isNew && $modal.data('bs.modal')._isShown) {
								// Reset form to make it easy for multi-create
								$modalName.val(null);
								clearDatePicker($modalStartPicker);
								clearDatePicker($modalEndPicker);
								$modalName.focus();
							}
							successMessage($modalMessage, resp.message);
							challenge.challengeIndex = resp.data.challengeIndex;
							challenge.name = resp.data.name;
							challenge.startTime = resp.data.startTime;
							challenge.endTime = resp.data.endTime;
							challenge.published = resp.data.published;
							resortChallenges();
							render();
						},
						304: () => {
							successMessage($modalMessage, "No changes");
						}
					},
					error: (jqXHR) => {
						errorMessage($modalMessage, getErrorMessageFromResponse(jqXHR));

						// Revert changes
						if (isNew) {
							removeChallenge(challengeIndex);
						} else {
							challenge.name = prevName;
							challenge.startTime = prevStartTime;
							challenge.endTime = prevEndTime;
						}
						resortChallenges();
						render();
					},
					complete: () => {
						$modalFooter.show();
					}
				});
			});
		}

		function setupDatePicker($picker) {
			initializePicker($picker, {
				onChange: clearModalMessageUnlessEnter
			});
		}

		function clearModalMessageUnlessEnter(e) {
			if (e.which !== 32) clearMessage($modalMessage);
		}

		function scheduleTable() {
			const $table = $($('#tableScaffold').html());
			const $tbody = $table.find('tbody');
			if (challenges.length === 0) {
				$tbody.append("Add challenges with the '+' button above.");
			} else {
				_.each(challenges, (challenge) => {
					$tbody.append(challengeRow(challenge));
				});
			}
			return $table;
		}

		function challengeRow(challenge) {
			const objArr = [
				{ele: editRowButton(challenge)},
				{ele: challenge.name, className: 'name'},
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

		function enableDeleteButton(enabled) {
			$modalConfirmDelete.prop('checked', enabled);
			$modalDeleteBtn.prop('disabled', !enabled);
		}
	});
</script>
</body>
</html>
