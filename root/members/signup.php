<?php
session_start();
$userSession = $_SESSION['userSession'];

if (isset($userSession) && $userSession != "") {
    // If logged in, go to registration home
    header("Location: /members/home.php");
    exit;
}
include_once('../utils/dbconnect.php');

if (isset($_POST['btn-signup'])) {
    $name = $MySQLi_CON->real_escape_string(trim($_POST['name']));
    $phone = $MySQLi_CON->real_escape_string(trim($_POST['phone']));
    $badgeType = $MySQLi_CON->real_escape_string(trim($_POST['badgeType']));
    $favoriteBooze = $MySQLi_CON->real_escape_string(trim($_POST['favoriteBooze']));
    $favoriteNerdism = $MySQLi_CON->real_escape_string(trim($_POST['favoriteNerdism']));
    $favoriteAnimal = $MySQLi_CON->real_escape_string(trim($_POST['favoriteAnimal']));
    $email = $MySQLi_CON->real_escape_string(trim($_POST['email']));
    $upass = $MySQLi_CON->real_escape_string(trim($_POST['password']));
    $emergencyCN = $MySQLi_CON->real_escape_string(trim($_POST['emergencyCN']));
    $emergencyCNP = $MySQLi_CON->real_escape_string(trim($_POST['emergencyCNP']));

    $new_password = md5($upass);

    $check_email = $MySQLi_CON->query("SELECT email FROM users WHERE email='$email'");
    $count = $check_email->num_rows;

    if ($count == 0) {
        //get the shit outta here
        $phone = preg_replace('/\D+/', '', $phone);
        $emergencyCNP = preg_replace('/\D+/', '', $emergencyCNP);
        $query = "INSERT INTO users(name,badgeType,email,phone,password,favoriteAnimal,favoriteBooze,favoriteNerdism,emergencyCN,emergencyCNP) VALUES('$name','$badgeType','$email','$phone','$new_password','$favoriteAnimal','$favoriteBooze','$favoriteNerdism','$emergencyCN','$emergencyCNP')";

        if ($MySQLi_CON->query($query)) {
            $msg = "<div class='alert alert-success'>
						<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Registration Successful!
					</div>";
        } else {
            $msg = "<div class='alert alert-danger'>
						<span class='glyphicon glyphicon-info-sign'></span> &nbsp; You gotta try again! Sorry.
					</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>
					<span class='glyphicon glyphicon-info-sign'></span> &nbsp; This email has previously registered!
				</div>";
    }
    //send ourselves an email on new user registration
    $toUs = "admin@friendcon.com";
    $subjectUs = "New Friend Registered! Welcome, " . $name . "!";
    $txtUs = "Name: " . $name . "\r\n" . "Email: " . $email;
    $headers = "From: admin@friendcon.com";

    mail($toUs, $subjectUs, $txtUs, $headers);

    //send an email to the user saying it was successful
    $to = $email;
    $subject = "Your FriendCon Account Has Been Created!";
    $txt = "Hey there, " . $name . "!" . "\r\n" . "\r\n" . "We're so happy you decided to create an account and hopefully join us at the next FriendCon! We look forward to seeing you there!" . "\r\n" . "\r\n" . "All the best from your friends at FriendCon!" . "\r\n";

    mail($to, $subject, $txt, $headers);

    $MySQLi_CON->close();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendCon - Registration</title>
    <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../lib/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>

<body>
<?php include_once('header.php'); ?>

<div class="container content">
    <div class="container content-card">
        <form method="post" id="register-form" onsubmit="return checkCheckBox(this)">
            <h2 class="form-signin-heading center">Sign Up</h2>
            <hr/>
            <?php if (isset($msg)) { ?>
                <?php echo $msg; ?>
            <?php } else { ?>
                <div class='alert alert-info'>
                    <span class='glyphicon glyphicon-asterisk'></span> &nbsp; Everything is required. EVERYTHING!
                </div>
            <?php } ?>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Full name" name="name" required/>
            </div>

            <div class="form-group">
                <input type="tel" class="form-control" placeholder="Phone Number" name="phone" required/>
            </div>

            <div class="form-group">
                <input type="email" class="form-control" placeholder="Email address" name="email" required/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" placeholder="Password" name="password" id="pass1" required/>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" placeholder="Confirm Password" name="Confirmpassword"
                       id="pass2" onkeyup="checkPass(); return false;" required/>
            </div>
            <span id="confirm-message" class="confirm-message"></span>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Information</h4>
            <h5 class="form-signin-heading center">*This section is used to make you a custom/premium badge.</h5>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Animal" name="favoriteAnimal"
                       required="required"/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Nerdy Thing (Game, Book, Topic, etc.)"
                       name="favoriteNerdism" required="required"/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Food/Beverage" name="favoriteBooze"
                       required="required"/>
                <span id="check-e"></span>
            </div>
            <hr/>

            <h4 class="form-signin-heading center">Emergency Contact Information</h4>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Emergency Contact Name" name="emergencyCN"
                       required="required"/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="tel" class="form-control" placeholder="Emergency Contact Phone Number" name="emergencyCNP"
                       required/>
                <span id="check-e"></span>
            </div>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Code of Conduct</h4>
            <div>
                By checking the box, you agree to the <a href="http://friendcon.com/code_of_conduct.php"
                                                         target="_blank">FriendCon Code of Conduct</a>.
            </div>
            <div class="acknowledge-color">
                <input id="acknowledge-code-of-conduct" type="checkbox" value="0" name="agree">
                <b>I Acknowledge and Agree to the FriendCon Code of Conduct</b>
            </div>
            <hr/>

            <div class="form-group">
                <a href="index.php" class="btn btn-default">Already Signed Up?</a>
                <button type="submit" class="btn btn-default pull-right" id="submit" name="btn-signup">
                    <span class="glyphicon glyphicon-log-in"></span> &nbsp; Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>

</script>

<!-- JavaScript -->
<script type="text/javascript" src="/js/jquery-1.11.1.min.js"></script>
<script src="/lib/bootstrap/js/bootstrap.min.js"></script>
<script src="/js/utils/formatter.js"></script>
<script type="text/javascript">
    function checkCheckBox(form) {
        if (form.agree.checked == false) {
            alert('Please agree to the Code of Conduct to continue.');
            return false;
        } else {
            return true;
        }
    }

    function checkPass() {
        //Store the password field objects into variables ...
        var $pass1 = $('#pass1'),
            text1 = $pass1.val(),
            $pass2 = $('#pass2'),
            text2 = $pass2.val(),
            $message = $('#confirm-message'),
            goodColor = "#66cc66",
            badColor = "#ff6666",
            neutralColor = "#fff";

        if (text1 === text2) {
            if (text1 === "") {
                // Passwords are blank
                $pass1.css('backgroundColor', neutralColor);
                $pass2.css('backgroundColor', neutralColor);
                $message.css('color', badColor);
                $message.html("Password is required!");
            } else {
                // Passwords match
                $pass1.css('backgroundColor', neutralColor);
                $pass2.css('backgroundColor', neutralColor);
                $message.css('color', goodColor);
                $message.html("");
            }
        } else {
            // Passwords do not match
            $pass1.css('backgroundColor', badColor);
            $pass2.css('backgroundColor', badColor);
            $message.css('color', badColor);
            $message.html("Passwords Do Not Match!");
        }
    }

    (function() {
        // When the phone number input loses focus, format the phone number, if possible
        formatPhoneNumberOnBlur($('.form-control[name=phone], .form-control[name=emergencyCNP]'));
    })();
</script>
</body>
</html>