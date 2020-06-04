<?php
$pageTitle = "Admin";
$requireAdmin = true;
include('head.php');
?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div class="container-fluid" id="content">
	<div class="container-fluid card mb-3 maxWidth-lg">
		<div class="card-body">
			<h5 class="card-title">Admins</h5>
			<div class="table-responsive">
				<table class="table" id="adminTable">
					<thead>
						<th class="pl-0 border-0">
							<a class="fa fa-plus-square" id="addNewAdmin" data-target="#adminModal" data-toggle="modal" aria-label="Add New Admin"></a>
						</th>
						<th class="pl-0 border-0">Name</th>
						<th class="pl-0 border-0">Email</th>
						<th class="pl-0 border-0 text-wrap">Game Admin?</th>
						<th class="pl-0 pr-0 border-0 text-wrap">Site Admin?</th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-lg">
		<div class="card-body">
			<h5 class="card-title">Global Variables</h5>
			<div class="table-responsive">
				<table class="table" id="variableTable">
					<thead>
						<th class="pl-0 border-0">
							<a class="fa fa-plus-square" id="addNewVariable" data-target="#variableModal" data-toggle="modal" aria-label="Create New Variable"></a>
						</th>
						<th class="pl-0 border-0">Name</th>
						<th class="pl-0 border-0">Type</th>
						<th class="pl-0 border-0">Value</th>
						<th class="pl-0 pr-0 border-0">Description</th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-sm border-danger">
		<div class="card-body">
			<h5 class="card-title">Danger Zone</h5>

			<!-- Reset Game Data Section -->
			<div class="input-group row mb-0">
				<div class="input-group-prepend col-10 pr-0">
					<span class="input-group-text form-control font-weight-bold border-bottom-0 rounded-0">Reset Game Data</span>
				</div>
				<label class="sr-only" for="confirmResetGameData">Text box to confirm intentions to reset game data</label>
				<input class="form-control text-danger col-2 border-bottom-0 rounded-0" type="text" id="confirmResetGameData" placeholder="Reset?">
			</div>
			<div class="input-group row mb-0">
				<div class="input-group-prepend col-10 pr-0">
					<div class="input-group-text form-control h-auto rounded-0">
						<div class="text-wrap small text-left">
							This resets the database for the game without deleting files on the server.
							<b>This CANNOT be undone!</b> Type 'RESET' to confirm your intentions.
						</div>
					</div>
				</div>
				<button class="btn btn-outline-danger form-control col-2 h-auto rounded-0" type="submit" id="resetGameDataBtn">Reset</button>
			</div>
			<div class="form-group mb-3">
				<div id="resetGameDataMessage"></div>
			</div>
		</div>
	</div>
</div>

<!-- Global Variable Modal -->
<div class="modal" role="dialog" id="variableModal" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="variableModalForm">
				<div class="modal-header">
					<h5 class="modal-title"></h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="variableModalName">Name:</label>
						</span>
						<input class="form-control" type="text" id="variableModalName" placeholder="Name" maxlength="32" required>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="variableModalType">Type:</label>
						</span>
						<select class="custom-select" id="variableModalType">
							<option value="boolean">boolean</option>
							<option value="integer">integer</option>
							<option value="string">string</option>
						</select>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="variableModalValue">Value:</label>
						</span>
						<input class="form-control" type="text" id="variableModalValue" placeholder="Value" maxlength="32" required>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="variableModalDescription">Description:</label>
						</span>
						<input class="form-control" type="text" id="variableModalDescription" placeholder="Description (optional)" maxlength="64">
					</div>
					<div class="form-group">
						<div id="variableModalMessage"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="input-group mr-auto w-auto" id="variableModalDeleteSection">
						<button class="btn btn-outline-danger form-control" type="button" id="variableModalDeleteBtn" disabled>Delete</button>
						<span class="input-group-append">
							<label class="sr-only" for="variableModalConfirmDelete">Confirm Delete</label>
							<div class="input-group-text">
								<input type="checkbox" id="variableModalConfirmDelete" aria-label="Checkbox for confirming variable delete">
							</div>
						</span>
					</div>
					<button class="btn btn-outline-secondary" type="button" data-dismiss="modal">Cancel</button>
					<button class="btn btn-outline-primary" type="submit" id="variableModalSubmitBtn"></button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Admin Modal -->
