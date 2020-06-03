<?php
$pageTitle = "Listserv";
$navTab = "SHOW";
$requireAdmin = true;
?>
<?php include('head.php'); ?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div class="container-fluid" id="content">
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<button class="btn btn-outline-primary btn-sm float-right mb-1" type="button" id="copyEmails">
				<span class="fa fa-clipboard"></span>
				<span>Copy</span>
			</button>
			<h5 class="card-title">FriendCon Listserv</h5>
			<label class="sr-only" for="emailList">Email List</label>
			<textarea class="form-control form-control-sm" id="emailList" rows="5" readonly></textarea>
		</div>
	</div>

	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<button class="btn btn-outline-primary btn-sm float-right mb-1" type="button" id="copyFooter">
				<span class="fa fa-clipboard"></span>
				<span>Copy</span>
			</button>
			<h5 class="card-title">Email Footer Template</h5>
			<label class="sr-only" for="footerTemplate">Email Footer Template</label>
			<textarea class="form-control form-control-sm" id="footerTemplate" rows="3" readonly></textarea>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		const $copyEmails = $('#copyEmails');
		const $emailList = $('#emailList');
		const $copyFooter = $('#copyFooter');
		const $footerTemplate = $('#footerTemplate');

		trackStats("LOAD/fun/listserv/show");
		render();

		function render() {
			renderEmailList();
			renderUnsubscribeTemplate();
			setupHandlers();
		}

		function renderEmailList() {
			$.ajax({
				type: 'GET',
				url: '/fun/api/listserv/get.php',
				success: (resp) => {
					$emailList.text(resp.data);
				},
				error: (jqXHR) => {
					$emailList.text("Error loading listserv.");
					console.log(jqXHR);
				}
			});
		}

		function renderUnsubscribeTemplate() {
			$footerTemplate.html("Don't want these emails? Unsubscribe: <?php echo $unsubscribeUrl; ?>");
		}

		function setupHandlers() {
			// Select all text on focus for textareas
			$('textarea').off('focus').focus((e) => {
				$(e.currentTarget).select();
			});

			// Copy emails to clipboard button
			$copyEmails.off('click').click((e) => {
				$emailList.select();
				document.execCommand('copy');
			});

			// Copy footer to clipboard button
			$copyFooter.off('click').click((e) => {
				$footerTemplate.select();
				document.execCommand('copy');
			});
		}
	});
</script>
</body>
