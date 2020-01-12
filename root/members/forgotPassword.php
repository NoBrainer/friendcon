<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/functions.php');
include('api-v2/internal/secrets/initDB.php');

// Short-circuit forwarding
if (forwardHttps() || forwardHomeIfLoggedIn()) {
    exit;
}

//TODO: move this to the backend
//on button submit action
if (isset($_POST['btn-signup'])) {
    $email = trim($_POST['email']);

    //check to see if the email exists
    $emailQuery = "SELECT email FROM users WHERE email = ?";
    $emailResult = executeSqlForResult($mysqli, $emailQuery, 's', $email);

    //if that email address has an account associated with it and thus has a return row, do this
    if (hasRows($emailResult, 1)) {
        $passwordQuery = "SELECT password FROM users WHERE email = ?";
        $passwordResult = executeSqlForResult($mysqli, $passwordQuery, 's', $email);

        //if query to get the password is successful, do this
        if (hasRows($passwordResult, 1)) {
            $row = getNextRow($passwordResult);
            $hash = $row['password'];
            $msg = "<div class='alert alert-success'>" .
                    "	<span class='fa fa-info-circle'></span>" .
                    "	<span>You'll get a token in your email!</span>" .
                    "</div>";

            //Send Email to the User
            $to = $email;
            $subject = "Your FriendCon Account Password Request";
            $txt = "<div>Thanks for reaching out to us about your FriendCon account. Your token is below and can be " .
                    "used to reset your account at: https://friendcon.com/members/passwordReset</div>" .
                    "<div>Token: <span>$hash</span></div>";
            $headers = "From: admin@friendcon.com\r\nContent-type:text/html";

            mail($to, $subject, $txt, $headers);
        } else {
            //if the sql query failed or the count != 1
            $msg = "<div class='alert alert-danger'>" .
                    "	<span class='fa fa-info-circle'></span>" .
                    "	<span>Something went wrong. Inexplicably.</span>" .
                    "</div>";
        }
    } else {
        //if there is no account record found, do this
        $msg = "<div class='alert alert-danger'>" .
                "	<span class='fa fa-info-circle'></span>" .
                "	<span>Something went wrong! Contact us at admins@friendcon.com</span>" .
                "</div>";
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
    <link href="/members/lib/fontawesome/css/fontawesome-all.min.css" rel="stylesheet" media="screen">
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
            <?php } ?>

            <div class="form-group">
                <input type="email" class="form-control" placeholder="Email" name="email" id="email" required/>
            </div>

            <span id="confirm-message" class="confirm-message"></span>
            <hr/>

            <button type="submit" class="btn btn-default pull-right" id="submit" name="btn-signup">
                <span class="fa fa-sign-in-alt"></span>
                <span>Send Token</span>
            </button>

            <a href="/members/passwordReset.php">Already have a token?</a>

        </form>
    </div>
</div>

</body>
</html>