<div class="modal" role="dialog" id="adminModal" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="adminModalForm">
				<div class="modal-header">
					<h5 class="modal-title"></h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="adminModalName">Name:</label>
						</span>
						<input class="form-control" type="text" id="adminModalName" placeholder="Name" maxlength="32" required>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="adminModalEmail">Email:</label>
						</span>
						<input class="form-control" type="email" id="adminModalEmail" placeholder="Email" maxlength="254" required>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="adminModalIsGameAdmin">Game Admin?</label>
						</span>
						<span class="input-group-append">
							<span class="input-group-text bg-transparent border-left-0">
								<input type="checkbox" id="adminModalIsGameAdmin" aria-label="Checkbox for game admin">
							</span>
						</span>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="adminModalIsSiteAdmin">Site Admin?</label>
						</span>
						<span class="input-group-append">
							<span class="input-group-text bg-transparent border-left-0">
								<input type="checkbox" id="adminModalIsSiteAdmin" aria-label="Checkbox for site admin">
							</span>
						</span>
					</div>
					<div class="form-group">
						<div id="adminModalMessage"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="input-group mr-auto w-auto" id="adminModalDeleteSection">
						<button class="btn btn-outline-danger form-control" type="button" id="adminModalDeleteBtn" disabled>Delete</button>
						<span class="input-group-append">
							<label class="sr-only" for="adminModalConfirmDelete">Confirm Delete</label>
							<div class="input-group-text">
								<input type="checkbox" id="adminModalConfirmDelete" aria-label="Checkbox for confirming admin delete">
							</div>
						</span>
					</div>
					<button class="btn btn-outline-secondary" type="button" data-dismiss="modal">Cancel</button>
					<button class="btn btn-outline-primary" type="submit" id="adminModalSubmitBtn"></button>
				</div>
			</form>
		</div>
	</div>
</div>

