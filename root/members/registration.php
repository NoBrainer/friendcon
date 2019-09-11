<?php
session_start();
$userSession = $_SESSION['userSession'];

// Short-circuit forwarding
include('utils/reroute_functions.php');
if (forwardHttps() || forwardIndexIfLoggedOut()) {
    exit;
}

include('utils/dbconnect.php');
include('utils/checkadmin.php');
include('utils/check_app_state.php');
include('utils/sql_functions.php');
include('utils/paypal.php');

if (!$isRegistrationEnabled) {
    // Not ready for con registration
    header("Location: /members/index.php");
    exit;
}

// Get the user data
$query = "SELECT * FROM users WHERE uid = ?";
$result = prepareSqlForResult($MySQLi_CON, $query, 'i', $userSession);
$userRow = $result->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
$points = $userRow['upoints'];
$houseName = $userRow['housename'];
$isRegistered = $userRow['isRegistered'];
$agreeToTerms = $userRow['agreeToTerms'] ? $userRow['agreeToTerms'] : "n/a";
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/fontawesome/css/fontawesome-all.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body class="registration">
<?php include('header.php'); ?>

<div class="container content">

    <div class="container content-card">
        <form method="post" id="update-form">
            <h3 class="form-signin-heading center">FriendCon <?php echo $conYear; ?> Registration</h3>
            <hr/>

            <h4 class="form-signin-heading center">FriendCon Code of Conduct</h4>
            <div>
                <span>By checking the box, you agree to the</span>
                <b><a href="/members/fwd/code_of_conduct.php" target="_blank">FriendCon Code of Conduct</a></b>
                <span>.</span>
            </div>
            <div class="acknowledge-color">
                <label style="cursor:pointer">
                    <input id="acknowledge-code-of-conduct" type="checkbox" value="0" name="agree" onchange="updateAcknowledgement()">
                    <b>I Acknowledge and Agree to the FriendCon Code of Conduct</b>
                </label>
            </div>
            <div class="last-acknowledged">
                <span>[Last acknowledged:</span>
                <span class="timestamp"><?php echo $agreeToTerms; ?></span>
                <span>]</span>
            </div>

            <div class="hide-until-acknowledged" style="display:none">
                <hr/>
                <div id="paypal-section" style="<?php if($isRegistered) echo 'display:none'; ?>">
                    <h4 class="form-signin-heading center">FriendCon Membership</h4>
                    <p>By paying for FriendCon membership, you gain access to the convention space and activities for
                        the duration of FriendCon <?php echo $conYear; ?> (Friday through Saturday).</p>
                    <p>FriendCon 2019 Membership: $<?php echo $badgePrice; ?></p>
                    <div id="paypal-button-container"></div>
                </div>
                <div>
                    <span id="registration-message"></span>
                    <span id="registration-spinner" style="display:none">
                        <i class="fa fa-spinner fa-spin"></i>
                    </span>
                </div>
                <div id="room-block-section" style="<?php if(!$isRegistered) echo 'display:none'; ?>">
                    <h4 class="form-signin-heading center">FriendCon Hotel Room Block</h4>
                    <p>You can find information for registration and the <a href="/tickets/" target="_blank">hotel room
                            block <i class="fa fa-external-link-alt"></i></a> on the main site.</p>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $PAYPAL_CLIENT_ID; ?>&disable-funding=credit,card"></script>
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

    function initializePayPalForm() {
        paypal.Buttons({
            createOrder: function(data, actions) {
                // Set up the transaction
                return actions.order.create({
                    application_context: {
                        brand_name: 'FriendCon',
                        user_action: 'PAY_NOW'
                    },
                    intent: 'CAPTURE',
                    purchase_units: [{
                        amount: {
                            currency_code: 'USD',
                            value: '<?php echo $badgePrice; ?>'
                        },
                        description: "FriendCon <?php echo $conYear; ?> Membership",
                        soft_descriptor: "FriendCon Membership" //shows up for transaction (prefix: PayPal *)
                    }]
                });
            },
            onApprove: function(data, actions) {
                setMessage("[ Processing... ]");
                $('#registration-spinner').show();
                $('#paypal-section').hide();

                // Capture the funds from the transaction
                return actions.order.capture().then(function(details) {
                    // Handle success
                    if (details.intent !== 'CAPTURE' || details.status !== 'COMPLETED') {
                        alert("Something went wrong with your payment. Please copy the following text and send it to " +
                            "admin@friendcon.com for assistance: [" + JSON.stringify(details) + "]");
                    } else {
                        saveOrder(details.id);
                    }
                    return this;
                });
            }
        }).render('#paypal-button-container');
    }

    function saveOrder(orderId) {
        $.ajax({
            type: 'POST',
            url: '/members/utils/paypal_handle_change.php',
            data: 'orderId=' + orderId
        })
            .done(function(resp) {
                if (resp === "Registration complete!") {
                    setMessage("[ Registration complete! ]");
                    $('#room-block-section').show();
                } else {
                    setMessage("Error registering... [" + resp + "]");
                }
            })
            .fail(function(resp) {
                setMessage("Error registering... [" + resp + "]");
            });
    }

    function setMessage(str) {
        $('#registration-message').text(str);
        $('#registration-spinner').hide();
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

        initializePayPalForm();
    })();
</script>
</body>
</html>