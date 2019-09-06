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

if (!$isAdmin) {
    die("You are not an admin! GTFO.");
}

// Get the user data
$result = $MySQLi_CON->query("SELECT * FROM users WHERE uid={$userSession}");
$userRow = $result->fetch_array();

// User Information
$name = $userRow['name'];
$emailAddress = $userRow['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Team Sorting the Friends of Cons</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/datatables/datatables-1.10.12.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body class="admin-check-in admin-team-sort">
<?php include('header.php'); ?>
<br/>
<br/>
<br/>
<br/>
<div class="container content-card wide">
    <span>Admin Navigation:</span>
    <div class="btn-group" role="group">
        <a class="btn btn-default" href="/members/adminCheckIn.php">Check-In</a>
        <a class="btn btn-default" href="/members/adminTeamSort.php" disabled>Team Sorting</a>
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
    <div class="btn-group pull-right" role="group">
        <a class="btn btn-default" id="sort-the-unsorted">Sort the Unsorted</a>
    </div>
    <h4>Sort into Teams (For Checked-In Friends)</h4>
    <table id="user-table"></table>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script type="text/javascript" src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript" src="/members/lib/datatables/datatables-1.10.12.min.js"></script>
<script type="text/javascript" src="/members/lib/datatables/datatables.dataSourcePlugins.js"></script>
<script type="text/javascript" src="/members/lib/underscore/underscore-1.9.1.min.js""></script>
<script type="text/javascript">
    $(document).ready(function() {

        var dataTableForUserTable;
        setupUserTable();

        function setupUserTable() {
            var $userTable = $('#user-table');
            $userTable.off().empty();
            $userTable.on('draw.dt', setupActionButtonClickHandlers);
            $userTable.on('order.dt', renumberRows);

            return $.get('/members/utils/getusers.php?forTeamSort')
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
                            housename: user.housename
                        };
                        dataArr.push(dataRow);
                    });
                    dataArr.sort(function(a, b) {
                        return a.housename.localeCompare(b);
                    });

                    function renderBooleanHumanReadable(bool) {
                        return bool ? "YES" : "NO";
                    }

                    function renderToggleButton(value, className, uid) {
                        var text = (value ? "YES" : "NO");
                        return '<button class="' + className + '" uid="' + uid + '">' + text + '</button>';
                    }

                    function renderTeamDropdown(value, uid) {
                        //TODO: pull teams from the database
                        var options = ['Unsorted', 'Baratheon', 'Lannister', 'Martell', 'Stark', 'Maesters'];
                        var optionHtml = _.reduce(options, function(html, team, i) {
                            html += '<option value="' + team + '">' + team + '</option>';
                            return html;
                        }, "");
                        return '<select class="team-dropdown" uid="' + uid + '" value="' + value + '">' + optionHtml + '</select>';
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
                                title: "House",
                                data: "housename",
                                className: "house-cell",
                                orderDataType: "team-select",
                                render: function(house, type, row, meta) {
                                    return renderTeamDropdown(house, row.uid);
                                }
                            }
                        ],
                        // Default order
                        order: [[3, "asc"], [1, "asc"]],
                        // Data for the table
                        data: dataArr,
                        // Entries per page menu
                        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
                        // Default to showing all
                        displayLength: -1,
                        // HTML DOM
                        dom: '<"top"<"row"lf><"row"ip>>rt<"bottom"<"row"ip>>'
                    });

                    // Fix the starting state for the house dropdown
                    _.each($('.house-cell select'), function(select) {
                        var $dropdown = $(select);
                        $dropdown.val($dropdown.attr('value'));
                    });
                });
        }

        function renumberRows() {
            // Go through each row and re-number them
            $.each($('td.row-num'), function(i, row) {
                $(row).text(i + 1);
            });
        }

        function setupActionButtonClickHandlers() {
            renumberRows();

            $('#sort-the-unsorted').on('click', function() {
                $.get('/members/utils/getusers.php?forTeamSort')
                    .done(function(users) {
                        _.each(users, function(user) {
                            if (user.houseid === "0") {
                                queueTeamSort(user.uid);
                            }
                        });
                    });
            });

            $('.team-dropdown').on('change', function(e) {
                var $dropdown = $(this);
                var uid = $dropdown.attr('uid');
                var housename = $dropdown.val();
                $.ajax({
                    url: "/members/utils/sortuser.php?uid=" + uid + "&housename=" + housename,
                    type: "GET"
                });
            });
            //TODO: allow multi-edit and/or drag-and-drop
        }

        var uidQueue = [];
        var sortPromise = null;

        function queueTeamSort(uid) {
            if (_.isNull(sortPromise)) {
                sortPromise = $.get("/members/utils/sortuser.php?uid=" + uid)
                    .always(nextTeamSort);
            } else {
                uidQueue = _.union(uidQueue, [uid]);
            }
        }

        function nextTeamSort() {
            if (_.isEmpty(uidQueue)) {
                window.location.reload(true);
                return;
            }

            // Get the next uid from the queue
            var uid = uidQueue[0];
            uidQueue = _.without(uidQueue, uid);

            // Make an ajax call
            sortPromise = $.get("/members/utils/sortuser.php?uid=" + uid)
                .always(nextTeamSort);
        }
    });
</script>
</body>
</html>