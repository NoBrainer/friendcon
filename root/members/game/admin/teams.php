<?php
$pageTitle = "Setup Teams";
$navTab = "ADMIN";
$subNavPage = "TEAMS";
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
					<span>All teams shown here can be</span>
					<a href="/members/game/admin/scores">given points</a>
					<span>and be used to submit</span>
					<a href="/members/game/admin/uploads">uploads</a><span>.</span>
				</li>
				<li>You can only delete a team if it has no approved uploads and no members.</li>
			</ul>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-md">
		<div class="card-body">
			<div id="tableWrapper" class="table-responsive"></div>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Add Member</h5>
			<form id="addMemberForm">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<label class="input-group-text" for="addMemberName">Name:</label>
						</div>
						<input type="text" class="form-control" placeholder="Name" id="addMemberName" required>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group" id="addMemberDropdownWrapper">
						<div class="input-group-prepend">
							<label class="input-group-text" for="addMemberDropdown">Team:</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-outline-secondary" aria-label="Add member">
						<span>Add Member</span>
					</button>
				</div>
				<div class="form-group">
					<div id="addMemberMessage"></div>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal -->
<div id="teamModal" class="modal" tabindex="-1" role="dialog">
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
								<span class="input-group-text">Name:</span>
							</div>
							<input type="text" class="form-control" placeholder="Name" aria-label="Edit Name" id="modalName" required>
						</div>
					</div>
					<div class="form-group" id="modalMembersSection">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Members:</span>
							</div>
							<div class="form-control h-auto" id="modalMembersWrapper"></div>
						</div>
					</div>
					<div class="form-group">
						<div id="modalMessage"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="input-group mr-auto w-auto" id="deleteSection">
						<button type="button" class="btn btn-outline-danger form-control" id="deleteTeamBtn" disabled>Delete</button>
						<span class="input-group-append">
							<label class="sr-only" for="confirmDelete">Confirm Delete</label>
							<div class="input-group-text">
								<input type="checkbox" id="confirmDelete" aria-label="Checkbox for confirming team delete">
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
	<div id="editRowButton">
		<a class="fa fa-edit editTeam" data-toggle="modal" data-target="#teamModal" aria-label="Edit Team"></a>
	</div>
	<div id="memberPill">
		<h4 class="d-inline-block mb-2 mr-2 memberPill">
			<span class="badge badge-pill badge-secondary">
				<span class="d-inline-block text-truncate font-weight-normal text"></span>
				<span class="pointer remove">&times;</span>
			</span>
		</h4>
	</div>
	<div id="tableScaffold">
		<table class="table">
			<thead>
				<tr>
					<th class="border-0">
						<a class="fa fa-plus-square" id="addNewTeam" data-toggle="modal" data-target="#teamModal" aria-label="Create Team"></a>
					</th>
					<th class="border-0">Name</th>
					<th class="border-0">Members</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";
	const addMemberDropdownId = 'addMemberDropdown';

	$(document).ready(() => {
		const $tableWrapper = $('#tableWrapper');
		const $addMemberName = $('#addMemberName');
		const $addMemberDropdownWrapper = $('#addMemberDropdownWrapper');
		const $addMemberForm = $('#addMemberForm');
		const $addMemberMessage = $('#addMemberMessage');
		const $modal = $('#teamModal');
		const $modalTitle = $modal.find('.modal-title');
		const $modalForm = $('#modalForm');
		const $modalName = $('#modalName');
		const $modalMembersWrapper = $('#modalMembersWrapper');
		const $modalMembersSection = $('#modalMembersSection');
		const $modalMessage = $('#modalMessage');
		const $modalDeleteSection = $('#deleteSection');
		const $modalDeleteBtn = $('#deleteTeamBtn');
		const $modalConfirmDelete = $('#confirmDelete');
		const $modalSubmitBtn = $('#modalSubmitBtn');
		let lastTeamDropdownValue = -1;

		trackStats("LOAD/members/game/admin/teams");
		loadData({asAdmin: true}).done(render);

		function render() {
			$addMemberName.val("");
			renderTeamsTable();
			renderTeamsDropdown();
			setupHandlers();
		}

		function renderTeamsDropdown() {
			const objArr = [{text: "Random", value: "-1", selected: true}];
			_.each(teams, (team) => {
				objArr.push({text: team.name, value: team.teamIndex});
			});
			$addMemberDropdownWrapper.find('.custom-select').remove();
			$addMemberDropdownWrapper.append(select(objArr, addMemberDropdownId));

			// Make sure the selection persists through re-rendering
			const $dropdown = $('#' + addMemberDropdownId);
			$dropdown.val(lastTeamDropdownValue);
			$dropdown.off('change').change((e) => {
				lastTeamDropdownValue = $(e.currentTarget).val();
				clearMessage($addMemberMessage);
			});

			// Clear the message on change
			$addMemberName.off('keydown').keydown((e) => clearMessage($addMemberMessage));
		}

		function renderTeamsTable() {
			$tableWrapper.html(teamTable());
		}

		function setupHandlers() {
			let prevName;
			let prevScore;
			let prevUpdateTime;
			let prevMembers = [];
			let updatedMembers = [];

			// Enable the submit button on modal show
			$modal.off('show.bs.modal').on('show.bs.modal', (e) => enableSubmitButton(true));

			// Focus on the first input once the modal is shown
			$modal.off('shown.bs.modal').on('shown.bs.modal', (e) => $modalName.focus());

			// Keep the delete button disabled unless the confirm checkbox is checked
			$modalConfirmDelete.off().change((e) => {
				enableDeleteButton($modalConfirmDelete.is(':checked'));
			});

			// Edit team click handler
			$('.editTeam').off().click((e) => {
				const $btn = $(e.currentTarget);
				const teamIndex = $btn.attr('teamIndex');
				const team = getTeam(teamIndex);

				// Setup the modal
				clearMessage($modalMessage);
				$modalTitle.text("Edit Team");
				$modalMembersSection.show();
				$modalMembersWrapper.empty();
				$modalDeleteSection.show();
				enableDeleteButton(false);
				$modalSubmitBtn.text("Save");
				$modalSubmitBtn.removeClass('new');

				// Save the starting state
				prevName = team.name;
				prevScore = team.score;
				prevUpdateTime = team.updateTime;
				prevMembers = _.clone(team.members);
				updatedMembers = _.clone(team.members);

				// Set the starting state
				$modalForm.attr('teamIndex', team.teamIndex);
				$modalName.val(prevName);
				_.each(prevMembers, (member) => {
					$modalMembersWrapper.append(memberPill(member));
				});
				if (prevMembers.length === 0) $modalMembersWrapper.text("None");

				// Click handler for removing members
				$modalMembersWrapper.find('.remove').click((e) => {
					const $remove = $(e.currentTarget);
					const $pill = $remove.closest('.memberPill');
					const name = _.unescape($pill.attr('memberName'));

					// Only remove the first instance of the name to support duplicates
					let removed = false;
					updatedMembers = _.reduce(updatedMembers, (members, member) => {
						if (removed || member !== name) {
							members.push(member);
						} else if (member === name) {
							removed = true;
						}
						return members;
					}, []);
					$pill.remove();
					if (updatedMembers.length === 0) $modalMembersWrapper.text("None");
				});
			});

			// New team click handler
			$('#addNewTeam').off().click((e) => {
				// Setup the modal
				clearMessage($modalMessage);
				$modalTitle.text("New Team");
				$modalMembersSection.hide();
				$modalMembersWrapper.empty();
				$modalDeleteSection.hide();
				enableDeleteButton(false);
				$modalSubmitBtn.text("Create");
				$modalSubmitBtn.addClass('new');

				// Set the starting state
				$modalForm.attr('teamIndex', null);
				$modalName.val(null);
			});

			// Add member click handler
			$addMemberForm.off('submit').submit((e) => {
				clearMessage($addMemberMessage);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($addMemberForm[0].checkValidity() === false) {
					return;
				}

				const name = $addMemberName.val().trim();
				const teamIndex = parseInt($('#' + addMemberDropdownId).val());

				// Build request data
				const formData = new FormData();
				if (teamIndex !== -1) formData.append('teamIndex', teamIndex);
				formData.append('name', name);

				// Make the change
				trackStats("ADD_MEMBER/members/game/admin/teams");
				$.ajax({
					type: 'POST',
					url: "/members/api-v2/teams/members/create.php",
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					statusCode: {
						200: (resp) => {
							successMessage($addMemberMessage, resp.message);
							teams = resp.data.teams;
							resortTeams();
							render();

							// Close the modal in 2 seconds
							setTimeout(() => $modal.modal('hide'), 2000);
						},
						304: () => {
							successMessage($addMemberMessage, "No changes");

							// Close the modal in 2 seconds
							setTimeout(() => $modal.modal('hide'), 2000);
						}
					},
					error: (jqXHR) => {
						const resp = jqXHR.responseJSON;
						errorMessage($addMemberMessage, resp.error);
					}
				});
			});

			// Delete team click handler
			$modalDeleteBtn.off().click((e) => {
				enableDeleteButton(false);
				enableSubmitButton(false);

				const teamIndex = $modalForm.attr('teamIndex');
				const team = getTeam(teamIndex);

				// Optimistically make changes
				removeTeam(teamIndex);
				render();

				// Build request data
				const formData = new FormData();
				formData.append('teamIndex', teamIndex);

				// Make the change
				trackStats("DELETE/members/game/admin/teams");
				$.ajax({
					type: 'POST',
					url: '/members/api-v2/teams/delete.php',
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
						const resp = jqXHR.responseJSON;
						// errorMessage($modalMessage, resp.error);
						errorMessage($modalMessage, "NOT SETUP YET");

						// Revert changes
						addTeam(team);
						resortTeams();
						render();
					}
				});
			});

			// Add/Edit team submit handler
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

				let teamIndex;
				let team;

				const name = $modalName.val().trim();

				if (isNew) {
					team = getTempTeam();
					teamIndex = team.teamIndex;
				} else {
					teamIndex = $modalForm.attr('teamIndex');
					team = getTeam(teamIndex);
				}

				// Optimistically make changes
				team.name = name;
				team.members = updatedMembers;
				if (isNew) addTeam(team);
				resortTeams();
				render();

				// Build request data
				const formData = new FormData();
				if (!isNew) {
					formData.append('teamIndex', teamIndex);
					formData.append('members', updatedMembers.join(','));
				}
				formData.append('name', name);

				// Make the change
				trackStats((isNew ? 'CREATE' : 'EDIT') + "/members/game/admin/teams");
				$.ajax({
					type: 'POST',
					url: (isNew ? "/members/api-v2/teams/create.php" : "/members/api-v2/teams/update.php"),
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($modalMessage, resp.message);
						teams = resp.data;
						resortTeams();
						render();

						// Close the modal in 2 seconds
						setTimeout(() => $modal.modal('hide'), 2000);
					},
					error: (jqXHR) => {
						enableSubmitButton(true);
						const resp = jqXHR.responseJSON;
						errorMessage($modalMessage, resp.error);

						// Revert changes
						if (isNew) {
							removeTeam(teamIndex);
						} else {
							team.name = prevName;
							team.score = prevScore;
							team.updateTime = prevUpdateTime;
							team.members = prevMembers;
						}
						resortTeams();
						render();
					}
				});
			});
		}

		function teamTable() {
			const $table = $($('#tableScaffold').html());
			_.each(teams, (team) => {
				$table.append(teamRow(team));
			});
			return $table;
		}

		function teamRow(team) {
			const objArr = [
				{ele: editRowButton(team)},
				{ele: team.name, className: 'name'},
				{ele: countMembers(team), className: 'members'}
			];
			return tr(objArr);
		}

		function editRowButton(team) {
			const $btn = $($('#editRowButton').html());
			$btn.attr('teamIndex', team.teamIndex);
			return $btn;
		}

		function countMembers(team) {
			return "" + team.members.length;
		}

		function memberPill(name) {
			const $pill = $($('#memberPill').html());
			$pill.find('.text').text(name);
			$pill.attr('memberName', _.escape(name));
			return $pill;
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