<!--  HTML Templates -->
<div class="templates" style="display:none">
	<div id="editAdminButton">
		<a class="fa fa-edit editAdmin" data-target="#adminModal" data-toggle="modal" aria-label="Edit Admin"></a>
	</div>
	<div id="editVariableButton">
		<a class="fa fa-edit editVariable" data-target="#variableModal" data-toggle="modal" aria-label="Edit Global Variable"></a>
	</div>
	<div id="iconFalse">
		<span class="far fa-square" aria-label="false"></span>
	</div>
	<div id="iconTrue">
		<span class="far fa-check-square" aria-label="true"></span>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		// Admins Card
		const $adminTableBody = $('#adminTable').find('tbody');
		const $addNewAdmin = $('#addNewAdmin');
		const $adminModal = $('#adminModal');
		const $adminModalForm = $('#adminModalForm');
		const $adminModalTitle = $adminModal.find('.modal-title');
		const $adminModalName = $('#adminModalName');
		const $adminModalEmail = $('#adminModalEmail');
		const $adminModalIsGameAdmin = $('#adminModalIsGameAdmin');
		const $adminModalIsSiteAdmin = $('#adminModalIsSiteAdmin');
		const $adminModalMessage = $('#adminModalMessage');
		const $adminModalFooter = $adminModal.find('.modal-footer');
		const $adminModalDeleteSection = $('#adminModalDeleteSection');
		const $adminModalDeleteBtn = $('#adminModalDeleteBtn');
		const $adminModalConfirmDelete = $('#adminModalConfirmDelete');
		const $adminModalSubmitBtn = $('#adminModalSubmitBtn');
		// Global Variable Card
		const $variableTableBody = $('#variableTable').find('tbody');
		const $addNewVariable = $('#addNewVariable');
		const $variableModal = $('#variableModal');
		const $variableModalForm = $('#variableModalForm');
		const $variableModalTitle = $variableModal.find('.modal-title');
		const $variableModalName = $('#variableModalName');
		const $variableModalType = $('#variableModalType');
		const $variableModalValue = $('#variableModalValue');
		const $variableModalDescription = $('#variableModalDescription');
		const $variableModalMessage = $('#variableModalMessage');
		const $variableModalFooter = $variableModal.find('.modal-footer');
		const $variableModalDeleteSection = $('#variableModalDeleteSection');
		const $variableModalDeleteBtn = $('#variableModalDeleteBtn');
		const $variableModalConfirmDelete = $('#variableModalConfirmDelete');
		const $variableModalSubmitBtn = $('#variableModalSubmitBtn');
		// Danger Zone Card
		const $confirmResetGameData = $('#confirmResetGameData');
		const $resetGameDataBtn = $('#resetGameDataBtn');
		const $resetGameDataMessage = $('#resetGameDataMessage');

		trackStats("LOAD/fun/admin");
		loadGlobals().always(renderGlobalVariableTable);
		loadAdmins().always(renderAdminTable);
		setupDangerZoneHandlers();

		function render() {
			renderAdminTable();
			renderGlobalVariableTable();
			setupDangerZoneHandlers();
		}

		function renderAdminTable() {
			$adminTableBody.empty();
			if (admins.length === 0) {
				$adminTableBody.append("No admins.");
			} else {
				_.each(admins, (admin) => {
					$adminTableBody.append(adminRow(admin));
				});
			}
			setupAdminTableHandlers();
		}

		function setupAdminTableHandlers() {
			let prevName;
			let prevEmail;
			let prevIsGameAdmin;
			let prevIsSiteAdmin;

			// Focus on the first input once the modal is shown
			$adminModal.off('shown.bs.modal').on('shown.bs.modal', (e) => {
				$adminModalFooter.show();
				$adminModalName.focus();
			});

			// Keep the delete button disabled unless the confirm checkbox is checked
			$adminModalConfirmDelete.off().change((e) => {
				enableDeleteAdminButton($adminModalConfirmDelete.is(':checked'));
			});

			// Clear the message as the form changes
			$adminModalName.on('keydown change', clearMessageUnlessEnter);
			$adminModalEmail.on('keydown change', clearMessageUnlessEnter);
			$adminModalIsGameAdmin.on('keydown change', clearMessageUnlessEnter);
			$adminModalIsSiteAdmin.on('keydown change', clearMessageUnlessEnter);
			function clearMessageUnlessEnter(e) {
				if (e.which !== 32) clearMessage($adminModalMessage);
			}

			// Edit variable click handler
			$('.editAdmin').off('click').click((e) => {
				const $btn = $(e.currentTarget);
				const uid = $btn.attr('uid');
				const admin = getAdmin(uid);

				// Setup the modal
				clearMessage($adminModalMessage);
				$adminModalTitle.text("Edit Admin");
				$adminModalEmail.prop('disabled', true);
				$adminModalDeleteSection.show();
				enableDeleteAdminButton(false);
				$adminModalSubmitBtn.text("Save");
				$adminModalSubmitBtn.removeClass('new');

				// Save the starting state
				prevName = admin.name;
				prevEmail = admin.email;
				prevIsGameAdmin = admin.gameAdmin;
				prevIsSiteAdmin = admin.siteAdmin;

				// Set the starting state
				$adminModalForm.attr('uid', uid);
				$adminModalName.val(admin.name);
				$adminModalEmail.val(admin.email);
				$adminModalIsGameAdmin.prop('checked', admin.gameAdmin);
				$adminModalIsSiteAdmin.prop('checked', admin.siteAdmin);
			});

			// Add new admin click handler
			$addNewAdmin.off().click((e) => {
				// Setup the modal
				clearMessage($adminModalMessage);
				$adminModalTitle.text("New Admin");
				$adminModalEmail.prop('disabled', false);
				$adminModalDeleteSection.hide();
				enableDeleteAdminButton(false);
				$adminModalSubmitBtn.text("Create");
				$adminModalSubmitBtn.addClass('new');

				// Set the starting state
				$adminModalForm.attr('uid', null);
				$adminModalName.val(null);
				$adminModalEmail.val(null);
				$adminModalIsGameAdmin.prop('checked', false);
				$adminModalIsSiteAdmin.prop('checked', false);
			});

			// Delete variable click handler
			$adminModalDeleteBtn.off().click((e) => {
				$adminModalFooter.hide();

				const uid = $adminModalForm.attr('uid');
				const admin = getAdmin(uid);

				// Optimistically make changes
				removeAdmin(uid);
				render();

				// Build request data
				const formData = new FormData();
				formData.append('uid', uid);

				// Make the change
				trackStats("DELETE_ADMIN/fun/admin");
				$.ajax({
					type: 'POST',
					url: '/fun/api/admin/delete.php',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($adminModalMessage, resp.message);
					},
					error: (jqXHR) => {
						$adminModalFooter.show();
						errorMessage($adminModalMessage, getErrorMessageFromResponse(jqXHR));

						// Revert changes
						addAdmin(admin);
						render();
					}
				});
			});

			// Add/Edit variable submit handler
			$adminModalForm.off('submit').submit((e) => {
				clearMessage($adminModalMessage);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($adminModalForm[0].checkValidity() === false) {
					return;
				}

				$adminModalFooter.hide();
				const isNew = $adminModalSubmitBtn.hasClass('new');

				const name = $adminModalName.val().trim();
				const email = $adminModalEmail.val().trim();
				const isSiteAdmin = $adminModalIsSiteAdmin.prop('checked');
				const isGameAdmin = $adminModalIsGameAdmin.prop('checked');

				let uid;
				let admin;
				if (isNew) {
					admin = getTempAdmin();
					if (!!getAdminByEmail(email)) {
						errorMessage($adminModalMessage, "Admin already exists with that email.");
						$adminModalFooter.show();
						return;
					}
				} else {
					uid = $adminModalForm.attr('uid');
					admin = getAdmin(uid);
				}

				// Optimistically make changes
				admin.name = name;
				admin.email = email;
				admin.siteAdmin = isSiteAdmin;
				admin.gameAdmin = isGameAdmin;
				if (isNew) {
					addAdmin(admin);
				} else {
					updateAdmin(admin);
				}
				render();

				// Build request data
				const formData = new FormData();
				if (!isNew) formData.append('uid', uid);
				formData.append('name', name);
				formData.append('email', email);
				formData.append('gameAdmin', isGameAdmin);
				formData.append('siteAdmin', isSiteAdmin);

				// Make the change
				infoMessage($adminModalMessage, (isNew ? "Creating admin..." : "Updating admin..."));
				trackStats((isNew ? 'CREATE' : 'UPDATE') + "_ADMIN/fun/admin");
				$.ajax({
					type: 'POST',
					url: (isNew ? "/fun/api/admin/create.php" : "/fun/api/admin/update.php"),
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					statusCode: {
						200: (resp) => {
							if (isNew && $adminModal.data('bs.modal')._isShown) {
								// Reset form to make it easy for multi-create
								$adminModalName.val(null);
								$adminModalEmail.val(null);
								$adminModalIsGameAdmin.prop('checked', false);
								$adminModalIsSiteAdmin.prop('checked', false);
								$adminModalName.focus();
							}
							successMessage($adminModalMessage, resp.message);
							admin.uid = resp.data.uid;
							admin.name = resp.data.name;
							admin.email = resp.data.email;
							admin.gameAdmin = resp.data.gameAdmin;
							admin.siteAdmin = resp.data.siteAdmin;
							render();
						},
						304: () => {
							successMessage($adminModalMessage, "No changes");
						}
					},
					error: (jqXHR) => {
						errorMessage($adminModalMessage, getErrorMessageFromResponse(jqXHR));

						// Revert changes
						if (isNew) {
							removeAdminByEmail(email);
						} else {
							admin.name = prevName;
							admin.email = prevEmail;
							admin.gameAdmin = prevIsGameAdmin;
							admin.siteAdmin = prevIsSiteAdmin;
							updateAdmin(admin);
						}
						render();
					},
					complete: () => {
						$adminModalFooter.show();
					}
				});
			});
		}

		function renderGlobalVariableTable() {
			$variableTableBody.empty();
			if (globals.length === 0) {
				$variableTableBody.append("No global variables.");
			} else {
				_.each(globals, (team) => {
					$variableTableBody.append(variableRow(team));
				});
			}
			setupGlobalVariableHandlers();
		}

		function setupGlobalVariableHandlers() {
			let prevName;
			let prevType;
			let prevValue;
			let prevDescription;

			// Focus on the first input once the modal is shown
			$variableModal.off('shown.bs.modal').on('shown.bs.modal', (e) => {
				$variableModalFooter.show();
				if ($variableModalSubmitBtn.hasClass('new')) {
					$variableModalName.prop('readonly', false);
					$variableModalType.prop('readonly', false);
					$variableModalName.focus();
				} else {
					$variableModalName.prop('readonly', true);
					$variableModalType.prop('readonly', true);
					$variableModalType.focus();
				}
			});

			// Keep the delete button disabled unless the confirm checkbox is checked
			$variableModalConfirmDelete.off().change((e) => {
				enableDeleteGlobalVariableButton($variableModalConfirmDelete.is(':checked'));
			});

			// Clear the message as the form changes
			$variableModalType.on('keydown change', clearMessageUnlessEnter);
			$variableModalValue.on('keydown change', clearMessageUnlessEnter);
			$variableModalDescription.on('keydown change', clearMessageUnlessEnter);
			function clearMessageUnlessEnter(e) {
				if (e.which !== 32) clearMessage($variableModalMessage);
			}

			// Edit variable click handler
			$('.editVariable').off('click').click((e) => {
				const $btn = $(e.currentTarget);
				const name = $btn.attr('name');
				const global = getGlobalByName(name);

				// Setup the modal
				clearMessage($variableModalMessage);
				$variableModalTitle.text("Edit Global Variable");
				$variableModalDeleteSection.show();
				enableDeleteGlobalVariableButton(false);
				$variableModalSubmitBtn.text("Save");
				$variableModalSubmitBtn.removeClass('new');

				// Save the starting state
				prevName = name;
				prevType = global.type;
				prevValue = global.value;
				prevDescription = global.description;

				// Set the starting state
				$variableModalForm.attr('name', name);
				$variableModalName.val(name);
				$variableModalType.val(global.type);
				$variableModalValue.val(global.value);
				$variableModalDescription.val(global.description);
			});

			// Add new variable click handler
			$addNewVariable.off().click((e) => {
				// Setup the modal
				clearMessage($variableModalMessage);
				$variableModalTitle.text("New Global Variable");
				$variableModalDeleteSection.hide();
				enableDeleteGlobalVariableButton(false);
				$variableModalSubmitBtn.text("Create");
				$variableModalSubmitBtn.addClass('new');

				// Set the starting state
				$variableModalForm.attr('name', null);
				$variableModalName.val(null);
				$variableModalType.val('string');
				$variableModalValue.val(null);
				$variableModalDescription.val(null);
			});

			// Delete variable click handler
			$variableModalDeleteBtn.off().click((e) => {
				$variableModalFooter.hide();

				const name = $variableModalForm.attr('name');
				const global = getGlobalByName(name);

				// Optimistically make changes
				removeGlobal(name);
				render();

				// Build request data
				const formData = new FormData();
				formData.append('name', name);

				// Make the change
				trackStats("DELETE_GLOBAL/fun/admin");
				$.ajax({
					type: 'POST',
					url: '/fun/api/admin/globals/delete.php',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($variableModalMessage, resp.message);
					},
					error: (jqXHR) => {
						$variableModalFooter.show();
						errorMessage($variableModalMessage, getErrorMessageFromResponse(jqXHR));

						// Revert changes
						addGlobal(global);
						render();
					}
				});
			});

			// Add/Edit variable submit handler
			$variableModalForm.off('submit').submit((e) => {
				clearMessage($variableModalMessage);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($variableModalForm[0].checkValidity() === false) {
					return;
				}

				$variableModalFooter.hide();
				const isNew = $variableModalSubmitBtn.hasClass('new');

				const type = $variableModalType.val().trim();
				const value = $variableModalValue.val().trim();
				const description = $variableModalDescription.val().trim();

				let name;
				let global;
				if (isNew) {
					global = {};
					name = $variableModalName.val().trim();
					if (!!getGlobalByName(name)) {
						errorMessage($variableModalMessage, "Global variable already exists with that name.");
						return;
					}
				} else {
					name = $variableModalForm.attr('name');
					global = getGlobalByName(name);
				}

				// Optimistically make changes
				global.name = name;
				global.type = type;
				global.value = value;
				global.description = description;
				if (isNew) {
					addGlobal(global);
				} else {
					updateGlobal(global);
				}
				render();

				// Build request data
				const formData = new FormData();
				formData.append('name', name);
				formData.append('type', type);
				formData.append('value', value);
				formData.append('description', description);

				// Make the change
				infoMessage($variableModalMessage, (isNew ? "Creating global variable..." : "Updating global variable..."));
				trackStats((isNew ? 'CREATE' : 'UPDATE') + "_GLOBAL/fun/admin");
				$.ajax({
					type: 'POST',
					url: (isNew ? "/fun/api/admin/globals/create.php" : "/fun/api/admin/globals/update.php"),
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					statusCode: {
						200: (resp) => {
							if (isNew && $variableModal.data('bs.modal')._isShown) {
								// Reset form to make it easy for multi-create
								$variableModalName.val(null);
								$variableModalValue.val(null);
								$variableModalDescription.val(null);
								$variableModalName.focus();
							}
							successMessage($variableModalMessage, resp.message);
							global.name = resp.data.name;
							global.type = resp.data.type;
							global.value = resp.data.value;
							global.description = resp.data.description;
							render();
						},
						304: () => {
							successMessage($variableModalMessage, "No changes");
						}
					},
					error: (jqXHR) => {
						errorMessage($variableModalMessage, getErrorMessageFromResponse(jqXHR));

						// Revert changes
						if (isNew) {
							removeGlobal(name);
						} else {
							global.name = prevName;
							global.type = prevType;
							global.value = prevValue;
							global.description = prevDescription;
							updateGlobal(global);
						}
						render();
					},
					complete: () => {
						$variableModalFooter.show();
					}
				});
			});
		}

		function setupDangerZoneHandlers() {
			$resetGameDataBtn.off().click((e) => {
				clearMessage($resetGameDataMessage);
				if ($confirmResetGameData.val() !== 'RESET') {
					warnMessage($resetGameDataMessage, "You must type 'RESET' in the text box to before clicking the button.");
					return;
				}

				// Build request data
				const formData = new FormData();
				formData.append('precaution', $confirmResetGameData.val());

				// Clear the confirmation text box
				$confirmResetGameData.val("");

				// Make the change
				trackStats("RESET_GAME_DATA/fun/admin");
				infoMessage($resetGameDataMessage, "Resetting game data...");
				$.ajax({
					type: 'POST',
					url: '/fun/api/admin/danger/resetGameData.php',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($resetGameDataMessage, resp.message);
					},
					error: (jqXHR) => {
						errorMessage($resetGameDataMessage, getErrorMessageFromResponse(jqXHR));
					}
				});
			});
		}

		function adminRow(admin) {
			const objArr = [
				{className: 'pl-0', ele: editAdminButton(admin.uid)},
				{className: 'pl-0 small name', ele: "" + admin.name},
				{className: 'pl-0 small email', ele: "" + admin.email},
				{className: 'pl-0 isGameAdmin', ele: booleanIcon(admin.gameAdmin)},
				{className: 'pl-0 isSiteAdmin', ele: booleanIcon(admin.siteAdmin)}
			];
			return tr(objArr);
		}

		function variableRow(global) {
			const objArr = [
				{className: 'pl-0', ele: editVariableButton(global)},
				{className: 'pl-0 name', ele: "" + global.name},
				{className: 'pl-0 type', ele: "" + global.type},
				{className: 'pl-0 value', ele: "" + global.value},
				{className: 'pl-0 description', ele: "" + global.description}
			];
			return tr(objArr, 'small');
		}

		function editAdminButton(uid) {
			const $btn = $($('#editAdminButton').html());
			$btn.attr('uid', uid);
			return $btn;
		}

		function editVariableButton(global) {
			const $btn = $($('#editVariableButton').html());
			$btn.attr('name', global.name);
			return $btn;
		}

		function booleanIcon(value) {
			return value ? $($('#iconTrue').html()) : $($('#iconFalse').html());
		}

		function enableDeleteAdminButton(enabled) {
			$adminModalConfirmDelete.prop('checked', enabled);
			$adminModalDeleteBtn.prop('disabled', !enabled);
		}

		function enableDeleteGlobalVariableButton(enabled) {
			$variableModalConfirmDelete.prop('checked', enabled);
			$variableModalDeleteBtn.prop('disabled', !enabled);
		}
	});
</script>
</body>
