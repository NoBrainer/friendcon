const DATE_FORMAT = 'YYYY-MM-DD HH:mm:ss';
const DATE_FORMAT_DISPLAY = 'MM/DD/YYYY h:mmA';

const REJECTED = 'REJECTED';
const PENDING = 'PENDING';
const APPROVED = 'APPROVED';
const HONORED = 'HONORED';
const WINNER = 'WINNER';

const NONE = 'NONE';

let challenges = [];
let scoreChanges = [];
let teams = [];
let uploads = [];

function addChallenge(challenge) {
	challenges.push(challenge);
	resortChallenges();
}

function addTeam(team) {
	teams.push(team);
}

function dateDisplayFormat(dateTime, emptyString) {
	emptyString = emptyString || NONE;
	return dateTime === null ? emptyString : moment(dateTime).format(DATE_FORMAT_DISPLAY);
}

function dateMillis(dateTime) {
	return dateTime === null ? -1 : moment(dateTime).unix();
}

function datePickerValue(dateTime) {
	return dateTime === null ? null : moment(dateTime);
}

function extractFileFromUrl(url) {
	let file = url.split("file=")[1];
	if (file.indexOf('&v=') > 0) {
		file = file.substr(0, file.indexOf('&v='));
	}
	return file;
}

function getChallenge(challengeIndex) {
	challengeIndex = parseInt(challengeIndex);
	return _.find(challenges, (challenge) => {
		return challenge.challengeIndex === challengeIndex;
	});
}

function getChallengeName(challengeIndex, defaultStr) {
	defaultStr = defaultStr || "CHALLENGE_NOT_FOUND";
	const challenge = getChallenge(challengeIndex) || {name: defaultStr};
	return challenge.name;
}

function getChallengeStartMillis(challengeIndex) {
	const challenge = getChallenge(challengeIndex) || {startTime: null};
	return dateMillis(challenge.startTime);
}

function getDateStringFromPicker($picker) {
	const date = $picker.datetimepicker('date');
	return date === null ? null : date.format(DATE_FORMAT);
}

function getDownloadLinkForUpload(upload) {
	return "/fun/api/uploads/download.php?file=" + upload.file + '&v=' + upload.rotation;
}

function getTeam(teamIndex) {
	teamIndex = parseInt(teamIndex);
	return _.find(teams, (team) => {
		return team.teamIndex === teamIndex;
	}) || null;
}

function getTeamName(teamIndex) {
	const team = getTeam(teamIndex) || {name: "TEAM_NOT_FOUND"};
	return team.name;
}

function getTeamsSortedByScore() {
	return teams.sort((a, b) => {
		if (a.score > b.score) return -1;
		if (a.score < b.score) return +1;
		return 0;
	});
}

function getTempChallenge(name, startTime, endTime) {
	return {
		name: name || "",
		challengeIndex: parseInt(_.uniqueId(-1)),
		startTime: startTime || null,
		endTime: endTime || null
	};
}

function getTempTeam(name) {
	return {
		name: name || "",
		score: 0,
		teamIndex: parseInt(_.uniqueId(-1)),
		updateTime: moment().format(DATE_FORMAT),
		members: []
	};
}

function getUploadByFile(file) {
	return _.find(uploads, (upload) => {
		return upload.file === file;
	}) || null;
}

function getUploadsByChallenge() {
	// Build a map with uploads split into challenges
	let uploadsByChallenge = _.reduce(uploads, (map, upload, i) => {
		const challengeIndex = upload.challengeIndex;
		if (!map[challengeIndex]) map[challengeIndex] = [];
		map[challengeIndex].push(upload);
		return map;
	}, {});

	// Sort the uploads within each challenge
	_.each(uploadsByChallenge, (uploads) => {
		uploads.sort(sortUploads);
	});

	return uploadsByChallenge;
}

function hasChallengeEnded(challenge) {
	if (challenge.endTime === null) return false;
	return new Date(challenge.endTime) <= new Date();
}

function hasChallengeStarted(challenge) {
	if (challenge.startTime === null) return true;
	return new Date(challenge.startTime) <= new Date();
}

function isChallengePublished(challengeIndex) {
	const challenge = getChallenge(challengeIndex) || {published: false};
	return !!challenge.published;
}

function isUploadApproved(upload) {
	return !isUploadRejected(upload) && !isUploadPending(upload);
}

function isUploadHonored(upload) {
	return upload.state === HONORED;
}

function isUploadPending(upload) {
	return upload.state === PENDING;
}

function isUploadRejected(upload) {
	return upload.state === REJECTED;
}

function isUploadWinner(upload) {
	return upload.state === WINNER;
}

function loadChallenges(opts) {
	const defaultOpts = {asAdmin: false};
	opts = _.extend(defaultOpts, opts);
	return $.ajax({
		type: 'GET',
		url: "/fun/api/challenges/get.php" + (opts.asAdmin ? "?all" : ""),
		success: (resp) => {
			challenges = resp.data.sort(sortChallenges);
		},
		error: (jqXHR) => {
			const resp = jqXHR.responseJSON;
			console.log(resp.error);
			alert(resp.error);
		}
	});
}

