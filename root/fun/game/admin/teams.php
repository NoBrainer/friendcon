<?php
$pageTitle = "Setup Teams";
$navTab = "ADMIN";
$subNavPage = "TEAMS";
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
					<span>All teams shown here can be</span>
					<a href="/fun/game/admin/scores">given points</a>
					<span>and be used to submit</span>
					<a href="/fun/game/admin/uploads">uploads</a><span>.</span>
				</li>
				<li>You can only delete a team if it has no approved uploads and no members.</li>
				<li>Edit a team to remove members from it.</li>
			</ul>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-md">
		<div class="card-body">
			<div class="table-responsive" id="tableWrapper"></div>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Add Member</h5>
			<form id="addMemberForm">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<label class="input-group-text" for="addMemberName" style="min-width:70px">Name:</label>
						</div>
						<input class="form-control" type="text" id="addMemberName" placeholder="Name" required>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group" id="addMemberDropdownWrapper">
						<div class="input-group-prepend">
							<label class="input-group-text" for="addMemberDropdown" style="min-width:70px">Team:</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<button class="btn btn-outline-secondary" type="submit" aria-label="Add member">
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
<div class="modal" role="dialog" id="teamModal" tabindex="-1">
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
					<div class="form-group" id="modalMembersSection">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" style="min-width:100px">Members:</span>
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
						<button class="btn btn-outline-danger form-control" type="button" id="deleteTeamBtn" disabled>Delete</button>
						<span class="input-group-append">
							<label class="sr-only" for="confirmDelete">Confirm Delete</label>
							<div class="input-group-text">
								<input type="checkbox" id="confirmDelete" aria-label="Checkbox for confirming team delete">
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
		<a class="fa fa-edit editTeam" data-target="#teamModal" data-toggle="modal" aria-label="Edit Team"></a>
	</div>
	<div id="memberPill">
		<h4 class="d-inline-block mb-2 mr-2 memberPill">
			<span class="badge badge-pill badge-secondary">
				<span class="d-inline-block text-truncate font-weight-normal align-middle text"></span>
				<span class="pointer remove">&times;</span>
			</span>
		</h4>
	</div>
	<div id="tableScaffold">
		<table class="table">
			<thead>
				<tr>
					<th class="border-0">
						<a class="fa fa-plus-square" id="addNewTeam" data-target="#teamModal" data-toggle="modal" aria-label="Create Team"></a>
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
		const $modalFooter = $modal.find('.modal-footer');
		const $modalDeleteSection = $('#deleteSection');
		const $modalDeleteBtn = $('#deleteTeamBtn');
		const $modalConfirmDelete = $('#confirmDelete');
		const $modalSubmitBtn = $('#modalSubmitBtn');
		let lastTeamDropdownValue = -1;

		trackStats("LOAD/fun/game/admin/teams");
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
			$modalName.keydown(clearMessageUnlessEnter);
			function clearMessageUnlessEnter(e) {
				if (e.which !== 32) clearMessage($modalMessage);
			}

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
				trackStats("ADD_MEMBER/fun/game/admin/teams");
				$.ajax({
					type: 'POST',
					url: "/fun/api/teams/members/create.php",
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
						},
						304: () => {
							successMessage($addMemberMessage, "No changes");
						}
					},
					error: (jqXHR) => {
						errorMessage($addMemberMessage, getErrorMessageFromResponse(jqXHR));
					}
				});
			});

			// Delete team click handler
			$modalDeleteBtn.off().click((e) => {
				$modalFooter.hide();
				const teamIndex = $modalForm.attr('teamIndex');
				const team = getTeam(teamIndex);

				// Optimistically make changes
				removeTeam(teamIndex);
				render();

				// Build request data
				const formData = new FormData();
				formData.append('teamIndex', teamIndex);

				// Make the change
				trackStats("DELETE/fun/game/admin/teams");
				$.ajax({
					type: 'POST',
					url: '/fun/api/teams/delete.php',
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

				$modalFooter.hide();
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
				trackStats((isNew ? 'CREATE' : 'EDIT') + "/fun/game/admin/teams");
				$.ajax({
					type: 'POST',
					url: (isNew ? "/fun/api/teams/create.php" : "/fun/api/teams/update.php"),
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						if (isNew && $modal.data('bs.modal')._isShown) {
							// Reset form to make it easy for multi-create
							$modalName.val(null);
							$modalName.focus();
						}
						successMessage($modalMessage, resp.message);
						teams = resp.data;
						resortTeams();
						render();
					},
					error: (jqXHR) => {
						errorMessage($modalMessage, getErrorMessageFromResponse(jqXHR));

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
					},
					complete: () => {
						$modalFooter.show();
					}
				});
			});
		}

		function teamTable() {
			const $table = $($('#tableScaffold').html());
			const $tbody = $table.find('tbody');
			if (teams.length === 0) {
				$tbody.append("Add teams with the '+' button above.");
			} else {
				_.each(teams, (team) => {
					$tbody.append(teamRow(team));
				});
			}
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

		function enableDeleteButton(enabled) {
			$modalConfirmDelete.prop('checked', enabled);
			$modalDeleteBtn.prop('disabled', !enabled);
		}
	});
</script>
</body>
</html>
