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
$result = executeSqlForResult($MySQLi_CON, $query, 'i', $userSession);
$userRow = $result->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Check-In the Friends of Cons</title>
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
<script type="text/javascript" src="/members/lib/underscore/underscore-1.9.1.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var dataTableForUserTable;
        setupUserTable();

        function setupUserTable() {
            var $userTable = $('#user-table');
            $userTable.empty();
            $userTable.on('draw.dt', setupActionButtonClickHandlers);
            $userTable.on('order.dt', renumberRows);

            return $.ajax({
                type: 'GET',
                url: "/members/api-v2/user/getUsers.php",
                data: 'forCheckIn',
                statusCode: {
                    200: function(resp) {
                        var users = resp.data;
                        if (!(users instanceof Array)) {
                            $userTable.text("Error loading users");
                            return;
                        }

                        // Build up the data
                        var dataArr = [];
                        _.each(users, function(user) {
                            var dataRow = {
                                uid: user.uid,
                                name: user.name,
                                email: user.email,
                                isRegistered: user.isRegistered,
                                isPresent: user.isPresent
                            };
                            dataArr.push(dataRow);
                        });
                        dataArr.sort(function(a, b) {
                            return a.name.localeCompare(b);
                        });

                        function renderToggleButton(value, className, uid) {
                            var text = (value ? "YES" : "NO");
                            return '<button class="' + className + '" uid="' + uid + '">'
                                + text + '</button>';
                        }

                        var COLUMN = {
                            NUMBER: 0,
                            NAME: 1,
                            EMAIL: 2,
                            REGISTERED: 3,
                            PRESENT: 4
                        };

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
                                    title: "Checked In?", data: "isPresent",
                                    render: function(isPresent, type, row, meta) {
                                        return renderToggleButton(isPresent, 'present-toggle-btn', row.uid);
                                    }
                                }
                            ],
                            // Default order
                            order: [[COLUMN.REGISTERED, "desc"], [COLUMN.NAME, "asc"]],
                            // Data for the table
                            data: dataArr,
                            // Entries per page menu
                            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
                            // Default to showing all
                            displayLength: -1,
                            // HTML DOM
                            dom: '<"top"<"row"lf><"row"ip>>rt<"bottom"<"row"ip>>'
                        });
                    },
                    500: function(jqXHR) {
                        alert(jqXHR.responseJSON.error);
                    }
                }
            });
        }

        function renumberRows() {
            // Go through each row and re-number them
            _.each($('td.row-num'), function(row, i) {
                $(row).text(i + 1);
            });
        }

        var pendingUsers = [];

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
                if (isUserLocked(uid)) {
                    alert("Toggling this won't work until the last request finishes processing.");
                    return;
                }
                lockUser(uid);

                // Update the value in the table
                var row = dataTableForUserTable.row($row[0]);
                var data = row.data();
                data.isRegistered = (data.isRegistered ? NO : YES);
                row.invalidate();
                setupActionButtonClickHandlers();

                function alertMessageAndRevert(message) {
                    alert(message);
                    data.isRegistered = (data.isRegistered ? NO : YES);
                }

                // Make the ajax call
                $.ajax({
                    type: 'POST',
                    url: "/members/api-v2/registration/modifyRegistration.php",
                    data: "toggleRegistered=true&uid=" + uid,
                    statusCode: {
                        200: function(resp) {
                            data.isRegistered = resp.data.isRegistered;
                            data.isPresent = resp.data.isPresent;
                        },
                        304: function() {
                            alertMessageAndRevert("No changes.");
                        },
                        400: function(jqXHR) {
                            alertMessageAndRevert(jqXHR.responseJSON.error);
                        },
                        403: function() {
                            alertMessageAndRevert("403 Forbidden: Must be an admin to do that");
                        },
                        500: function(jqXHR) {
                            alertMessageAndRevert(jqXHR.responseJSON.error);
                        }
                    },
                    complete: function() {
                        row.invalidate();
                        setupActionButtonClickHandlers();
                        unlockUser(uid);
                    }
                });
            });

            // Click handler for the present toggle button
            $('.present-toggle-btn').off().on('click', function(e) {
                var $btn = $(this);
                var $row = $btn.closest('tr');
                var uid = $btn.attr('uid') || "";

                // Do nothing if it's already processing for this row
                if (isUserLocked(uid)) {
                    alert("Toggling this won't work until the last request finishes processing.");
                    return;
                }
                lockUser(uid);

                // Update the value in the table
                var row = dataTableForUserTable.row($row[0]);
                var data = row.data();
                data.isPresent = (data.isPresent ? NO : YES);
                row.invalidate();
                setupActionButtonClickHandlers();

                function alertMessageAndRevert(message) {
                    alert(message);
                    data.isPresent = (data.isPresent ? NO : YES);
                }

                // Make the ajax call
                $.ajax({
                    type: 'POST',
                    url: "/members/api-v2/registration/modifyRegistration.php",
                    data: "togglePresent=true&uid=" + uid,
                    statusCode: {
                        200: function(resp) {
                            data.isRegistered = resp.data.isRegistered;
                            data.isPresent = resp.data.isPresent;
                            if (data.isPresent) {
                                sortUser(uid);
                            } else {
                                unlockUser(uid);
                            }
                        },
                        304: function() {
                            alertMessageAndRevert("No changes.");
                        },
                        400: function(jqXHR) {
                            alertMessageAndRevert(jqXHR.responseJSON.error);
                        },
                        403: function() {
                            alertMessageAndRevert("403 Forbidden: Must be an admin to do that");
                        },
                        500: function(jqXHR) {
                            alertMessageAndRevert(jqXHR.responseJSON.error);
                        }
                    },
                    complete: function() {
                        row.invalidate();
                        setupActionButtonClickHandlers();
                    }
                });
            });
        }

        function sortUser(uid) {
            $.ajax({
                type: 'GET',
                url: "/members/api-v2/registration/sortUser.php",
                data: "uid=" + uid,
                success: function(resp) {
                    alert("Sorted user to house: " + resp.data);
                    window.location.reload(true);
                },
                error: function(jqXHR) {
                    var error = jqXHR.responseJSON.error;
                    console.log(error);
                    alert(error);
                },
                complete: function() {
                    unlockUser(uid);
                }
            });
        }

        // Helper functions for locking/unlocking actions for a user
        function isUserLocked(uid) {
            return _.contains(pendingUsers, uid);
        }

        function lockUser(uid) {
            pendingUsers = _.union(pendingUsers, [uid]);
        }

        function unlockUser(uid) {
            pendingUsers = _.without(pendingUsers, uid);
        }
    });
</script>
</body>
</html>