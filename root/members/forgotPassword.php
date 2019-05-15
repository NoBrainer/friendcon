<?php
session_start();
$userSession = $_SESSION['userSession'];

if (isset($userSession) && $userSession != "") {
    // If logged in, go to registration home
    header("Location: /members/home.php");
    exit;
}
include('utils/dbconnect.php');

//on button submit action
if (isset($_POST['btn-signup'])) {
    $email = $MySQLi_CON->real_escape_string(trim($_POST['email']));

    //check to see if the email exists
    $check_email = $MySQLi_CON->query("SELECT email FROM users WHERE email='$email'");
    $count = $check_email->num_rows;

    //if that email address has an account associated with it and thus has a return row, do this
    if ($count == 1) {

        //get the password hash
        $passwordGet = $MySQLi_CON->query("SELECT password FROM users WHERE email='$email'");
        //put the sqli item into an array
        $row = mysqli_fetch_array($passwordGet);
        //hash is now $row[0]

        $password = "SELECT password FROM users WHERE email='$email'";

        //if query to get the password is successful, do this
        if ($MySQLi_CON->query($password)) {
            $msg = "<div class='alert alert-success'>
						<span class='glyphicon glyphicon-info-sign'></span> &nbsp; You'll get a token in your email!
					</div>";

            //Send Email to the User
            $to = $email;
            $subject = "Your FriendCon Account Password Request";
            $txt = "Thanks for reaching out to us about your FriendCon account. Your token is below and can be used to reset your account at http://friendcon.com/members/passwordReset" . "\r\n" . "Token: " . $row[0];
            $headers = "From: admin@friendcon.com";

            mail($to, $subject, $txt, $headers);

        } else {
            //if the sql query failed, do this
            $msg = "<div class='alert alert-danger'>
						<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Something went wrong. Inexplicably. 
					</div>";
        }
    } else {
        //if there is no account record found, do this
        $msg = "<div class='alert alert-danger'>
					<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Something went wrong! Contact us at admins@friendcon.com
				</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendCon - Forgot Password</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">
    <div class="container content-card">
        <form method="post" id="register-form" onsubmit="return checkCheckBox(this)">
            <h2 class="form-signin-heading center">Send Yourself A Password Reset Token!</h2>
            <hr/>
            <?php if (isset($msg)) { ?>
                <?php echo $msg; ?>
            <?php } else { ?>
            <?php } ?>

            <div class="form-group">
                <input type="email" class="form-control" placeholder="Email" name="email" id="email" required/>
            </div>

            <span id="confirm-message" class="confirm-message"></span>
            <hr/>

            <button type="submit" class="btn btn-default pull-right" id="submit" name="btn-signup">
                <span class="glyphicon glyphicon-log-in"></span> &nbsp; Send Token
            </button>

            <a href="/members/passwordReset.php">Already have a token?</a>

        </form>
    </div>
</div>

</body>
</html>