function loadData(opts) {
	const defaultOpts = {asAdmin: false, withScoreChanges: false};
	opts = _.extend(defaultOpts, opts);
	const challengeDeferred = loadChallenges(opts);
	const teamDeferred = loadTeams(opts);
	const uploadDeferred = loadUploads(opts);
	let combined = $.when(challengeDeferred, teamDeferred, uploadDeferred);
	if (opts.withScoreChanges) {
		const scoreChangesDeferred = loadScoreChanges(opts);
		combined = $.when(challengeDeferred, scoreChangesDeferred, teamDeferred, uploadDeferred);
	}
	return combined.fail(() => {
		const message = "Failed to load data";
		console.log(message);
		alert(message);
	});
}

function loadScoreChanges(opts) {
	const defaultOpts = {};
	opts = _.extend(defaultOpts, opts);
	return $.ajax({
		type: 'GET',
		url: "/fun/api/score/getChangeLog.php",
		success: (resp) => {
			scoreChanges = resp.data.sort(sortScoreChanges);
		},
		error: (jqXHR) => {
			const resp = jqXHR.responseJSON;
			console.log(resp.error);
			alert(resp.error);
		}
	});
}

function loadTeams(opts) {
	const defaultOpts = {};
	opts = _.extend(defaultOpts, opts);
	return $.ajax({
		type: 'GET',
		url: "/fun/api/teams/get.php",
		success: (resp) => {
			teams = resp.data.sort(sortTeams);
		},
		error: (jqXHR) => {
			const resp = jqXHR.responseJSON;
			console.log(resp.error);
			alert(resp.error);
		}
	});
}

function loadUploads(opts) {
	const defaultOpts = {asAdmin: false};
	opts = _.extend(defaultOpts, opts);
	return $.ajax({
		type: 'GET',
		url: "/fun/api/uploads/get.php" + (opts.asAdmin ? "?all" : ""),
		success: (resp) => {
			uploads = resp.data.sort(sortUploads);
		},
		error: (jqXHR) => {
			const resp = jqXHR.responseJSON;
			console.log(resp.error);
			alert(resp.error);
		}
	});
}

function removeChallenge(challengeIndex) {
	challengeIndex = parseInt(challengeIndex);
	challenges = _.reject(challenges, (challenge) => {
		return challenge.challengeIndex === challengeIndex;
	});
}

function removeTeam(teamIndex) {
	teamIndex = parseInt(teamIndex);
	teams = _.reject(teams, (team) => {
		return team.teamIndex === teamIndex;
	});
}

function resortChallenges() {
	challenges = challenges.sort(sortChallenges);
}

function resortScoreChanges() {
	scoreChanges = scoreChanges.sort(sortScoreChanges);
}

function resortTeams() {
	teams = teams.sort(sortTeams);
}

function resortUploads() {
	uploads = uploads.sort(sortUploads);
}

function sortChallenges(a, b) {
	// Primary sort: Start time (null first, newest last)
	const startA = dateMillis(a.startTime);
	const startB = dateMillis(b.startTime);
	if (startA < startB) return -1;
	if (startA > startB) return 1;

	// Secondary sort: Name (alphabetical)
	return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
}

function sortScoreChanges(a, b) {
	// Primary sort: Update Time (newest first)
	const updateA = dateMillis(a.updateTime);
	const updateB = dateMillis(b.updateTime);
	if (updateA < updateB) return +1;
	if (updateA > updateB) return -1;
	return 0;
}

function sortTeams(a, b) {
	// Primary sort: Name (alphabetical)
	return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
}

function sortUploads(a, b) {
	// Primary sort: Challenge Start Time (null first, newest last)
	const startA = getChallengeStartMillis(a.challengeIndex);
	const startB = getChallengeStartMillis(b.challengeIndex);
	if (startA < startB) return -1;
	if (startA > startB) return +1;

	// Secondary sort: State (WINNER < HONORED < APPROVED < PENDING < REJECTED)
	if (a.state !== b.state) {
		if (isUploadWinner(a)) return -1;
		if (isUploadWinner(b)) return +1;
		if (isUploadHonored(a)) return -1;
		if (isUploadHonored(b)) return +1;
		if (isUploadApproved(a)) return -1;
		if (isUploadApproved(b)) return +1;
		if (isUploadPending(a)) return -1;
		if (isUploadPending(b)) return +1;
	}

	// Tertiary sort: Upload Time (null first, newest last)
	const uploadTimeA = dateMillis(a.uploadTime);
	const uploadTimeB = dateMillis(b.uploadTime);
	if (uploadTimeA < uploadTimeB) return -1;
	if (uploadTimeA > uploadTimeB) return 1;
	return 0;
}
