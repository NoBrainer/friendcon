<?php
$pageTitle = "Login Required";
$requireAdmin = false;
$forwardAdmin = true;
?>
<?php include('head.php'); ?>
<body>
<?php include('nav.php'); ?>

<!-- Content -->
<div class="container-fluid" id="content">
	<div class="container-fluid card mb-3 maxWidth-sm">
		<div class="card-body">
			<h5 class="card-title"><?php echo $pageTitle; ?></h5>
			<p>Login is required to access these features.</p>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
	$(document).ready(() => {
		trackStats("LOAD/fun/admin/nope");
	});
</script>
</body>
