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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Check-In the Friends of Cons</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/datatables/datatables-1.10.12.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
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
        <a class="btn btn-default" href="/members/adminCheckIn.php" disabled>Check-In</a>
        <a class="btn btn-default" href="/members/adminTeamSort.php">Team Sorting</a>
        <a class="btn btn-default" href="/members/adminEmailList.php">Email List</a>
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
    <h4>Check-In Friends</h4>
    <table id="user-table"></table>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script type="text/javascript" src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript" src="/members/lib/datatables/datatables-1.10.12.min.js"></script>
<script type="text/javascript" src="/members/lib/underscore/underscore-1.9.1.min.js""></script>
<script type="text/javascript">
    $(document).ready(function() {
        var dataTableForUserTable;
        setupUserTable();

        function setupUserTable() {
            var $userTable = $('#user-table');
            $userTable.empty();
            $userTable.on('draw.dt', setupActionButtonClickHandlers);
            $userTable.on('order.dt', renumberRows);

            return $.get('/members/utils/getusers.php?forCheckIn')
                .done(function(resp) {
                    if (!(resp instanceof Array)) {
                        $userTable.text("Error loading users");
                        return;
                    }

                    // Build up the data
                    var dataArr = [];
                    $.each(resp, function(i, user) {
                        var dataRow = {
                            uid: user.uid,
                            name: user.name,
                            email: user.email,
                            isRegistered: user.isRegistered,
                            isPaid: user.isPaid,
                            isPresent: user.isPresent
                        };
                        dataArr.push(dataRow);
                    });
                    dataArr.sort(function(a, b) {
                        return a.name.localeCompare(b);
                    });

                    function renderBooleanHumanReadable(bool) {
                        return bool ? "YES" : "NO";
                    }

                    function renderToggleButton(value, className, uid) {
                        var text = (value ? "YES" : "NO");
                        return '<button class="' + className + '" uid="' + uid + '">'
                            + text + '</button>';
                    }

                    // Use DataTables for a fancy table
                    dataTableForUserTable = $userTable.DataTable({
                        // Don't do any fancy auto resizing of columns
                        autoWidth: false,
                        // Column definitions
                        columns: [
                            //placeholder cell for row number
                            {title: "#", data: null, orderable: false, className: "row-num"},
                            {title: "Name", data: "name"},
                            {title: "Email", data: "email"},
                            {
                                title: "Registered?", data: "isRegistered",
                                render: function(isRegistered, type, row, meta) {
                                    return renderToggleButton(isRegistered, 'registered-toggle-btn', row.uid);
                                }
                            },
                            {
                                title: "Paid?", data: "isPaid",
                                render: function(isPaid, type, row, meta) {
                                    return renderToggleButton(isPaid, 'paid-toggle-btn', row.uid);
                                }
                            },
                            {
                                title: "Checked In?", data: "isPresent",
                                render: function(isPresent, type, row, meta) {
                                    return renderToggleButton(isPresent, 'present-toggle-btn', row.uid);
                                }
                            }
                        ],
                        // Default order: isPaid, isRegistered, name
                        order: [[4, "desc"], [3, "desc"], [1, "asc"]],
                        // Data for the table
                        data: dataArr,
                        // Entries per page menu
                        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
                        // Default to showing all
                        displayLength: -1,
                        // HTML DOM
                        dom: '<"top"<"row"lf><"row"ip>>rt<"bottom"<"row"ip>>'
                    });
                });
        }

        function renumberRows() {
            // Go through each row and re-number them
            $.each($('td.row-num'), function(i, row) {
                $(row).text(i + 1);
            });
        }

        var processingRegister = [];
        var processingPaid = [];
        var processingPresent = [];

        function setupActionButtonClickHandlers() {
            renumberRows();

            var YES = 1;
            var NO = 0;

            // Click handler for the registered toggle button
            $('.registered-toggle-btn').off().on('click', function(e) {
                var $btn = $(this);
                var $row = $btn.closest('tr');
                var uid = $btn.attr('uid') || "";

                // Do nothing if it's already processing for this row
                if (isTogglingRegisteredLocked(uid)) {
                    alert("Toggling this won't work until the last request finishes processing.");
                    return;
                }
                lockTogglingIsRegistered(uid);

                // Update the value in the table
                var row = dataTableForUserTable.row($row[0]);
                var data = row.data();
                data.isRegistered = (data.isRegistered ? NO : YES);
                row.invalidate();
                setupActionButtonClickHandlers();

                // Make the ajax call
                $.ajax({
                    url: "/members/utils/modifyregistration.php",
                    type: 'POST',
                    data: "toggleRegistered=true&uid=" + uid
                }).done(function(resp) {
                    if (typeof resp == 'object') {
                        // Make the change
                        data.isRegistered = resp.isRegistered;
                        data.isPaid = resp.isPaid;
                        data.isPresent = resp.isPresent;
                    } else {
                        // Print the error message and revert the change
                        alert(resp);
                        data.isRegistered = (data.isRegistered ? NO : YES);
                    }
                    row.invalidate();
                    setupActionButtonClickHandlers();
                }).always(function() {
                    unlockTogglingIsRegistered(uid);
                });
            });

            // Click handler for the paid toggle button
            $('.paid-toggle-btn').off().on('click', function(e) {
                var $btn = $(this);
                var $row = $btn.closest('tr');
                var uid = $btn.attr('uid') || "";

                // Do nothing if it's already processing for this row
                if (isTogglingPaidLocked(uid)) {
                    alert("Toggling this won't work until the last request finishes processing.");
                    return;
                }
                lockTogglingIsPaid(uid);

                // Update the value in the table
                var row = dataTableForUserTable.row($row[0]);
                var data = row.data();
                data.isPaid = (data.isPaid ? NO : YES);
                row.invalidate();
                setupActionButtonClickHandlers();

                // Make the ajax call
                $.ajax({
                    url: "/members/utils/modifyregistration.php",
                    type: 'POST',
                    data: "togglePaid=true&uid=" + uid
                }).done(function(resp) {
                    if (typeof resp == 'object') {
                        // Make the change
                        data.isRegistered = resp.isRegistered;
                        data.isPaid = resp.isPaid;
                        data.isPresent = resp.isPresent;
                    } else {
                        // Print the error message and revert the change
                        alert(resp);
                        data.isPaid = (data.isPaid ? NO : YES);
                    }
                    row.invalidate();
                    setupActionButtonClickHandlers();
                }).always(function() {
                    unlockTogglingIsPaid(uid);
                });
            });

            // Click handler for the present toggle button
            $('.present-toggle-btn').off().on('click', function(e) {
                var $btn = $(this);
                var $row = $btn.closest('tr');
                var uid = $btn.attr('uid') || "";

                // Do nothing if it's already processing for this row
                if (isTogglingPresentLocked(uid)) {
                    alert("Toggling this won't work until the last request finishes processing.");
                    return;
                }
                lockTogglingIsPresent(uid);

                // Update the value in the table
                var row = dataTableForUserTable.row($row[0]);
                var data = row.data();
                data.isPresent = (data.isPresent ? NO : YES);
                row.invalidate();
                setupActionButtonClickHandlers();

                // Make the ajax call
                $.ajax({
                    url: "/members/utils/modifyregistration.php",
                    type: 'POST',
                    data: "togglePresent=true&uid=" + uid
                }).done(function(resp) {
                    if (typeof resp == 'object') {
                        // Make the change
                        data.isRegistered = resp.isRegistered;
                        data.isPaid = resp.isPaid;
                        data.isPresent = resp.isPresent;
                        if (data.isPresent) {
                            sortUser(uid);
                        } else {
                            unlockTogglingIsPresent(uid);
                        }
                    } else {
                        // Print the error message and revert the change
                        alert(resp);
                        data.isPresent = (data.isPresent ? NO : YES);

                        unlockTogglingIsPresent(uid);
                    }
                    row.invalidate();
                    setupActionButtonClickHandlers();
                });
            });
        }

        function sortUser(uid) {
            $.ajax({
                url: "/members/utils/sortuser.php",
                type: "GET",
                data: "uid=" + uid
            }).done(function(resp) {
                alert("Sorted user to house: " + resp);
                window.location.reload(true);
            }).fail(function(resp) {
                console.log(resp);
                alert(resp);
            }).always(function() {
                unlockTogglingIsPresent(uid);
            });
        }

        // Helper functions for locking/unlocking isRegistered toggling
        function isTogglingRegisteredLocked(uid) {
            return _.contains(processingRegister, uid);
        }

        function lockTogglingIsRegistered(uid) {
            processingRegister = _.union(processingRegister, [uid]);
        }

        function unlockTogglingIsRegistered(uid) {
            processingRegister = _.without(processingRegister, uid);
        }

        // Helper functions for locking/unlocking isPaid toggling
        function isTogglingPaidLocked(uid) {
            return _.contains(processingPaid, uid);
        }

        function lockTogglingIsPaid(uid) {
            processingPaid = _.union(processingPaid, [uid]);
        }

        function unlockTogglingIsPaid(uid) {
            processingPaid = _.without(processingPaid, uid);
        }

        // Helper functions for locking/unlocking isPresent toggling
        function isTogglingPresentLocked(uid) {
            return _.contains(processingPresent, uid);
        }

        function lockTogglingIsPresent(uid) {
            processingPresent = _.union(processingPresent, [uid]);
        }

        function unlockTogglingIsPresent(uid) {
            processingPresent = _.without(processingPresent, uid);
        }

    });
</script>
</body>
</html>