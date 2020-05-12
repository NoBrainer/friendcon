<?php
$pageTitle = "Manage Uploads";
$navTab = "ADMIN";
$subNavPage = "UPLOADS";
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
				<li>Approve pending uploads then mark them with the winning trophy or gold star.</li>
				<li>Rotate any images as necessary.</li>
				<li>
					<span>Use the "Publish to Album" checkbox for each challenge card to publish its approved uploads to the</span>
					<a href="/members/game/album">album</a><span>.</span>
				</li>
				<li>Award teams with points in the <a href="/members/game/admin/scores">Manage Scores</a> page.</li>
			</ul>
		</div>
	</div>
	<div class="container-lg card mb-3 maxWidth-lg">
		<div class="card-body">
			<div class="form-check float-right">
				<label class="form-check-label">
					<input class="form-check-input" type="checkbox" id="showRejected">
					<span>Show Rejected</span>
				</label>
			</div>
			<h5 class="card-title">Pending Uploads</h5>
			<div id="pendingWrapper"></div>
		</div>
	</div>
	<div id="challengeWrapper"></div>
</div>

<!-- HTML Templates -->
<div class="templates" style="display:none">
	<div id="actionBarForPending">
		<div class="container-sm mt-3 maxWidth-sm actionBar">
			<div class="row">
				<h5 class="col text-center title"></h5>
			</div>
			<div class="row">
				<p class="col text-center">
					<span>By:</span>
					<span class="author"></span>
				</p>
			</div>
			<div class="row actions">
				<a class="col-4 text-center action reject">
					<span class="fa fa-window-close"></span>
				</a>
				<a class="col-4 text-center action approve">
					<span class="fa fa-check-square"></span>
				</a>
				<a class="col-4 text-center action rotate">
					<span class="fa fa-undo fa-flip-horizontal"></span>
				</a>
			</div>
		</div>
	</div>
	<div id="actionBarForRanking">
		<div class="container-sm mt-3 maxWidth-sm actionBar">
			<div class="row">
				<h5 class="col text-center author"></h5>
			</div>
			<div class="row actions">
				<a class="col-3 text-center action reject">
					<span class="fa fa-window-close"></span>
				</a>
				<a class="col-3 text-center action winner">
					<span class="fa fa-trophy position-relative">
						<span class="position-absolute number">1</span>
					</span>
				</a>
				<a class="col-3 text-center action honor">
					<span class="fa fa-star"></span>
				</a>
				<a class="col-3 text-center action rotate">
					<span class="fa fa-undo fa-flip-horizontal"></span>
				</a>
			</div>
		</div>
	</div>
	<div id="carousel">
		<div id="_CAROUSEL_ID" class="carousel slide" data-interval="false">
			<!-- Indicators -->
			<ol class="carousel-indicators"></ol>

			<!-- Wrapper for slides -->
			<div class="carousel-inner"></div>

			<!-- Controls -->
			<a class="carousel-control-prev" href="#_CAROUSEL_ID" role="button" data-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next" href="#_CAROUSEL_ID" role="button" data-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a>
		</div>
	</div>
	<div id="carouselIndicator">
		<li data-target="#_CAROUSEL_ID" data-slide-to="_INDEX"></li>
	</div>
	<div id="carouselSlide">
		<div class="carousel-item">
			<img class="w-100">
		</div>
	</div>
	<div id="challengeCard">
		<div class="container-lg card mb-3 maxWidth-lg">
			<div class="card-body">
				<div class="form-check float-right">
					<label class="form-check-label">
						<input class="form-check-input publishToAlbum" type="checkbox">
						<span>Publish to Album</span>
					</label>
				</div>
				<h5 class="card-title challengeTitle"></h5>
				<div class="challengeContent"></div>
			</div>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	const captchaSiteV3Key = "<?php echo CAPTCHA_SITE_V3_KEY; ?>";

	$(document).ready(() => {
		const $pendingWrapper = $('#pendingWrapper');
		const $challengeWrapper = $('#challengeWrapper');
		const $showRejectedCheckbox = $('#showRejected');
		const pendingCarouselId = 'pendingCarousel';
		const pendingActionBarId = 'pendingActionBar';

		trackStats("LOAD/members/game/admin/uploads");
		loadData({asAdmin: true}).done(render);

		function render() {
			renderPendingUploads();
			renderChallengeCards();
		}

		function renderPendingUploads() {
			populatePendingUploads();
			setupPendingUploadsHandlers();
		}

		function populatePendingUploads() {
			const uploadsToRender = _.filter(uploads, (upload) => {
				const showRejected = $showRejectedCheckbox.is(':checked');
				return isUploadPending(upload) || (showRejected && isUploadRejected(upload));
			});
			const slides = buildSlides(uploadsToRender);
			if (slides.length === 0) {
				$pendingWrapper.text("No pending uploads!");
			} else {
				$pendingWrapper.html(carousel(pendingCarouselId, slides, {actionBar: '#' + pendingActionBarId}));
				$pendingWrapper.append(actionBarForPending(pendingActionBarId));
			}
		}

		function setupPendingUploadsHandlers() {
			$showRejectedCheckbox.off().on('change', renderPendingUploads);
			setupCarouselHandlers(pendingCarouselId, render);
		}

		function renderChallengeCards() {
			populateChallengeCards();
			setupChallengeCardsHandlers();
		}

		function populateChallengeCards() {
			// Render each challenge card
			const uploadsByChallenge = getUploadsByChallenge();
			$challengeWrapper.empty();
			_.each(uploadsByChallenge, (uploads, challengeIndex) => {
				$challengeWrapper.append(challengeCard(uploads, challengeIndex));
			});
		}

		function setupChallengeCardsHandlers() {
			_.each($challengeWrapper.find('.carousel'), (carousel) => {
				const carouselId = $(carousel).prop('id');
				setupCarouselHandlers(carouselId, renderChallengeCards);
			});
			setupPublishHandlers();
		}

		function setupCarouselHandlers(carouselId, renderCallback) {
			const $carousel = $('#' + carouselId);
			if ($carousel.length === 0) return;
			const $actionBar = $($carousel.attr('actionBar'));
			const $actionTitle = $actionBar.find('.title');
			const $actionAuthor = $actionBar.find('.author');
			const $actions = $actionBar.find('.action');
			const $rejectAction = $actionBar.find('.reject');
			const $approveAction = $actionBar.find('.approve');
			const $honorAction = $actionBar.find('.honor');
			const $winnerAction = $actionBar.find('.winner');
			const $rotateAction = $actionBar.find('.rotate');
			let isRotationLocked = false;

			// Update the button metadata on each transition
			$carousel.off().on('slide.bs.carousel', onSlideTransition);

			// Trigger first transition to enable mobile swiping
			$carousel.carousel(0);

			// Setup action bar for first slide
			onSlideTransition({relatedTarget: $carousel.find('.carousel-item.active')});

			$rejectAction.off().click((e) => {
				const $btn = $(e.currentTarget);
				$btn.toggleClass('active');
				const file = $btn.attr('file');
				const isRejected = $btn.hasClass('active');
				const state = isRejected ? REJECTED : PENDING;
				setUploadState(file, state, renderCallback);
			});
			$approveAction.off().click((e) => {
				const $btn = $(e.currentTarget);
				$btn.toggleClass('active');
				const file = $btn.attr('file');
				const isApproved = $btn.hasClass('active');
				const state = isApproved ? APPROVED : PENDING;
				setUploadState(file, state, renderCallback);
			});
			$honorAction.off().click((e) => {
				const $btn = $(e.currentTarget);
				$btn.toggleClass('active');
				const file = $btn.attr('file');
				const isHonorableMention = $btn.hasClass('active');
				const state = isHonorableMention ? HONORED : APPROVED;
				setUploadState(file, state, renderCallback);
			});
			$winnerAction.off().click((e) => {
				const $btn = $(e.currentTarget);
				$btn.toggleClass('active');
				const file = $btn.attr('file');
				const isWinner = $btn.hasClass('active');
				const state = isWinner ? WINNER : APPROVED;
				setUploadState(file, state, renderCallback);
			});
			$rotateAction.off().click((e) => {
				// Prevent rotating more than once every two seconds
				if (isRotationLocked) return false;
				isRotationLocked = true;
				setTimeout(() => isRotationLocked = false, 2000);

				const $btn = $(e.currentTarget);
				const file = $btn.attr('file');
				const upload = getUploadByFile(file);
				const $img = $carousel.find('.carousel-item.active img');
				rotateImage(upload, $img);
			});

			function onSlideTransition(e) {
				const $newSlide = $(e.relatedTarget);

				// Update each button's reference to the current file
				const file = $newSlide.attr('file');
				$actions.attr('file', file);

				// Update the action bar state
				const upload = getUploadByFile(file);
				$actionTitle.text(getChallengeDescription(upload.challengeIndex));
				$actionAuthor.text(getTeamName(upload.teamIndex));
				$rejectAction.toggleClass('active', isUploadRejected(upload));
				$approveAction.toggleClass('active', isUploadApproved(upload));
				$honorAction.toggleClass('active', isUploadHonored(upload));
				$winnerAction.toggleClass('active', isUploadWinner(upload));
			}
		}

		function rotateImage(upload, $img) {
			// Build request data
			const formData = new FormData();
			formData.append('file', upload.file);

			trackStats("ROTATE_UPLOAD/members/game/admin/uploads");
			$.ajax({
				type: 'POST',
				url: "/members/api-v2/uploads/rotate.php",
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				success: (resp) => {
					console.log(resp.message);

					// Get the updated uploads
					uploads = resp.uploads;
					resortUploads();

					// Update the image
					const updatedUpload = getUploadByFile(upload.file);
					$img.attr('src', getDownloadLinkForUpload(updatedUpload));
				},
				error: (jqXHR) => {
					const resp = jqXHR.responseJSON;
					console.log(resp.error);
					alert(resp.error);
				}
			});
		}

		function setupPublishHandlers() {
			$challengeWrapper.find('.publishToAlbum').off().change((e) => {
				const $checkbox = $(e.currentTarget);
				const isPublished = $checkbox.is(':checked');
				const challengeIndex = $checkbox.attr('challengeIndex');

				const challenge = getChallenge(challengeIndex);
				const wasPublished = challenge.published;

				// Optimistically make changes
				challenge.published = isPublished;

				// Build request data
				const formData = new FormData();
				formData.append('challengeIndex', challengeIndex);
				formData.append('published', isPublished);

				// Make the change
				trackStats((isPublished ? '' : 'UN') + "PUBLISH/members/game/admin/uploads");
				$.ajax({
					type: 'POST',
					url: "/members/api-v2/challenges/publish.php",
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					statusCode: {
						200: (resp) => {
							console.log(resp.message);
						},
						304: (resp) => {
							console.log("No change to published state");
						}
					},
					error: (jqXHR) => {
						const resp = jqXHR.responseJSON;
						console.log(resp.error);
						alert(resp.error);

						// Revert changes
						challenge.published = wasPublished;
						$checkbox.prop('checked', wasPublished);
					}
				});
			});
		}

		function challengeCard(uploads, challengeIndex) {
			const $card = $($('#challengeCard').html());
			const uploadsToRender = _.filter(uploads, isUploadApproved);
			const slides = buildSlides(uploadsToRender);
			const actionBarId = 'challengeActionBar-' + challengeIndex;
			const $publishCheckbox = $card.find('.publishToAlbum');
			const $challengeContent = $card.find('.challengeContent');
			$card.find('.challengeTitle').text(getChallengeDescription(challengeIndex));
			$publishCheckbox.attr('challengeIndex', challengeIndex);
			$publishCheckbox.prop('checked', isChallengePublished(challengeIndex));
			if (slides.length === 0) {
				$challengeContent.text("No approved uploads.");
			} else {
				$challengeContent.html(carousel("challenge-" + challengeIndex, slides, {actionBar: '#' + actionBarId}));
				$challengeContent.append(actionBarForRanking(actionBarId));
			}
			return $card;
		}

		function setUploadState(file, state, renderCallback) {
			const upload = getUploadByFile(file);
			const prevState = upload.state;

			// Optimistically make changes
			upload.state = state;
			renderCallback();

			// Build request data
			const formData = new FormData();
			formData.append('file', upload.file);
			formData.append('state', upload.state);

			trackStats(state + "_UPLOAD/members/game/admin/uploads");
			return $.ajax({
				type: 'POST',
				url: "/members/api-v2/uploads/update.php",
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				success: (resp) => {
					console.log(resp.message);
				},
				error: (jqXHR) => {
					const resp = jqXHR.responseJSON;
					console.log(resp.error);
					alert(resp.error);

					// Revert changes
					upload.state = prevState;
					renderCallback();
				}
			});
		}

		function buildSlides(uploads) {
			let slides = [];
			_.each(uploads, (upload) => {
				const slide = {
					file: upload.file,
					isActive: (slides.length === 0),
					url: getDownloadLinkForUpload(upload)
				};
				slides.push(slide);
			});
			return slides;
		}

		function actionBarForPending(id) {
			const $actionBar = $($('#actionBarForPending').html());
			$actionBar.prop('id', id);
			return $actionBar;
		}

		function actionBarForRanking(id) {
			const $actionBar = $($('#actionBarForRanking').html());
			$actionBar.prop('id', id);
			return $actionBar;
		}

		function carousel(id, slides, attrs) {
			const $carousel = $($('#carousel').html());
			_.each(attrs, (val, key) => {
				$carousel.attr(key, val);
			});
			$carousel.prop('id', id);
			$carousel.find('.carousel-control-prev, .carousel-control-next').attr('href', '#' + id);
			const $slidesWrapper = $carousel.find('.carousel-inner');
			const $indicators = $carousel.find('.carousel-indicators');
			_.each(slides, (slide, i) => {
				$slidesWrapper.append(carouselSlide(id, slide.url, slide.isActive, {file: slide.file}));
				$indicators.append(carouselIndicator(id, i, slide.isActive));
			});
			return $carousel;
		}

		function carouselIndicator(id, index, isActive) {
			const $indicator = $($('#carouselIndicator').html());
			$indicator.attr('data-target', '#' + id);
			$indicator.attr('data-slide-to', index);
			if (!!isActive) $indicator.addClass('active');
			return $indicator;
		}

		function carouselSlide(id, url, isActive, attrs) {
			const $slide = $($('#carouselSlide').html());
			_.each(attrs, (val, key) => {
				$slide.attr(key, val);
			});
			$slide.find('img').attr('src', url);
			if (!!isActive) $slide.addClass('active');
			return $slide;
		}
	});
</script>
</body>
</html>
