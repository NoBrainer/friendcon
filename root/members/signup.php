<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/secrets/initDB.php');
include('api-v2/internal/functions.php');

// Short-circuit forwarding
if (forwardHttps() || forwardHomeIfLoggedIn()) {
    exit;
}

if (isset($_POST['btn-signup'])) {
    $name = trim($_POST['name']);
    $phone = preg_replace('/\D+/', '', trim($_POST['phone']));
    $favoriteBooze = trim($_POST['favoriteBooze']);
    $favoriteNerdism = trim($_POST['favoriteNerdism']);
    $favoriteAnimal = trim($_POST['favoriteAnimal']);
    $email = trim($_POST['email']);
    $hashedPassword = md5(trim($_POST['password']));
    $emergencyCN = trim($_POST['emergencyCN']);
    $emergencyCNP = preg_replace('/\D+/', '', trim($_POST['emergencyCNP']));

    $emailQuery = "SELECT email FROM users WHERE email = ?";
    $emailResult = executeSqlForResult($MySQLi_CON, $emailQuery, 's', $email);

    if (hasRows($emailResult)) {
        // Email is already registered
        $msg = "<div class='alert alert-danger'>
					<span class='fa fa-info-circle'></span>
					<span>This email has previously registered!</span>
				</div>";
    } else {
        // Try to register the user
        $query = "INSERT INTO users(`name`, `email`, `phone`, `password`, `favoriteAnimal`, `favoriteBooze`, " .
                "`favoriteNerdism`, `emergencyCN`, `emergencyCNP`, `agreeToTerms`) " .
                "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $affectedRows = executeSqlForAffectedRows($MySQLi_CON, $query, 'sssssssss', $name, $email, $phone,
                $hashedPassword, $favoriteAnimal, $favoriteBooze, $favoriteNerdism, $emergencyCN, $emergencyCNP);

        if ($affectedRows === 1) {
            $shouldSendEmailToAdmin = true;
            $shouldSendEmailToUser = true;
            $msg = "<div class='alert alert-success'>
						<span class='fa fa-info-circle'></span>
						<span>Registration Successful!</span>
					</div>";
        } else {
            $shouldSendEmailToAdmin = true;
            $shouldSendEmailToUser = false;
            $msg = "<div class='alert alert-danger'>
						<span class='fa fa-info-circle'></span>
						<span>You gotta try again! Sorry.</span>
					</div>";
        }

        // Email settings
        $headers = "From: admin@friendcon.com";
        $toAdmin = "admin@friendcon.com";
        $subjectAdmin = "New Friend Registered! Welcome, {$name}!";
        $bodyAdmin = "Name: {$name}\nEmail: {$email}";
        $toUser = $email;
        $subjectUser = "Your FriendCon Account Has Been Created!";
        $txtUser = "Hey there, {$name}!\n\nWe're so happy you decided to create an account and hopefully join us at " .
                "the next FriendCon! We look forward to seeing you there!\n\nAll the best from your friends at " .
                "FriendCon!\n";

        if ($shouldSendEmailToAdmin) {
            // Send ourselves an email on new user registration
            mail($toAdmin, $subjectAdmin, $bodyAdmin, $headers);
        }

        if ($shouldSendEmailToUser) {
            // Send an email to the user saying it was successful
            mail($toUser, $subjectUser, $txtUser, $headers);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendCon - Registration</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">
    <div class="container content-card">
        <form method="post" id="register-form" onsubmit="return checkCheckBox(this)">
            <h2 class="form-signin-heading center">Sign Up</h2>
            <hr/>
            <?php if (isset($msg)) { ?>
                <?php echo $msg; ?>
            <?php } else { ?>
                <div class='alert alert-info'>
                    <span class='fa fa-asterisk'></span> &nbsp; Everything is required. EVERYTHING!
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
                <input type="password" class="form-control" placeholder="Confirm Password" name="Confirmpassword" id="pass2" onkeyup="checkPass(); return false;" required/>
            </div>
            <span id="confirm-message" class="confirm-message"></span>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Information</h4>
            <h5 class="form-signin-heading center">*This section is used to make you a custom/premium badge.</h5>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Animal" name="favoriteAnimal" required="required"/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Nerdy Thing (Game, Book, Topic, etc.)" name="favoriteNerdism" required="required"/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Food/Beverage" name="favoriteBooze" required="required"/>
                <span id="check-e"></span>
            </div>
            <hr/>

            <h4 class="form-signin-heading center">Emergency Contact Information</h4>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Emergency Contact Name" name="emergencyCN" required="required"/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="tel" class="form-control" placeholder="Emergency Contact Phone Number" name="emergencyCNP" required/>
                <span id="check-e"></span>
            </div>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Code of Conduct</h4>
            <div>
                By checking the box, you agree to the
                <a href="/members/fwd/code_of_conduct.php" target="_blank">FriendCon Code of Conduct
                    <i class="fa fa-external-link-alt"></i></a>.
            </div>
            <div class="acknowledge-color">
                <input id="acknowledge-code-of-conduct" type="checkbox" value="0" name="agree">
                <b>I Acknowledge and Agree to the FriendCon Code of Conduct</b>
            </div>
            <hr/>

            <div class="form-group">
                <a href="/members/index.php" class="btn btn-default">Already Signed Up?</a>
                <button type="submit" class="btn btn-default pull-right" id="submit" name="btn-signup">
                    <span class="fa fa-sign-in-alt"></span>
                    <span>Create Account</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script src="/members/js/formatter.js"></script>
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
        var $pass1 = $('#pass1');
        var text1 = $pass1.val();
        var $pass2 = $('#pass2');
        var text2 = $pass2.val();
        var $message = $('#confirm-message');
        var goodColor = "#66cc66";
        var badColor = "#ff6666";
        var neutralColor = "#fff";

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
