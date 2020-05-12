<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/initDB.php');
include('api-v2/internal/functions.php');

// Short-circuit forwarding
if (forwardHttps() || forwardIndexIfLoggedOut()) {
	exit;
}


// Get the user data
$query =
		"SELECT u.email, u.emergencyCn, u.emergencyCNP, u.favoriteAnimal, u.favoriteBooze, u.favoriteNerdism, u.name," .
		" u.phone, u.uid, u.upoints, uh.housename AS housename FROM users u JOIN house uh ON uh.houseid = u.houseid" .
		" WHERE uid = ?";
$result = executeSqlForResult($mysqli, $query, 'i', $userSession);
$userRow = $result->fetch_array();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Welcome <?php echo $userRow['email']; ?></title>
	<link rel="stylesheet" media="screen" href="/members/lib/bootstrap-old/css/bootstrap-3.3.4.min.css">
	<link rel="stylesheet" media="screen" href="/members/lib/bootstrap-old/css/bootstrap-theme-3.3.5.min.css">
	<link rel="stylesheet" media="screen" href="/members/lib/fontawesome/css/all.min.css">
	<link rel="stylesheet" media="screen" href="/members/css/old.css">
	<link rel="icon" href="/wp-content/uploads/2019/02/cropped-fc-32x32.png">
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">

	<div class="container content-card">
		<h3 class="form-signin-heading center">My Profile</h3>
		<hr/>
		<h4 class="form-signin-heading center">Your Phone Number</h4>
		<div class="form-group">
			<input type="tel" pattern='[\(]\d{3}[\)]\d{3}[\-]\d{4}' class="form-control phone"
				   placeholder="Phone Number ex. (123)867-5309" name="phone" required/>
		</div>
		<hr/>
		<h4 class="form-signin-heading center">Emergency Contact Information</h4>

		<div class="form-group">
			<input type="text" class="form-control emergencyName" placeholder="Emergency Contact Name"
				   name="emergencyCN" required="required"/>
			<span id="check-e"></span>
		</div>

		<div class="form-group">
			<input type="tel" pattern='[\(]\d{3}[\)]\d{3}[\-]\d{4}' class="form-control emergencyNumber"
				   placeholder="Emergency Contact Phone Number ex. (123)867-5309" name="emergencyCNP" required/>
			<span id="check-e"></span>
		</div>

		<h4 class="form-signin-heading center">Preferences</h4>
		<div class="form-group">
			<span>Favorite Animal:</span>
			<input type="text" class="form-control animal" placeholder="Favorite Animal" name="favoriteAnimal"
				   required="required"/>
			<span id="check-e"></span>
		</div>
		<div class="form-group">
			<span>Favorite Food/Beverage:</span>
			<input type="text" class="form-control food" placeholder="Favorite Food/Beverage" name="favoriteBooze"
				   required="required"/>
			<span id="check-e"></span>
		</div>
		<div class="form-group">
			<span>Favorite Nerdism:</span>
			<input type="text" class="form-control nerdism" placeholder="Favorite Nerdism" name="favoriteNerdism"
				   required="required"/>
			<span id="check-e"></span>
		</div>

		<div class="form-group">
			<button class="btn btn-default" id="submit" name="btn-update"
					style=" display:block; margin-left: auto; margin-right: auto;">Update Settings
			</button>
		</div>

		<div class="alert alert-success" style="display:none">
			<span class="fa fa-info-circle"></span>
			<span class="message"></span>
		</div>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery.min.js"></script>
<script src="/members/lib/bootstrap-old/js/bootstrap-3.3.4.min.js"></script>
<script src="/members/js/utils.js"></script>
<script type="text/javascript">
	(function() {
		// When the phone number input loses focus, format the phone number, if possible
		formatPhoneNumberOnBlur($('.form-control[name=phone], .form-control[name=emergencyCNP]'));

		// Set the starting values (Note: 'blur' makes it reformat the phone number)
		var startingPhoneNumber = "<?php echo $userRow['phone']; ?>";
		var startingEmergencyName = "<?php echo $userRow['emergencyCn']; ?>";
		var startingEmergencyPhone = "<?php echo $userRow['emergencyCNP']; ?>";
		var startingFavoriteAnimal = "<?php echo $userRow['favoriteAnimal']; ?>";
		var startingFavoriteBooze = "<?php echo $userRow['favoriteBooze']; ?>";
		var startingFavoriteNerdism = "<?php echo $userRow['favoriteNerdism']; ?>";
		$('.form-control[name=phone]').val(startingPhoneNumber).blur();
		$('.form-control[name=emergencyCN]').val(startingEmergencyName);
		$('.form-control[name=emergencyCNP]').val(startingEmergencyPhone).blur();
		$('.form-control[name=favoriteAnimal]').val(startingFavoriteAnimal);
		$('.form-control[name=favoriteBooze]').val(startingFavoriteBooze);
		$('.form-control[name=favoriteNerdism]').val(startingFavoriteNerdism);

		function clearAlert() {
			var $alert = $('.alert');
			if (!$alert.is(':hidden')) {
				$alert.hide();
				$('.alert .message').text("");
			}
		}

		function buildProfileParams() {
			var arr = [
				"phone=" + $('.phone').val().trim(),
				"emergencyCN=" + $('.emergencyName').val().trim(),
				"emergencyCNP=" + $('.emergencyNumber').val().trim(),
				"favoriteAnimal=" + $('.animal').val().trim(),
				"favoriteBooze=" + $('.food').val().trim(),
				"favoriteNerdism=" + $('.nerdism').val().trim()
			];
			return arr.join('&');
		}

		// When any input gains focus, is clicked, or has a keydown call clearAlert()
		$('input').focus(clearAlert).click(clearAlert).keydown(clearAlert);

		// Click handler for the submit button
		$('#submit').click(function() {
			var $alert = $('.alert');
			$alert.hide();
			$alert.removeClass('alert-danger alert-info alert-success');
			$.ajax({
				type: 'POST',
				url: '/members/api-v2/users/update.php',
				data: buildProfileParams(),
				statusCode: {
					200: function(resp) {
						$alert.addClass('alert-success');
						$('.alert .message').text(resp.data);
					},
					304: function() {
						$alert.addClass('alert-info');
						$('.alert .message').text("No changes.");
					}
				},
				error: function(jqXHR) {
					var resp = jqXHR.responseJSON;
					$alert.addClass('alert-danger');
					$('.alert .message').text(resp.error);
				},
				complete: function() {
					$alert.show();
				}
			});
		});
	})();
</script>
</body>
</html>