<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to registration index
    header("Location: /members/index.php");
    exit;
}
include_once('utils/dbconnect.php');
include_once('utils/checkadmin.php');
include_once('utils/check_app_state.php');

// Get the user data
$query = $MySQLi_CON->query("SELECT u.email, u.emergencyCn, u.emergencyCNP, u.favoriteAnimal, u.favoriteBooze, 
        u.favoriteNerdism, u.name, u.phone, u.uid, u.isRegistered, u.isPaid, u.isPresent, u.upoints, h.housename AS housename 
	FROM users u 
	JOIN house h ON h.houseid = u.houseid 
	WHERE uid={$userSession}");
$userRow = $query->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
$points = $userRow['upoints'];
$houseName = $userRow['housename'];
$isRegistered = $userRow['isRegistered'] == 1;
$isPaid = $userRow['isPaid'] == 1;
$isPresent = $userRow['isPresent'] == 1;

$MySQLi_CON->close();
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
<?php include_once('header.php'); ?>

<div class="container content">
    <!-- TODO: clean this up after we figure out if we want to keep houses
	<div class="container title-card">
		<br/>
		<br/>
		<?php if ($isRegistrationEnabled) { ?>
			<?php if ($isRegistered && $isPaid) { ?>
				<p>You are registered, and your payment has been confirmed!</p>
				<div class="container content-card">
					<p>Registered: <i class="fa fa-check-square"></i></p>
					<p>Payment Confirmed: <i class="fa fa-check-square"></i></p>
				</div>
			<?php } else if ($isRegistered) { ?>
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
            <?php if ($isRegistered && $isPaid && $isPresent) { ?>
                <p><i class="fa fa-check-square green"></i> Registered!</p>
                <p><i class="fa fa-check-square green"></i> Payment Confirmed!</p>
                <p><i class="fa fa-check-square green"></i> Checked In!</p>
            <?php } else if ($isRegistered && $isPaid) { ?>
                <p><i class="fa fa-check-square green"></i> Registered!</p>
                <p><i class="fa fa-check-square green"></i> Payment Confirmed!</p>
                <p><i class="fa fa-times-circle"></i> Attend FriendCon and Check-in...</p>
                <a href="/members/registration.php" class="btn btn-default btn-wide">Update My Registration</a>
            <?php } else if ($isRegistered) { ?>
                <p><i class="fa fa-check-square green"></i> Registered!</p>
                <p><i class="fa fa-times-circle"></i> Awaiting Payment Confirmation...</p>
                <a href="/members/registration.php" class="btn btn-default btn-wide">Update My Registration</a>
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
    console.log("<?php echo $userSession; ?>");
    //console.log("<?php echo $name; ?>");
</script>
</body>
</html>