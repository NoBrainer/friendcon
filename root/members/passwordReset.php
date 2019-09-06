<?php
session_start();
$userSession = $_SESSION['userSession'];

// Short-circuit forwarding
include('utils/reroute_functions.php');
if (forwardHttps() || forwardHomeIfLoggedIn()) {
    exit;
}

include('utils/dbconnect.php');
include('utils/sql_functions.php');

if (isset($_POST['btn-signup'])) {
    $hash = trim($_POST['checkDatHash']);
    $password = trim($_POST['uPass']);
    $email = trim($_POST['uEmail']);

    //get the pass hash from the db if it matches the token input
    $hashQuery = "SELECT password FROM users WHERE password = ?";
    $hashResult = prepareSqlForResult($MySQLi_CON, $hashQuery, 's', $hash);
    $hashRow = getNextRow($hashResult);

    //get the email addresses from the db if it matches the token input
    $emailQuery = "SELECT email FROM users WHERE password = ? AND email = ?";
    $emailResult = prepareSqlForResult($MySQLi_CON, $emailQuery, 'ss', $hash, $email);
    $emailRow = getNextRow($emailResult);

    //if emails match and hashes match, do this
    if ($emailRow && $emailRow[0] == $email && $hashRow && $hashRow[0] == $hash) {

        //hash the new password and update
        $hashedPassword = md5($password);
        $query = "UPDATE users SET password = ? WHERE email = ?";
        $result = prepareSqlForResult($MySQLi_CON, $query, 'ss', $hashedPassword, $email);

        //if update query is successful, do this
        if (hasRows($result)) {
            $msg = "<div class='alert alert-success'>
						<span class='glyphicon glyphicon-info-sign'></span>
						<span>Password Successfully Updated!</span>
					</div>";

            //send an email to the user saying it was successful
            $to = $email;
            $subject = "Your FriendCon Account Password Request";
            $txt = "Your Password has been successfully reset. If you did not change your password, please contact " .
                    "us immediately at admin@friendcon.com\n";
            $headers = "From: admin@friendcon.com";

            mail($to, $subject, $txt, $headers);
        } else {
            //if the query fails, give an error message
            $msg = "<div class='alert alert-danger'>
                        <span class='glyphicon glyphicon-info-sign'></span>
                        <span>There was an error processing your request. Please Try Again.</span>
                    </div>";
        }
    } else {
        //If the info doesn't match, throw an error
        $msg = "<div class='alert alert-danger'>
					<span class='glyphicon glyphicon-info-sign'></span>
					<span>Something is not correct with the info provided. Try again if you want.</span>
				</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your New Friendcon Password</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">
    <div class="container content-card">
        <form method="post" id="register-form" onsubmit="return checkCheckBox(this)">
            <h2 class="form-signin-heading center">Reset Your Password</h2>
            <hr/>
            <?php if (isset($msg)) { ?>
                <?php echo $msg; ?>
            <?php } ?>

            <div class="form-group">
                <input type="email" class="form-control" placeholder="Account Email" name="uEmail" id="uEmail"
                       required/>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" placeholder="Your Token" name="checkDatHash"
                       id="checkDatHash" required/>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" placeholder="New Password" name="uPass" id="uPass"
                       required/>
            </div>
            <hr/>

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
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script src="/members/js/formatter.js"></script>

</body>
</html>