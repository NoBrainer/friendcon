<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/secrets/initDB.php');
include('api-v2/internal/functions.php');

// Short-circuit forwarding
if (forwardHttps() || forwardHomeIfLoggedIn()) {
    exit;
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
    <link href="/members/lib/fontawesome/css/fontawesome-all.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">
    <div class="container content-card">
        <form method="post" id="register-form" onsubmit="return false">
            <h2 class="form-signin-heading center">Sign Up</h2>
            <hr/>
            <div class="alert alert-info">
                <span class="fa fa-asterisk"></span>
                <span>Everything is required. EVERYTHING!</span>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Full name" id="name" required/>
            </div>

            <div class="form-group">
                <input type="tel" class="form-control" placeholder="Phone Number" id="phone" required/>
            </div>

            <div class="form-group">
                <input type="email" class="form-control" placeholder="Email address" id="email" required/>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" placeholder="Password" id="pass1" onkeyup="checkThatPasswordsMatch(true)" required/>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" placeholder="Confirm Password" id="pass2" onkeyup="checkThatPasswordsMatch(); return false;" required/>
            </div>
            <span id="password-validation" class="error password-validation"></span>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Information</h4>
            <h5 class="form-signin-heading center">*This section is used to make you a custom/premium badge.</h5>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Animal" id="animal" required="required"/>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Nerdy Thing (Game, Book, Topic, etc.)" id="nerdism" required="required"/>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Favorite Food/Beverage" id="booze" required="required"/>
            </div>
            <hr/>

            <h4 class="form-signin-heading center">Emergency Contact Information</h4>

            <div class="form-group">
                <input type="text" class="form-control" placeholder="Emergency Contact Name" id="contactName" required="required"/>
            </div>

            <div class="form-group">
                <input type="tel" class="form-control" placeholder="Emergency Contact Phone Number" id="contactPhone" required/>
            </div>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Code of Conduct</h4>
            <div>
                By checking the box, you agree to the
                <a href="/members/fwd/code_of_conduct.php" target="_blank">FriendCon Code of Conduct
                    <i class="fa fa-external-link-alt"></i></a>.
            </div>
            <div class="acknowledge-color">
                <input id="code-of-conduct-checkbox" type="checkbox" value="0">
                <b>I Acknowledge and Agree to the FriendCon Code of Conduct</b>
            </div>
            <hr/>

            <div id="message-wrapper" class="alert" style="display:none">
                <span class="fa fa-info-circle"></span>
                <span id="message-text"></span>
            </div>

            <div class="form-group">
                <a href="/members/index.php" class="btn btn-default">Already Signed Up?</a>
                <button type="button" class="btn btn-default pull-right" id="submit" onclick="submitForm()">
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
    var $name = $('#name');
    var $phone = $('#phone');
    var $email = $('#email');
    var $pass1 = $('#pass1');
    var $pass2 = $('#pass2');
    var $animal = $('#animal');
    var $booze = $('#booze');
    var $nerdism = $('#nerdism');
    var $contactName = $('#contactName');
    var $contactPhone = $('#contactPhone');
    var $codeOfConduct = $('#code-of-conduct-checkbox');
    var $passValidation = $('#password-validation');
    var $messageWrapper = $('#message-wrapper');
    var $messageText = $('#message-text');

    function isAcknowledged() {
        return $codeOfConduct.is(':checked');
    }

    function isValidPassword() {
        var text1 = $pass1.val();
        var text2 = $pass2.val();
        return text1 === text2 && text1 !== "";
    }

    function checkThatPasswordsMatch(isFirstTextbox) {
        if (isFirstTextbox && $pass2.val() === "") {
            return; //short-circuit if it's the first password textbox and the second is blank
        }

        if (isValidPassword()) {
            $pass1.removeClass('invalid');
            $pass2.removeClass('invalid');
            $passValidation.html("");
            return;
        }

        $pass1.addClass('invalid');
        $pass2.addClass('invalid');
        if ($pass1.val() === "" || $pass2.val() === "") {
            $passValidation.html("Password is required!");
        } else {
            $passValidation.html("Passwords do not match!");
        }
    }

    function successMessage(text) {
        updateMessage(text, "alert-success");
    }

    function errorMessage(text) {
        updateMessage(text, "alert-danger");
    }

    function infoMessage(text) {
        updateMessage(text, "alert-info");
    }

    function updateMessage(text, wrapperClass) {
        clearMessage();
        $messageText.text(text);
        $messageWrapper.addClass(wrapperClass);
        $messageWrapper.show();
    }

    function clearMessage() {
        $messageWrapper.hide();
        $messageWrapper.removeClass("alert-info alert-danger alert-success");
        $messageText.empty();
    }

    function buildFormData() {
        var params = [];
        params.push("name=" + $name.val());
        params.push("phone=" + $phone.val());
        params.push("email=" + $email.val());
        params.push("password=" + $pass1.val());
        params.push("favoriteAnimal=" + $animal.val());
        params.push("favoriteBooze=" + $booze.val());
        params.push("favoriteNerdism=" + $nerdism.val());
        params.push("emergencyCN=" + $contactName.val());
        params.push("emergencyCNP=" + $contactPhone.val());
        return params.join("&");
    }

    function submitForm() {
        clearMessage();
        if (!isAcknowledged()) {
            errorMessage("You must agree to the Code of Conduct before proceeding.");
        } else if (!isValidPassword()) {
            errorMessage("Passwords must match!");
        } else {
            $.ajax({
                type: 'POST',
                url: "/members/api-v2/user/signup.php",
                data: buildFormData(),
                success: function(resp) {
                    successMessage("Successfully signed up!");
                },
                error: function(jqXHR) {
                    var error = jqXHR.responseJSON.error;
                    errorMessage(error);
                }
            });
        }
    }

    (function() {
        // When the phone number input loses focus, format the phone number, if possible
        formatPhoneNumberOnBlur($('#phone, #contactPhone'));
    })();
</script>
</body>
</html>
