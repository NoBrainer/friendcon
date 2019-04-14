<?php
session_start();
$userSession = $_SESSION['userSession'];

if(isset($userSession) && $userSession != ""){
	// If logged in, go to registration home
	header("Location: /members/home.php");
	exit;
}
include_once('../utils/dbconnect.php');

if(isset($_POST['btn-signup'])){
	//sanitize the inputs
	$safeHash = $MySQLi_CON->real_escape_string(trim($_POST['checkDatHash']));
	$safePass = $MySQLi_CON->real_escape_string(trim($_POST['uPass']));
	$safeEmail = $MySQLi_CON->real_escape_string(trim($_POST['uEmail']));

	//get the pass hash from the db if it matches the token input
	$tokenGet = $MySQLi_CON->query("SELECT password FROM users WHERE password = '$safeHash'");
	//put results into an array
	$row = mysqli_fetch_array($tokenGet);
	//hash now in an array index starting at $row[0]
	
	//get the email addresses from the db if it matches the token input
	$tokenGetSuccess = $MySQLi_CON->query("SELECT email FROM users WHERE password = '$safeHash' AND email = '$safeEmail'");
	//put results into an array
	$email = mysqli_fetch_array($tokenGetSuccess);
	//emails are now in an array with index at $email[0]

	//if emails match and hashes match, do this
	if($email[0] == $safeEmail && $safeHash == $row[0]){
		
		//hash the new password and update
		$new_password = md5($safePass);
		$query = "UPDATE users SET password = '$new_password' WHERE email = '$safeEmail'";
		
		//if update query is successful, do this
		if($MySQLi_CON->query($query)){
			$msg = "<div class='alert alert-success'>
						<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Password Succesfully Updated!
					</div>";
					
			//send an email to the user saying it was successful		
			$to = $safeEmail;
			$subject = "Your FriendCon Account Password Request";
			$txt = "Your Password has been successfully reset. If you did not change your password, please contact us immediately at admin@friendcon.com" . "\r\n";
			$headers = "From: admin@friendcon.com";
		
			mail($to,$subject,$txt,$headers);
		}
		//if the query fails, give an error message
		else{
			$msg = "<div class='alert alert-danger'>
			<span class='glyphicon glyphicon-info-sign'></span> &nbsp; There was an error processing your request. Please Try Again.
			</div>";
		}
	}
	//If the info doesn't match, throw an error
	else{
		$msg = "<div class='alert alert-danger'>
					<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Something is not correct with the info provided. Try again if you want.
				</div>";
	}

	$MySQLi_CON->close();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set Your New Friendcon Password</title>
<link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="../lib/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
<link rel="stylesheet" href="style.css" type="text/css" />
</head>

<body>
<?php include_once('header.php'); ?>

<div class="container content">
	<div class="container content-card">
		<form method="post" id="register-form" onsubmit="return checkCheckBox(this)">
			<h2 class="form-signin-heading center">Reset Your Password</h2>
			<hr />
			<?php if(isset($msg)){ ?>
				<?php echo $msg; ?>
			<?php } ?>

			<div class="form-group">
				<input type="email" class="form-control" placeholder="Account Email" name="uEmail" id="uEmail" required  />
			</div>
			
			<div class="form-group">
				<input type="password" class="form-control" placeholder="Your Token" name="checkDatHash" id="checkDatHash" required  />
			</div>
			
			<div class="form-group">
				<input type="password" class="form-control" placeholder="New Password" name="uPass" id="uPass" required  />
			</div>
			<hr />
			
			<div class="form-group">
				<button type="submit" class="btn btn-default pull-right" id="submit" name="btn-signup">
					<span class="glyphicon glyphicon-log-in"></span> &nbsp; Reset Password
				</button>
			</div>
			
			<a href="/members/forgotPassword.php">Need a token?</a>
		</form>
	</div>
</div>

<script>

</script>

<!-- JavaScript -->
<script type="text/javascript" src="/js/jquery-1.11.1.min.js"></script>
<script src="/lib/bootstrap/js/bootstrap.min.js"></script>
<script src="/js/utils/formatter.js"></script>

</body>
</html>