<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/initDB.php');
include('api-v2/internal/functions.php');

// Short-circuit forwarding
if (forwardHttps() || forwardHomeIfLoggedIn()) {
	exit;
}

//TODO: move this to the backend
if (isset($_POST['btn-login'])) {
	$email = trim($_POST['email']);
	$password = trim($_POST['password']);
	$query = "SELECT uid, email, password FROM users WHERE email = ?";
	$result = executeSqlForResult($mysqli, $query, 's', $email);

	if (hasRows($result, 1)) {
		$row = getNextRow($result);
		if (md5($password) === $row['password']) {
			$_SESSION['userSession'] = $row['uid'];
			header("Location: /members/home.php");
		} else {
			$msg = "<div class='alert alert-danger'>
						<span class='fa fa-info-circle'></span>
						<span>Your password does not match!</span>
					</div>";
		}
	} else {
		$msg = "<div class='alert alert-danger'>
					<span class='fa fa-info-circle'></span>
					<span>No registration entry with this email!</span>
				</div>";
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FriendCon - Sign In</title>
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
		<form method="post" id="login-form">
			<h2 class="form-signin-heading center">Sign In</h2>
			<h3 class="form-signin-heading">
				<a href="/members/signup.php" class="btn btn-default btn-wide">Sign up for an account here!</a>
			</h3>
			<hr/>

			<?php if (isset($msg)) echo $msg; ?>

			<div class="form-group">
				<input type="email" class="form-control" placeholder="Email address" name="email" required/>
				<span id="check-e"></span>
			</div>

			<div class="form-group">
				<input type="password" class="form-control" placeholder="Password" name="password" required/>
			</div>
			<hr/>

			<div class="form-group">
				<button type="submit" name="btn-login" id="btn-login" style="display:block; margin-left: auto; margin-right: auto;">
					<span class="fa fa-sign-in-alt"></span>
					&nbsp;
					<span>Sign In</span>
				</button>
			</div>

			<a href="/members/forgotPassword.php">Forgot Your Password?</a>
		</form>
	</div>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery.min.js"></script>
<script src="/members/lib/bootstrap-old/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript">
	//console.log("<?php echo $userSession; ?>");
	//console.log("<?php echo $userRow['name']; ?>");
</script>
</body>
</html>