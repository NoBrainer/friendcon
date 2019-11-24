<?php
session_start();
$userSession = $_SESSION['userSession'];

include('api-v2/internal/constants.php');
include('api-v2/internal/functions.php');
include('api-v2/internal/secrets/initDB.php');
include('api-v2/internal/checkAdmin.php');
include('api-v2/internal/checkAppState.php');

// Short-circuit forwarding
if (forwardHttps() || forwardIndexIfLoggedOut()) {
    exit;
}

if (!$isAdmin) {
    http_response_code($HTTP_FORBIDDEN);
    return;
}

// Get the user data
$query = "SELECT * FROM users WHERE uid = ?";
$result = executeSqlForResult($mysqli, $query, 'i', $userSession);
$userRow = $result->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Email List for the Friends of Cons</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/fontawesome/css/fontawesome-all.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/datatables/datatables-1.10.12.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body class="admin-check-in">
<?php include('header.php'); ?>
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
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script type="text/javascript" src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript" src="/members/lib/underscore/underscore-1.9.1.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        //TODO: make this stored in a database or something
        //TODO: or use a listserv instead
        var blacklist = ['hjguth@gmail.com'];

        setupEmailList();

        function setupEmailList() {
            var $emailList = $('#email-list');
            $emailList.empty();

            $emailList.on('focus', function() {
                this.select();
            });

            $.ajax({
                type: 'GET',
                url: "/members/api-v2/user/getUsers.php",
                data: "forEmailList",
                success: function(resp) {
                    var users = resp.data;
                    if (!(users instanceof Array)) {
                        $emailList.text("Error loading emails");
                        return;
                    }

                    // Map the user objects into an array of email addresses
                    var emailArr = _.chain(users)
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
                },
                error: function(jqXHR) {
                    $emailList.text("Error loading emails");
                    console.log(jqXHR.responseJSON);
                }
            });
        }

    });
</script>
</body>
</html>