<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/constants.php');
include('api-v2/internal/functions.php');
include('api-v2/internal/initDB.php');
include('api-v2/internal/checkAdmin.php');
include('api-v2/internal/checkAppState.php');

// Short-circuit forwarding
if (forwardHttps() || forwardIndexIfLoggedOut()) {
	exit;
}

if (!$isSuperAdmin) {
	http_response_code(HTTP['FORBIDDEN']);
	return;
}

// Get the user data
$query = "SELECT * FROM users WHERE uid = ?";
$result = executeSqlForResult($mysqli, $query, 'i', $userSession);
$userRow = $result->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FriendCon Super Admin</title>
	<link rel="stylesheet" media="screen" href="/members/lib/bootstrap-old/css/bootstrap-3.3.4.min.css">
	<link rel="stylesheet" media="screen" href="/members/lib/bootstrap-old/css/bootstrap-theme-3.3.5.min.css">
	<link rel="stylesheet" media="screen" href="/members/lib/fontawesome/css/all.min.css">
	<link rel="stylesheet" media="screen" href="/members/lib/datatables/datatables.min.css">
	<link rel="stylesheet" media="screen" href="/members/css/old.css">
	<link rel="icon" href="/wp-content/uploads/2019/02/cropped-fc-32x32.png">
</head>

<body class="admin-check-in">
<?php include('header.php'); ?>
<br/>
<br/>
<br/>
<br/>
<div class="container content-card wide">
	<span>Admin Navigation:</span>
	<div class="btn-group" role="group">
		<a class="btn btn-default" href="/members/adminCheckIn.php">Check-In</a>
		<a class="btn btn-default" href="/members/adminTeamSort.php">Team Sorting</a>
		<a class="btn btn-default" href="/members/adminEmailList.php">Email List</a>
	</div>
	<?php if ($isSuperAdmin) { ?>
		<div class="btn-group" role="group">
			<a class="btn btn-default" href="/members/superAdmin.php" disabled>SUPERadmin</a>
		</div>
	<?php } ?>
	<?php if ($isPointsEnabled) { ?>
		<div class="btn-group" role="group">
			<a class="btn btn-default" href="/members/points.php">Points</a>
		</div>
	<?php } ?>
</div>
<div class="container content-card wide">
	<h4>SUPER ADMIN PAGE: Modify the App State</h4>
	<table class="table">
		<tr>
			<td class="title-cell">General</td>
			<td></td>
		</tr>
		<tr>
			<td>Convention Month (1-12)</td>
			<td><input type="text" id="con-month"/></td>
		</tr>
		<tr>
			<td>Convention Day (1-31)</td>
			<td><input type="text" id="con-day"/></td>
		</tr>
		<tr>
			<td>Convention Year</td>
			<td><input type="text" id="con-year"/></td>
		</tr>
		<tr>
			<td>Badge Price (##.##)</td>
			<td><input type="text" id="badge-price"/></td>
		</tr>
		<tr>
			<td>Enable Registration</td>
			<td><input type="checkbox" id="enable-registration"/></td>
		</tr>
		<tr>
			<td>Enable Points</td>
			<td><input type="checkbox" id="enable-points"/></td>
		</tr>
		<tr>
			<td><span id="message" style="float:right;"></span></td>
			<td>
				<button type="button" id="save-changes">Save</button>
			</td>
		</tr>
	</table>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" src="/members/lib/bootstrap-old/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript" src="/members/lib/underscore/underscore.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {

		setup();

		function setup() {
			var $conMonthTextbox = $('#con-month');
			var $conDayTextbox = $('#con-day');
			var $conYearTextbox = $('#con-year');
			var $badgePriceTextbox = $('#badge-price');
			var $enableRegistrationCheckbox = $('#enable-registration');
			var $enablePointsCheckbox = $('#enable-points');
			var $saveButton = $('#save-changes');
			var $message = $('#message');

			// Get the values from the PHP variables
			var conMonth = (<?php echo $conMonth; ?>);
			var conDay = (<?php echo $conDay; ?>);
			var conYear = (<?php echo $conYear; ?>);
			var badgePrice = ("<?php echo $badgePrice; ?>");
			var isRegistrationEnabled = (<?php echo $isRegistrationEnabled; ?>) === 1;
			var isPointsEnabled = (<?php echo $isPointsEnabled; ?>) === 1;

			// Set the starting state
			$conMonthTextbox.val(conMonth);
			$conDayTextbox.val(conDay);
			$conYearTextbox.val(conYear);
			$badgePriceTextbox.val(badgePrice);
			$enableRegistrationCheckbox.prop('checked', isRegistrationEnabled);
			$enablePointsCheckbox.prop('checked', isPointsEnabled);

			$saveButton.click(function _onClick(e) {
				// Build up the parameters
				var params = [];
				params.push('conMonth=' + $conMonthTextbox.val());
				params.push('conDay=' + $conDayTextbox.val());
				params.push('conYear=' + $conYearTextbox.val());
				params.push('badgePrice=' + $badgePriceTextbox.val());
				params.push($enableRegistrationCheckbox.is(':checked') ? 'enableRegistration' : 'disableRegistration');
				params.push($enablePointsCheckbox.is(':checked') ? 'enablePoints' : 'disablePoints');

				if (isInvalidYear($conYearTextbox.val())) {
					$message.text("Changes ignored. Cannot edit past/future/invalid years.");
					return;
				} else if (isInvalidPrice("" + $badgePriceTextbox.val())) {
					$message.text("Changes ignored. Invalid badge price.");
					return;
				}

				// Make the ajax call
				$.ajax({
					type: 'POST',
					url: '/members/api-v2/setAppState.php',
					data: params.join('&'),
					statusCode: {
						200: function(resp) {
							$message.text(resp.data);
						},
						304: function() {
							$message.text("No changes.");
						}
					},
					error: function(jqXHR) {
						var resp = jqXHR.responseJSON;
						$message.text(resp.error);
					}
				});
			});
		}

		function isInvalidYear(year) {
			try {
				year = parseInt(year);
				if (isNaN(year)) return true;
			} catch(e) {
				return true;
			}

			// The year is invalid if it is not the current year
			var currentYear = new Date().getFullYear();
			return year !== currentYear;
		}

		function isInvalidPrice(price) {
			return !price.match(/^\d{2}\.\d{2}$/);
		}
	});
</script>
</body>
</html>