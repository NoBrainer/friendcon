<?php
$pageTitle = "Manage Scores";
$navTab = "ADMIN";
$subNavPage = "SCORES";
$requireAdmin = true;
?>
<?php include('../head.php'); ?>
<body>
<?php include('../nav.php'); ?>

<!-- Content -->
<div id="content" class="container-fluid">
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title"><?php echo $pageTitle; ?></h5>
			<p>How does this work?</p>
			<ul>
				<li>Modify team scores here.</li>
				<li>When in doubt, check the change logs below.</li>
			</ul>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table" id="scoreTable">
					<thead>
						<th class="border-0">Team</th>
						<th class="border-0">Score</th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title">Modify Score</h5>
			<form id="modifyForm">
				<div class="form-group">
					<div class="input-group" id="pickTeamWrapper">
						<div class="input-group-prepend">
							<label class="input-group-text" for="pickTeamDropdown">Team:</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group" id="pickChallengeWrapper">
						<div class="input-group-prepend">
							<label class="input-group-text" for="pickChallengeDropdown">Challenge:</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<label class="input-group-text" for="scoreChange">Change:</label>
						</div>
						<input type="number" class="form-control" id="scoreChange" required>
					</div>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-outline-secondary" aria-label="Modify score">Submit</button>
				</div>
				<div class="form-group">
					<div id="modifyMessage"></div>
				</div>
			</form>
		</div>
	</div>
	<div class="container-fluid card mb-3 maxWidth-md">
		<div class="card-body">
			<h5 class="card-title">Change Log</h5>
			<button type="button" class="btn btn-outline-secondary" id="showChangeLog">Show</button>
			<div id="changeLogEntries"></div>
		</div>
	</div>
</div>

<!-- HTML Templates -->
<div class="templates" style="display:none">
	<div id="changeLogTableScaffold">
		<div class="table-responsive">
			<table class="table">
				<thead>
					<tr>
						<th class="border-0">Update</th>
						<th class="border-0">Change</th>
						<th class="border-0">Team</th>
						<th class="border-0">Challenge</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		const $scoreTable = $('#scoreTable');
		const $modifyForm = $('#modifyForm');
		const $pickTeamDropdownWrapper = $('#pickTeamWrapper');
		const $pickChallengeDropdownWrapper = $('#pickChallengeWrapper');
		const $scoreChange = $('#scoreChange');
		const $modifyMessage = $('#modifyMessage');
		const $showChangeLogBtn = $('#showChangeLog');
		const $changeLogEntries = $('#changeLogEntries');

		const pickTeamDropdownId = 'pickTeamDropdown';
		const pickChallengeDropdownId = 'pickChallengeDropdown';
		let lastTeamDropdownValue = -1;
		let lastChallengeDropdownValue = -1;
		let showChangeLog = false;

		trackStats("LOAD/fun/game/admin/scores");
		loadData({asAdmin: true, withScoreChanges: true}).done(render);

		function render() {
			renderScoreTable();
			renderTeamsDropdown();
			renderChallengeDropdown();
			if (showChangeLog) renderChangeLog();
			setupHandlers();
		}

		function renderScoreTable() {
			$scoreTable.find('tbody').empty();
			if (teams.length === 0) {
				$scoreTable.append("Teams need to be setup via the Teams admin page.");
			} else {
				_.each(getTeamsSortedByScore(), (team) => {
					$scoreTable.append(scoreRow(team));
				});
			}
		}

		function renderTeamsDropdown() {
			const objArr = [{text: "Pick Team", value: "-1", selected: true}];
			_.each(teams, (team) => {
				objArr.push({text: team.name, value: team.teamIndex});
			});
			$pickTeamDropdownWrapper.find('.custom-select').remove();
			$pickTeamDropdownWrapper.append(select(objArr, pickTeamDropdownId));

			// Make sure the selection persists through re-rendering
			const $dropdown = $('#' + pickTeamDropdownId);
			$dropdown.val(lastTeamDropdownValue);
			$dropdown.off('change').change((e) => {
				lastTeamDropdownValue = $(e.currentTarget).val();
				clearMessage($modifyMessage);
			});
		}

		function renderChallengeDropdown() {
			const objArr = [{text: "Pick Challenge (optional)", value: "-1", selected: true}];
			_.each(challenges, (challenge) => {
				objArr.push({text: challenge.name, value: challenge.challengeIndex});
			});
			$pickChallengeDropdownWrapper.find('.custom-select').remove();
			$pickChallengeDropdownWrapper.append(select(objArr, pickChallengeDropdownId));

			// Make sure the selection persists through re-rendering
			const $dropdown = $('#' + pickChallengeDropdownId);
			$dropdown.val(lastChallengeDropdownValue);
			$dropdown.off('change').change((e) => {
				lastChallengeDropdownValue = $(e.currentTarget).val();
				clearMessage($modifyMessage);
			});
		}

		function setupHandlers() {
			// Modify form submit handler
			$modifyForm.off('submit').submit((e) => {
				clearMessage($modifyMessage);
				e.preventDefault();
				e.stopPropagation();

				// HTML5 form validation
				if ($modifyForm[0].checkValidity() === false) {
					return;
				}

				const teamIndex = parseInt($('#' + pickTeamDropdownId).val());
				const challengeIndex = parseInt($('#' + pickChallengeDropdownId).val());
				const delta = parseInt($scoreChange.val());

				if (!delta || isNaN(delta)) {
					errorMessage($modifyMessage, "Invalid value for change");
					return;
				}

				// Build request data
				const formData = new FormData();
				formData.append('delta', delta);
				formData.append('teamIndex', teamIndex);
				if (challengeIndex !== -1) formData.append('challengeIndex', challengeIndex);

				// Make the change
				trackStats("MODIFY_SCORE/fun/game/admin/scores");
				$.ajax({
					type: 'POST',
					url: "/fun/api/score/update.php",
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: (resp) => {
						successMessage($modifyMessage, resp.message);
						teams = resp.data.teams;
						scoreChanges = resp.data.scoreChanges;
						resortTeams();
						resortScoreChanges();
						render();
					},
					error: (jqXHR) => {
						errorMessage($modifyMessage, getErrorMessageFromResponse(jqXHR));
					}
				});
			});

			// Show change log click handler
			$showChangeLogBtn.off('click').on('click', (e) => {
				showChangeLog = true;
				$showChangeLogBtn.hide();
				renderChangeLog();
			});
		}

		function renderChangeLog() {
			$changeLogEntries.empty().show();
			if (scoreChanges.length === 0) {
				$changeLogEntries.text("Empty change log.");
			} else {
				$changeLogEntries.html(changeLogTableScaffold());
				const $table = $changeLogEntries.find('.table');
				_.each(scoreChanges, (scoreChange) => {
					$table.append(changeLogRow(scoreChange));
				});
			}
		}

		function changeLogTableScaffold() {
			return $($('#changeLogTableScaffold').html());
		}

		function changeLogRow(scoreChange) {
			const objArr = [
				{className: 'updateTime', ele: dateDisplayFormat(scoreChange.updateTime)},
				{className: 'delta', ele: scoreChange.delta > 0 ? "+" + scoreChange.delta : scoreChange.delta},
				{className: 'team', ele: getTeamName(scoreChange.teamIndex)},
				{className: 'challenge', ele: getChallengeName(scoreChange.challengeIndex, "-")}
			];
			return tr(objArr);
		}

		function scoreRow(team) {
			const objArr = [
				{className: 'team', ele: getTeamName(team.teamIndex)},
				{className: 'score', ele: "" + team.score}
			];
			return tr(objArr);
		}
	});
</script>
</body>
</html>
