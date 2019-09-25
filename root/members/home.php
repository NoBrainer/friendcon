<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/secrets/initDB.php');
include('api-v2/internal/checkAdmin.php'); //includes functions.php
include('api-v2/internal/checkAppState.php');

// Short-circuit forwarding
if (forwardHttps() || forwardIndexIfLoggedOut()) {
    exit;
}

// Get the user data
$query = "SELECT u.email, u.emergencyCn, u.emergencyCNP, u.favoriteAnimal, u.favoriteBooze,
            u.favoriteNerdism, u.name, u.phone, u.uid, u.isRegistered, u.isPresent, u.upoints, h.housename AS housename
        FROM users u
        JOIN house h ON h.houseid = u.houseid
        WHERE uid = ?";
$result = executeSqlForResult($MySQLi_CON, $query, 'i', $userSession);
$userRow = $result->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
$points = $userRow['upoints'];
$houseName = $userRow['housename'];
$isRegistered = $userRow['isRegistered'] == 1;
$isPresent = $userRow['isPresent'] == 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome <?php echo $emailAddress; ?></title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/fontawesome/css/fontawesome-all.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">
    <!-- TODO: clean this up after we figure out if we want to keep houses
	<div class="container title-card">
		<br/>
		<br/>
		<?php if ($isRegistrationEnabled) { ?>
			<?php if ($isRegistered) { ?>
				<?php if ("$houseName" == "Unsorted") { ?>
					<p>You are registered!</p>
					<div class="container content-card">
						<a href="/members/registration.php" class="btn btn-default btn-wide">Update Registration</a>
					</div>
				<?php } else { ?>
					<p>You're a member of house <?php echo $houseName; ?></p>
					<?php if ($isPointsEnabled) { ?>
						<p>Your points: <?php echo $points; ?> </p>
					<?php } ?>
				<?php } ?>
			<?php } else { ?>
				<div class="container content-card">
					<a href="/members/registration.php" class="btn btn-default btn-wide">FriendCon <?php echo $conYear; ?> Registration!</a>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
	-->

    <?php if ($isRegistrationEnabled) { ?>
        <div class="container content-card registration-card">
            <h4>FriendCon <?php echo $conYear; ?> Registration</h4>
            <?php if ($isRegistered && $isPresent) { ?>
                <p><i class="fa fa-check-square green"></i> Registered!</p>
                <p><i class="fa fa-check-square green"></i> Checked In!</p>
            <?php } else if ($isRegistered) { ?>
                <p><i class="fa fa-check-square green"></i> Registered!
                    (<a href="/tickets/" target="_blank">Hotel Room Block <i class="fa fa-external-link-alt"></i></a>)
                </p>
                <p><i class="fa fa-times-circle"></i> Attend FriendCon and Check-in...</p>
            <?php } else { ?>
                <a href="/members/registration.php" class="btn btn-default btn-wide">Registration</a>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="container content-card">
        <h4>Friend Panel</h4>
        <?php if ($isRegistrationEnabled && $houseName != "Unsorted" && $isPointsEnabled) { ?>
            <a href="/members/points.php" class="btn btn-default btn-wide">My Points</a>
        <?php } ?>
        <!--
        <a href="/members/docs/FriendCon-2017-Information.pdf" target="_blank" class="btn btn-default btn-wide">2017 Game Info</a>
        -->
        <a href="/members/profile.php" class="btn btn-default btn-wide">My Profile</a>
    </div>

    <?php if ($isAdmin) { ?>
        <div class="container content-card">
            <h4>Admin Panel</h4>
            <h5>Admin Functions:</h5>
            <a href="/members/adminCheckIn.php" class="btn btn-default btn-wide">Check-in</a>
            <a href="/members/adminTeamSort.php" class="btn btn-default btn-wide">Team Sorting</a>
            <a href="/members/adminEmailList.php" class="btn btn-default btn-wide">Email List</a>
            <?php if ($isSuperAdmin) { ?>
                <hr/>
                <h5>Super Admin Functions:</h5>
                <a href="/members/superAdmin.php" class="btn btn-default btn-wide">Super Admin Page</a>
            <?php } ?>
            <?php if ($isPointsEnabled) { ?>
                <hr/>
                <h5>Non-Admin Functions (sometimes hidden to normal users):</h5>
                <a href="/members/points.php" class="btn btn-default btn-wide">My Points</a>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript">
    //console.log("<?php //echo $userSession; ?>//");
    //console.log("<?php echo $name; ?>");
</script>
</body>
</html>