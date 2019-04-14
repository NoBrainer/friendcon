<?php
session_start();
$userSession = $_SESSION['userSession'];

if (!isset($userSession) || $userSession == "") {
    // If not logged in, go to registration index
    header("Location: /members/index.php");
    exit;
}
include_once('../utils/dbconnect.php');
include_once('../utils/checkadmin.php');
include_once('../utils/check_app_state.php');

if (!$isSuperAdmin) {
    die("You are not a super admin! GTFO.");
}

// Get the user data
$query = $MySQLi_CON->query("SELECT * FROM users WHERE uid={$userSession}");
$userRow = $query->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>FriendCon Super Admin</title>
    <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../lib/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
    <link href="../css/datatables.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>

<body class="admin-check-in">
<?php include_once('header.php'); ?>
<br/>
<br/>
<br/>
<br/>
<div class="container content-card wide">
    <span>Admin Navigation:</span>
    <div class="btn-group" role="group">
        <a class="btn btn-default" href="/members/adminCheckIn.php">Check-In</a>
        <a class="btn btn-default" href="/members/adminTeamSort.php">Team Sorting</a>
        <a class="btn btn-default" href="/members/adminEmailList.php">Email List</a>
    </div>
    <?php if ($isSuperAdmin) { ?>
        <div class="btn-group" role="group">
            <a class="btn btn-default" href="/members/superAdmin.php" disabled>SUPERadmin</a>
        </div>
    <?php } ?>
    <?php if ($isPointsEnabled) { ?>
        <div class="btn-group" role="group">
            <a class="btn btn-default" href="/members/points.php">Points</a>
        </div>
    <?php } ?>
</div>
<div class="container content-card wide">
    <h4>SUPER ADMIN PAGE: Modify the App State</h4>
    <table class="table">
        <tr>
            <td class="title-cell">General</td>
            <td></td>
        </tr>
        <tr>
            <td>Convention Month (1-12)</td>
            <td><input type="text" id="con-month"/></td>
        </tr>
        <tr>
            <td>Convention Day (1-31)</td>
            <td><input type="text" id="con-day"/></td>
        </tr>
        <tr>
            <td>Convention Year</td>
            <td><input type="text" id="con-year"/></td>
        </tr>
        <tr>
            <td>Enable Registration</td>
            <td><input type="checkbox" id="enable-registration"/></td>
        </tr>
        <tr>
            <td>Enable Points</td>
            <td><input type="checkbox" id="enable-points"/></td>
        </tr>
        <tr>
            <td><span id="message" style="float:right;"></span></td>
            <td>
                <button type="button" id="save-changes">Save</button>
            </td>
        </tr>
    </table>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="/lib/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/underscore.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        setup();

        function setup() {
            var $conMonthTextbox = $('#con-month');
            var $conDayTextbox = $('#con-day');
            var $conYearTextbox = $('#con-year');
            var $enableRegistrationCheckbox = $('#enable-registration');
            var $enablePointsCheckbox = $('#enable-points');
            var $saveButton = $('#save-changes');
            var $message = $('#message');

            // Get the values from the PHP variables
            var conMonth = (<?php echo $conMonth; ?>);
            var conDay = (<?php echo $conDay; ?>);
            var conYear = (<?php echo $conYear; ?>);
            var isRegistrationEnabled = (<?php echo $isRegistrationEnabled; ?> === 1
        )
            ;
            var isPointsEnabled = (<?php echo $isPointsEnabled; ?> === 1
        )
            ;

            // Set the starting state
            $conMonthTextbox.val(conMonth);
            $conDayTextbox.val(conDay);
            $conYearTextbox.val(conYear);
            $enableRegistrationCheckbox.prop('checked', isRegistrationEnabled);
            $enablePointsCheckbox.prop('checked', isPointsEnabled);

            $saveButton.click(function _onClick(e) {
                // Build up the parameters
                var params = [];
                params.push('conMonth=' + $conMonthTextbox.val());
                params.push('conDay=' + $conDayTextbox.val());
                params.push('conYear=' + $conYearTextbox.val());
                params.push($enableRegistrationCheckbox.is(':checked') ? 'enableRegistration' : 'disableRegistration');
                params.push($enablePointsCheckbox.is(':checked') ? 'enablePoints' : 'disablePoints');

                if (isInvalidYear($conYearTextbox.val())) {
                    $message.text("Changes ignored. Cannot edit past/future/invalid years.");
                    return;
                }

                // Make the ajax call
                $.ajax({
                    type: 'POST',
                    url: '/utils/set_app_state.php',
                    data: params.join('&')
                }).done(function _onSuccess(resp) {
                    $message.text(resp);
                }).fail(function _onFailure(resp) {
                    $message.text(resp);
                });
            });
        }

        function isInvalidYear(year) {
            try {
                year = parseInt(year);
                if (isNaN(year)) return true;
            } catch(e) {
                return true;
            }

            // The year is invalid if it is not the current year
            var currentYear = new Date().getFullYear();
            return year !== currentYear;
        }

    });
</script>
</body>
</html>