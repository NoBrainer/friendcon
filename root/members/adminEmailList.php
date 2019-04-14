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

if (!$isAdmin) {
    die("You are not an admin! GTFO.");
}

// Get the user data
$query = $MySQLi_CON->query("SELECT * FROM users WHERE uid={$userSession}");
$userRow = $query->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];

$MySQLi_CON->close();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Email List for the Friends of Cons</title>
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
        <a class="btn btn-default" href="/members/adminEmailList.php" disabled>Email List</a>
    </div>
    <?php if ($isSuperAdmin) { ?>
        <div class="btn-group" role="group">
            <a class="btn btn-default" href="/members/superAdmin.php">SUPERadmin</a>
        </div>
    <?php } ?>
    <?php if ($isPointsEnabled) { ?>
        <div class="btn-group" role="group">
            <a class="btn btn-default" href="/members/points.php">Points</a>
        </div>
    <?php } ?>
</div>
<div class="container content-card wide">
    <h4>Email List</h4>
    <p>Here is a list of email addresses for all users with accounts on this site.</p>
    <p><b>IMPORTANT: When using this list, make sure you BCC these email addresses to hide the list from the
            recipients.</b></p>
    <textarea id="email-list" spellcheck="false"></textarea>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="/lib/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/underscore.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        // TODO: make this stored in a database or something
        var blacklist = ['hjguth@gmail.com'];

        setupEmailList();

        function setupEmailList() {
            var $emailList = $('#email-list');
            $emailList.empty();

            $emailList.on('focus', function() {
                this.select();
            });

            $.get('/utils/getusers.php?forEmailList')
                .done(function(resp) {
                    if (!(resp instanceof Array)) {
                        $emailList.text("Error loading emails");
                        return;
                    }

                    // Map the user objects into an array of email addresses
                    var emailArr = _.chain(resp)
                        .map(function(user) {
                            user = user || {};
                            return user.email;
                        })
                        .compact()
                        .reject(function(email) {
                            return email.match(/friendcon.com$/i) || _.contains(blacklist, email);
                        })
                        .value();

                    // Build the string to display
                    var emailStr = emailArr.join("; ");
                    $emailList.text(emailStr);
                });
        }

    });
</script>
</body>
</html>