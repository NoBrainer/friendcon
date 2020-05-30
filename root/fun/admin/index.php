<?php
$pageTitle = "Site Admin";
$requireAdmin = true;
?>
<?php include('head.php'); ?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-fluid card mb-3 maxWidth-lg">
		<div class="card-body">
			<h5 class="card-title">Global Variables</h5>
			<div class="table-responsive">
				<table class="table" id="variableTable">
					<thead>
						<th class="pl-0 border-0">
							<a class="fa fa-plus-square" id="addNewVariable" data-toggle="modal" data-target="#variableModal" aria-label="Create New Variable"></a>
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
</div>

<!-- Modal -->
<div id="variableModal" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="variableModalForm">
				<div class="modal-header">
					<h5 class="modal-title"></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="variableModalName">Name:</label>
						</span>
						<input class="form-control" type="text" maxlength="32" id="variableModalName" placeholder="Name" required>
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
						<input class="form-control" type="text" maxlength="32" id="variableModalValue" placeholder="Value" required>
					</div>
					<div class="input-group mb-3">
						<span class="input-group-prepend">
							<label class="input-group-text" for="variableModalDescription">Description:</label>
						</span>
						<input class="form-control" type="text" maxlength="64" id="variableModalDescription" placeholder="Description (optional)">
					</div>
					<div class="form-group">
						<div id="variableModalMessage"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="input-group mr-auto w-auto" id="variableModalDeleteSection">
						<button type="button" class="btn btn-outline-danger form-control" id="variableModalDeleteBtn" disabled>Delete</button>
						<span class="input-group-append">
							<label class="sr-only" for="variableModalConfirmDelete">Confirm Delete</label>
							<div class="input-group-text">
								<input type="checkbox" id="variableModalConfirmDelete" aria-label="Checkbox for confirming variable delete">
							</div>
						</span>
					</div>
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-outline-primary" id="variableModalSubmitBtn"></button>
				</div>
			</form>
		</div>
	</div>
</div>

<!--  HTML Templates -->
<div class="templates" style="display:none">
	<div id="editVariableButton">
		<a class="fa fa-edit editVariable" data-toggle="modal" data-target="#variableModal" aria-label="Edit Global Variable"></a>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		const $variableTableBody = $('#variableTable').find('tbody');
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

		trackStats("LOAD/fun/admin");
		loadGlobals().always(render);

		function render() {
			renderVariableTable();
			setupHandlers();
		}

		function renderVariableTable() {
			$variableTableBody.empty();
			if (globals.length === 0) {
				$variableTableBody.append("No global variables.");
			} else {
				_.each(globals, (team) => {
					$variableTableBody.append(variableRow(team));
				});
			}
		}

		function setupHandlers() {
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
				enableDeleteButton($variableModalConfirmDelete.is(':checked'));
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
				enableDeleteButton(false);
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
			$('#addNewVariable').off().click((e) => {
				// Setup the modal
				clearMessage($variableModalMessage);
				$variableModalTitle.text("New Global Variable");
				$variableModalDeleteSection.hide();
				enableDeleteButton(false);
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

		function editVariableButton(global) {
			const $btn = $($('#editVariableButton').html());
			$btn.attr('name', global.name);
			return $btn;
		}

		function enableDeleteButton(enabled) {
			$variableModalConfirmDelete.prop('checked', enabled);
			$variableModalDeleteBtn.prop('disabled', !enabled);
		}
	});
</script>
</body>
