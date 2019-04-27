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

if (!$isRegistrationEnabled) {
    // Not ready for con registration
    header("Location: /members/index.php");
    exit;
}

// Get the user data
$query = $MySQLi_CON->query("SELECT * FROM users WHERE uid={$userSession}");
$userRow = $query->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
$points = $userRow['upoints'];
$houseName = $userRow['housename'];
$isRegistered = $userRow['isRegistered'];
$isPaid = $userRow['isPaid'];
$agreeToTerms = $userRow['agreeToTerms'] ? $userRow['agreeToTerms'] : "n/a";

//Check user payment status
if ($isPaid == 1) {
    // If paid, go to members page
    header("Location: /members/home.php");
    exit;
}

$MySQLi_CON->close();
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body class="registration">
<?php include_once('header.php'); ?>

<div class="container content">

    <div class="container content-card">
        <form method="post" id="update-form">
            <h3 class="form-signin-heading center">FriendCon <?php echo $conYear; ?> Registration</h3>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Code of Conduct</h4>
            <div>
                By checking the box, you agree to the <b><a href="/members/fwd/code_of_conduct.php"
                                                            target="_blank">FriendCon Code of Conduct</a></b>.
            </div>
            <div class="acknowledge-color">
                <label style="cursor:pointer">
                    <input id="acknowledge-code-of-conduct" type="checkbox" value="0" name="agree"
                           onchange="updateAcknowledgement()">
                    <b>I Acknowledge and Agree to the FriendCon Code of Conduct</b>
                </label>
            </div>
            <div class="last-acknowledged">[Last acknowledged: <span
                        class="timestamp"><?php echo $agreeToTerms; ?></span>]
            </div>

            <div class="hide-until-acknowledged" style="display:none">
                <hr/>
                <h4 class="form-signin-heading center">Hotel Room Block</h4>
                <div>
                    <p>Joining the room block online*:</p>
                    <ol>
                        <li>Go to the hotel website: <a href="https://statecollege.place.hyatt.com" target="_new">Hyatt
                                Place State College</a></li>
                        <li>Pick the dates (July 19-21)</li>
                        <li>Click on the <b>Special Rates</b> dropdown</li>
                        <li>Pick <b>Corporate or Group Code</b></li>
                        <li>Enter the group code: <b>G-FRCO</b></li>
                    </ol>
                    <p>*If reserving your room over the phone, make sure to mention the group code.</p>
                </div>

                <h4 class="form-signin-heading center">TODO</h4>
                <div>
                    [UNDER CONSTRUCTION] Make sure to mention that people are paying for FriendCon membership.
                </div>

                <div class="payment-section" style="display:none">
                    <?php
                    /*
                    <hr/>
                    <h4 id="payment-title" class="form-signin-heading center">Basic Badge!</h4>
                    <h5 id="payment-description" class="form-signin-heading center">*Everything you need! (Excludes Pay2Win cosmetics)</h5>
                    <div class="update-registration-btn-row">
                        <a id="paypal-btn" class="btn btn-primary" href="https://www.paypal.me/TylarN" target="_blank">Pay via PayPal</a>
                    </div>
                    */
                    ?>
                    <hr/>
                    <h4 id="payment-title" class="form-signin-heading center">Payment FAQ</h4>
                    <div class="note"><b>How do I pay?</b> Payments must be made when you check-in at FriendCon this
                        year.
                    </div>
                    <?php
                    /*
                    <div class="note"><b>Who is TylarN, and why am I paying him for this?</b> He is our fearless leader, and he deals with our money.</div>
                    <div class="note"><b>How do I pre-order multiple shirts?</b> You can't. Pre-ordering is limited to one per attendee.</div>
                    <div class="note"><b>Can I still buy a t-shirt if I don't pre-order one?</b> Yes! We plan on ordering and selling extra shirts. Keep in mind that pre-ordering is the only way to guarantee that we will have your size.</div>
                    <div class="note"><b>How do I pay for multiple people?</b> Each attendee must sign up for an account on FriendCon.com and register via this form. Once that is done, manually increase your PayPal payment amount and include a note in your PayPal payment with all of your names.</div>
                    */
                    ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript">
    var $acknowledgeCheckbox;

    function initializeForm(acknowledgedCode) {
        $acknowledgeCheckbox.prop('checked', acknowledgedCode);
        updateSectionVisibility(acknowledgedCode);
    }

    function updateAcknowledgement() {
        updateSectionVisibility();
        saveAcknowledgement();
    }

    function updateSectionVisibility(acknowledgedCode) {
        acknowledgedCode = acknowledgedCode || $acknowledgeCheckbox.is(':checked');

        // Hide most sections until the code of conduct is acknowledged
        $('.hide-until-acknowledged').toggle(acknowledgedCode);

        // Show the payment section for registered users and update the contents
        //TODO
    }

    function toggleButtonsForSaving() {
        $('#saving-btn').show();
        $('#save-btn,#done-saving-btn').hide();
    }

    function toggleButtonsForDoneSaving() {
        $('#done-saving-btn').show();
        $('#saving-btn,#save-btn').hide();
    }

    function toggleSaveButtonsForChanges() {
        $('#save-btn').show();
        $('#saving-btn,#done-saving-btn').hide();
    }

    function saveAcknowledgement() {
        if (!$acknowledgeCheckbox.is(':checked')) {
            return; //don't save unless the checkbox is checked
        }
        $.ajax({
            type: 'POST',
            url: '/members/utils/modifyregistration.php',
            data: "uid=<?php echo $userSession; ?>&agreeToTerms"
        })
            .done(function(resp) {
                $('.last-acknowledged .timestamp').text(resp.agreeToTerms);
            });
    }

    function updateRegistration() {
        //TODO: get registration from checkbox or something
        var setRegistered = 1;

        var params = [];
        params.push("setRegistered=" + setRegistered);
        params.push("uid=<?php echo $userSession; ?>");

        return $.ajax({
            type: 'POST',
            url: '/members/utils/modifyregistration.php',
            data: params.join('&')
        }).done(function(resp) {
            //console.log(resp);
        });
    }

    //Run things after page renders
    (function() {

        $acknowledgeCheckbox = $('#acknowledge-code-of-conduct');

        // Check to see if the user agreed to the terms within the past 30 days
        var now = new Date();
        var agreeToTermsTimestamp = "<?php echo $agreeToTerms; ?>";
        var agreeToTermsDate = new Date(agreeToTermsTimestamp);
        var THIRTY_DAYS = 1000 * 86400 * 30;
        var agreedRecently = now - THIRTY_DAYS < agreeToTermsDate;

        initializeForm(agreedRecently);

    })();
</script>
</body>
</html>