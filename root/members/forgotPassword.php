<?php
session_start();
$userSession = $_SESSION['userSession'];

if (isset($userSession) && $userSession != "") {
    // If logged in, go to registration home
    header("Location: /members/home.php");
    exit;
}
include('utils/dbconnect.php');
include('utils/sql_functions.php');

//on button submit action
if (isset($_POST['btn-signup'])) {
    //TODO: clean this stuff up with my own conventions
    $email = trim($_POST['email']);

    //check to see if the email exists
    $emailQuery = "SELECT email FROM users WHERE email='?'";
    $emailResult = prepareSqlForResult($MySQLi_CON, $emailQuery, 's', [$email]);
    $count = $emailResult->num_rows;

    //if that email address has an account associated with it and thus has a return row, do this
    if ($count == 1) {
        $passwordQuery = "SELECT password FROM users WHERE email='?'";

        //get the password hash
        $passwordResult = prepareSqlForResult($MySQLi_CON, $passwordQuery, 's', [$email]);
        //put the sqli item into an array
        $row = mysqli_fetch_array($passwordResult);
        //hash is now $row[0]

        //if query to get the password is successful, do this
        if ($passwordResult) {
            $msg = "<div class='alert alert-success'>
						<span class='glyphicon glyphicon-info-sign'></span>
						<span>You'll get a token in your email!</span>
					</div>";

            //Send Email to the User
            $to = $email;
            $subject = "Your FriendCon Account Password Request";
            $txt = "Thanks for reaching out to us about your FriendCon account. Your token is below and can be used " .
                    "to reset your account at http://friendcon.com/members/passwordReset\nToken: " . $row[0];
            $headers = "From: admin@friendcon.com";

            mail($to, $subject, $txt, $headers);
        } else {
            //if the sql query failed, do this
            $msg = "<div class='alert alert-danger'>
						<span class='glyphicon glyphicon-info-sign'></span>
						<span>Something went wrong. Inexplicably.</span>
					</div>";
        }
    } else {
        //if there is no account record found, do this
        $msg = "<div class='alert alert-danger'>
					<span class='glyphicon glyphicon-info-sign'></span>
					<span>Something went wrong! Contact us at admins@friendcon.com</span>
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