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

// Get the user data
$userResult = $MySQLi_CON->query("SELECT u.email, u.name, u.uid, u.upoints, h.housename
	 FROM users u
	 JOIN house h ON u.houseid = h.houseid
	 WHERE uid={$userSession}");
if (!$userResult) {
    die("User query failed");
}
$userRow = $userResult->fetch_array();

$email = $userRow['email'];
$name = $userRow['name'];
$uid = $userRow['uid'];
$points = $userRow['upoints'];
$housename = $userRow['housename'];
$userResult->free_result();

// Prevent users from accessing this until they are in a house
if (!$isAdmin && $housename == "Unsorted") {
    die("You are not worthy of points! GTFO.");
}

// Get the list of users
$userListResult = $MySQLi_CON->query("SELECT u.name, u.uid FROM users u");
if (!$userListResult) {
    die("User list query failed");
}
$userList = [];
while ($row = $userListResult->fetch_array()) {
    $userList[] = $row;
}
$userListResult->free_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendCon - Points</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">
    <?php if ($isAdmin) { ?>
        <div class="container content-card wide">
            <span>Admin Navigation:</span>
            <div class="btn-group" role="group">
                <a class="btn btn-default" href="/members/adminCheckIn.php">Check-In</a>
                <a class="btn btn-default" href="/members/adminBadgeStats.php">Badge Stats</a>
                <a class="btn btn-default" href="/members/adminTeamSort.php">Team Sorting</a>
            </div>
            <?php if ($isSuperAdmin) { ?>
                <div class="btn-group" role="group">
                    <a class="btn btn-default" href="/members/superAdmin.php">SUPERadmin</a>
                </div>
            <?php } ?>
            <?php if ($isPointsEnabled) { ?>
                <div class="btn-group" role="group">
                    <a class="btn btn-default" href="/members/points.php" disabled>Points</a>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="container title-card">
        <h2>What's the Point? Yes.</h2>
    </div>

    <div class="container content-card">
        <h4>User Profile</h4>
        <table class="table">
            <tr>
                <td>Name:</td>
                <td><?php echo $name; ?></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><?php echo $email; ?></td>
            </tr>
            <tr>
                <td>House:</td>
                <td><?php echo $housename; ?></td>
            </tr>
            <tr>
                <td>Points:</td>
                <td id="my-points"><?php echo $points; ?></td>
            </tr>
        </table>
    </div>

    <div class="container content-card">
        <h4>Pending Requests</h4>
        <table id="pending-requests-table" class="table">
            <thead style="display:none">
            <th>From</th>
            <th>Points</th>
            <th>Actions</th>
            </thead>
            <tbody></tbody>
        </table>
        <div id="pending-requests-success" class="success"></div>
        <div id="pending-requests-error" class="error"></div>
    </div>

    <div class="container content-card">
        <h4>Send Points</h4>
        <table class="table">
            <tr>
                <td class="points-cell">
                    <span id="send-points-amount">0</span> Points
                </td>
                <td>
                    <div>
                        <button class="btn btn-default point-btn enforce-max" value="1" target="#send-points-amount">
                            +1
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="5" target="#send-points-amount">
                            +5
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="10" target="#send-points-amount">
                            +10
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="25" target="#send-points-amount">
                            +25
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="50" target="#send-points-amount">
                            +50
                        </button>
                    </div>
                    <div>
                        <button class="btn btn-default point-btn enforce-max" value="-1" target="#send-points-amount">
                            -1
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="-5" target="#send-points-amount">
                            -5
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="-10" target="#send-points-amount">
                            -10
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="-25" target="#send-points-amount">
                            -25
                        </button>
                        <button class="btn btn-default point-btn enforce-max" value="-50" target="#send-points-amount">
                            -50
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>To:</td>
                <td>
                    <input id="send-typeahead" class="typeahead" type="text">
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button id="send-points-btn" name="btn-send-points">Send</button>
                </td>
            </tr>
        </table>
        <div id="send-points-success" class="success"></div>
        <div id="send-points-error" class="error"></div>
    </div>

    <div class="container content-card">
        <h4>Request Points</h4>
        <table class="table">
            <tr>
                <td class="points-cell">
                    <span id="request-points-amount">0</span> Points
                </td>
                <td>
                    <div>
                        <button class="btn btn-default point-btn" value="1" target="#request-points-amount">+1</button>
                        <button class="btn btn-default point-btn" value="5" target="#request-points-amount">+5</button>
                        <button class="btn btn-default point-btn" value="10" target="#request-points-amount">+10
                        </button>
                        <button class="btn btn-default point-btn" value="25" target="#request-points-amount">+25
                        </button>
                        <button class="btn btn-default point-btn" value="50" target="#request-points-amount">+50
                        </button>
                    </div>
                    <div>
                        <button class="btn btn-default point-btn" value="-1" target="#request-points-amount">-1</button>
                        <button class="btn btn-default point-btn" value="-5" target="#request-points-amount">-5</button>
                        <button class="btn btn-default point-btn" value="-10" target="#request-points-amount">-10
                        </button>
                        <button class="btn btn-default point-btn" value="-25" target="#request-points-amount">-25
                        </button>
                        <button class="btn btn-default point-btn" value="-50" target="#request-points-amount">-50
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>From:</td>
                <td>
                    <input id="request-typeahead" class="typeahead" type="text">
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button id="request-points-btn">Request</button>
                </td>
            </tr>
        </table>
        <div id="request-points-success" class="success"></div>
        <div id="request-points-error" class="error"></div>
    </div>

    <div class="container content-card">
        <h4>Point History</h4>
        <table id="point-history-table" class="table">
            <thead style="display:none">
            <tr>
                <th>Timestamp</th>
                <th>Points</th>
                <th>Person</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
        <div>
            <button id="point-history-prev-page" class="btn btn-default">&lt;</button>
            <span id="point-history-page-label"></span>
            <button id="point-history-next-page" class="btn btn-default pull-right">&gt;</button>
        </div>
    </div>

    <?php if ($isAdmin) { ?>
        <div class="container content-card">
            <h4>ADMIN POWERS - Modify Points <span class="collapser">+</span></h4>
            <table class="table" style="display:none;">
                <tr>
                    <td class="points-cell">
                        <span id="admin-points-amount">0</span> Points
                    </td>
                    <td>
                        <div>
                            <button class="btn btn-default point-btn point-btn-admin" value="1"
                                    target="#admin-points-amount">+1
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="5"
                                    target="#admin-points-amount">+5
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="10"
                                    target="#admin-points-amount">+10
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="25"
                                    target="#admin-points-amount">+25
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="50"
                                    target="#admin-points-amount">+50
                            </button>
                        </div>
                        <div>
                            <button class="btn btn-default point-btn point-btn-admin" value="-1"
                                    target="#admin-points-amount">-1
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="-5"
                                    target="#admin-points-amount">-5
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="-10"
                                    target="#admin-points-amount">-10
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="-25"
                                    target="#admin-points-amount">-25
                            </button>
                            <button class="btn btn-default point-btn point-btn-admin" value="-50"
                                    target="#admin-points-amount">-50
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>User:</td>
                    <td>
                        <input id="admin-typeahead" class="typeahead" type="text">
                    </td>
                    <td>
                        <button class="btn btn-default" id="point-btn-all">Everyone</button>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button id="admin-points-btn">Change</button>
                    </td>
                </tr>
            </table>
            <div id="admin-points-success" class="success" style="display:none;"></div>
            <div id="admin-points-error" class="error" style="display:none;"></div>
        </div>

        <div class="container content-card">
            <h4>ADMIN POWERS - View Users <span class="collapser">+</span></h4>
            <table id="admin-user-table" class="table" style="display:none;">
                <thead>
                <th>UID</th>
                <th>Name</th>
                <th>Points</th>
                <th>House</th>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    <?php } ?>

</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script type="text/javascript" src="/members/lib/typeahead/typeahead.jquery.min.js"></script>
<script src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript">
    function typeAheadMatcher(list) {
        return function findMatches(query, callback) {
            var matches = [],
                substringRegex = new RegExp(query, 'i');
            $.each(list, function(i, item) {
                if (substringRegex.test(item)) {
                    matches.push(item);
                }
            });
            callback(matches);
        };
    }

    function buildTypeAheadOpts(name, list, emptyMessage, templateParams) {
        return {
            name: name,
            source: typeAheadMatcher(list),
            templates: {
                empty: emptyMessage
                //TODO: suggestion template with underscore
            }
        };
    }

    function typeAheadOnKeydown(e) {
        var $typeahead = $(e.target);
        if (e.keyCode === 13) { //Enter
            var $menu = $typeahead.siblings('.tt-menu');
            var $selectedSuggestion = $menu.find('.tt-suggestion')
                .filter(function() {
                    return $(this).text() === $typeahead.val();
                });
            if ($selectedSuggestion.length > 0) {
                // Trigger a click on the selected option
                $selectedSuggestion.get(0).click();
            } else {
                // Default to triggering a click on the first option
                var $firstSuggestion = $menu.find('.tt-suggestion:first');
                $firstSuggestion.click();
            }
        }
    }

    function formatDate(date) {
        var dayArr = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            dayOfWeek = dayArr[date.getDay()],
            year = date.getFullYear().toString(),
            month = (date.getMonth() + 101).toString().substring(1),
            day = (date.getDate() + 100).toString().substring(1),
            hours = (date.getHours() + 100).toString().substring(1),
            minutes = (date.getMinutes() + 100).toString().substring(1),
            suffix = "AM";
        if (date.getHours() > 12) {
            hours = (date.getHours() - 12 + 100).toString().substring(1);
            suffix = "PM";
        } else if (date.getHours() === 0) {
            hours = "12";
            suffix = "AM";
        }
        return dayOfWeek + ", " + year + "-" + month + "-" + day + " " + hours + ":" + minutes + suffix;
    }

    function updateMyPoints() {
        return $.get("/members/utils/getpoints.php")
            .done(function(resp) {
                $('#my-points').text(resp);
            });
    }

    function updateMyHistory() {
        var $tableBody = $('#point-history-table tbody'),
            $tableHead = $('#point-history-table thead'),
            $prevPage = $('#point-history-prev-page'),
            $nextPage = $('#point-history-next-page'),
            $pageLabel = $('#point-history-page-label'),
            currentPage = 1,
            rowsPerPage = 10,
            numRows = 0,
            numPages = 1;
        $prevPage.hide();
        $nextPage.hide();
        $pageLabel.text("");
        return $.get('/members/utils/gethistory.php')
            .done(function(resp) {
                $tableBody.empty();
                if (!(resp instanceof Array)) {
                    return;
                }
                numRows = resp && resp.length ? resp.length : 0;
                numPages = Math.ceil(numRows / rowsPerPage);
                $tableHead.show();
                $.each(resp, function(i, row) {
                    // Massage the data
                    row.timestamp = new Date(row.timestamp.replace(" ", "T"));
                    row.timestamp.setHours(row.timestamp.getHours() + 3); //adjust for server timezone
                    row.isAdminAction = (row.isAdminAction ? true : false);

                    // Build the html
                    var dateCol = formatDate(row.timestamp);
                    var personCol = (row.fromEmail === "<?php echo $email; ?>" ? row.toName : row.fromName);
                    if (row.isAdminAction) {
                        var lostPoints = (row.numPoints < 0);
                        var pointsCol = (lostPoints ? "" : "+") + row.numPoints + " from";
                        personCol = row.fromName;
                    } else {
                        var pointsCameFromMe = (row.fromEmail === "<?php echo $email; ?>");
                        var pointsCol = (pointsCameFromMe ? "-" : "+") + row.numPoints + (pointsCameFromMe ? " to" : " from");
                    }
                    var html = i < rowsPerPage ? "<tr>" : "<tr style='display:none;'>";
                    html += "<td>" + dateCol + "</td>";
                    html += "<td>" + pointsCol + "</td>";
                    html += "<td>" + personCol + "</td>";
                    html += "</tr>";

                    // Append it to the table
                    $tableBody.append(html);
                });
            }).always(function() {
                if ($tableBody.text().trim() === "") {
                    $tableHead.hide();
                    $tableBody.text("No history entries");
                } else {
                    var $rows = $($tableBody.find('tr'));
                    if (numRows > rowsPerPage) {
                        function updatePaging() {
                            var firstShownRow = (currentPage - 1) * rowsPerPage,
                                lastShownRow = (currentPage * rowsPerPage) - 1;
                            $rows.hide();
                            for (var i = firstShownRow; i <= lastShownRow; i++) {
                                $($rows.get(i)).show();
                            }
                            if (firstShownRow > 0) {
                                $prevPage.show();
                            } else {
                                $prevPage.hide();
                            }
                            if (lastShownRow < numRows) {
                                $nextPage.show();
                            } else {
                                $nextPage.hide();
                            }
                            $pageLabel.text("Page " + currentPage + " of " + numPages);
                        }

                        // Paging is required
                        updatePaging();
                        $nextPage.click(function() {
                            currentPage++;
                            updatePaging();
                        });
                        $prevPage.click(function() {
                            currentPage--;
                            updatePaging();
                        });
                    }
                }
            });
    }

    function clearSendForm() {
        $('#send-points-amount').text("0");
        $('#send-typeahead').val("");
        $('#send-points-error').text("");
        $('#send-points-success').text("");
    }

    function clearRequestForm() {
        $('#request-points-amount').text("0");
        $('#request-typeahead').val("");
        $('#request-points-error').text("");
        $('#request-points-success').text("");
    }
    <?php if($isAdmin){ ?>
    function clearAdminForm() {
        $('#admin-points-amount').text("0");
        $('#admin-typeahead').val("");
        $('#admin-points-error').text("");
        $('#admin-points-success').text("");
    }

    function updateAdminUserTable() {
        var $tableBody = $('#admin-user-table tbody'),
            $tableHead = $('#admin-user-table thead');
        return $.get('/members/utils/getusers.php?forAdmin')
            .done(function(resp) {
                $tableBody.empty();
                if (!(resp instanceof Array)) {
                    return;
                }
                $tableHead.show();
                $.each(resp, function(i, row) {
                    // Build the html
                    var html = "<tr>";
                    html += "<td>" + row.uid + "</td>";
                    html += "<td>" + row.name + "</td>";
                    html += "<td>" + row.upoints + "</td>";
                    html += "<td>" + row.housename + "</td>";
                    html += "</tr>";

                    // Append it to the table
                    $tableBody.append(html);
                });
            }).always(function() {
                if ($tableBody.text().trim() === "") {
                    $tableHead.hide();
                    $tableBody.text("No users?... Uh oh...");
                }
            });
    }

    var $pointBtnAll = $('#point-btn-all');
    var EVERYONE = "EVERYONE";
    $pointBtnAll.on('click', function() {
        $('#admin-typeahead').val(EVERYONE);
    });
    <?php } ?>
    function updatePendingRequests() {
        var $tableBody = $('#pending-requests-table tbody'),
            $tableHead = $('#pending-requests-table thead');
        $.get('/members/utils/pointsrequest_get.php')
            .done(function(resp) {
                $tableBody.empty();
                if (!(resp instanceof Array)) {
                    return;
                }
                $tableHead.show();
                $.each(resp, function(i, row) {
                    // Massage the data
                    row.timestamp = new Date(row.timestamp.replace(" ", "T"));
                    row.timestamp.setHours(row.timestamp.getHours() + 3); //adjust for server timezone

                    // Build the html
                    var acceptLink = '<a class="accept-request" sourceUid="' + row.sourceUid + '">Accept</a>';
                    var rejectLink = '<a class="reject-request" sourceUid="' + row.sourceUid + '">Reject</a>';
                    var actionLinks = acceptLink + " | " + rejectLink;
                    var html = "<tr>";
                    html += "<td>" + row.sourceName + "</td>";
                    html += "<td>" + row.numPoints + "</td>";
                    html += "<td>" + actionLinks + "</td>";
                    html += "</tr>";

                    // Append it to the table
                    $tableBody.append(html);
                });
            }).always(function() {
            if ($tableBody.text().trim() === "") {
                $tableHead.hide();
                $tableBody.text("No pending requests");
            } else {
                applyClickHandlersForAcceptReject();
            }
        });
    }

    function applyClickHandlersForAcceptReject() {
        var $error = $('#pending-requests-error'),
            $success = $('#pending-requests-success');

        // Click handler for accepting pending point requests
        $('.accept-request').off().on('click', function(e) {
            var $target = $(e.target),
                $row = $target.closest('tr'),
                sourceUid = $target.attr('sourceuid');
            $error.text("");
            $success.text("");
            if ($row.hasClass('disabled')) {
                return;
            }

            if (isNaN(parseInt(sourceUid))) {
                $error.text('Error accepting request. Please refresh page and try again.');
                return;
            }
            $success.text("Processing...");
            $row.addClass('disabled');
            $.ajax({
                url: "/members/utils/pointsrequest_accept.php",
                type: 'POST',
                data: "source_uid=" + sourceUid
            }).done(function(resp) {
                if (resp === 'SUCCESS') {
                    $success.text("Request accepted!");
                    setTimeout(function() { //after 2 seconds, update the data on the page
                        updateAllDataOnPage();
                        $success.text("");
                    }, 2000);
                } else {
                    $error.text(resp);
                    $row.removeClass('disabled');
                }
            });
        });

        // Click handler for rejecting pending point requests
        $('.reject-request').off().on('click', function(e) {
            var $target = $(e.target),
                $row = $target.closest('tr'),
                sourceUid = $target.attr('sourceuid');
            $error.text("");
            $success.text("");
            if ($row.hasClass('disabled')) {
                return;
            }

            if (isNaN(parseInt(sourceUid))) {
                $error.text('Error accepting request. Please refresh page and try again.');
                return;
            }
            $success.text("Processing...");
            $row.addClass('disabled');
            $.ajax({
                url: "/members/utils/pointsrequest_reject.php",
                type: 'POST',
                data: "source_uid=" + sourceUid
            }).done(function(resp) {
                if (resp === 'SUCCESS') {
                    $success.text("Request rejected!");
                    setTimeout(function() { //after 2 seconds, update the data on the page
                        updatePendingRequests();
                        $success.text("");
                    }, 2000);
                } else {
                    $error.text(resp);
                    $row.removeClass('disabled');
                }
            });
        });
    }

    function updateAllDataOnPage() {
        updateMyPoints();
        updateMyHistory();
        updatePendingRequests();
        <?php if($isAdmin){ ?>updateAdminUserTable();<?php } ?>
    }

    // Get user name/uid mapping from PHP / database
    var userMapping = <?php
        $length = count($userList);
        $i = 0;
        $str = "[";
        while ($i < $length) {
            $user = $userList[$i];
            $str = "{$str}{\"uid\":\"{$user['uid']}\",\"name\":\"{$user['name']}\"}";
            if ($i + 1 < $length) {
                $str = "{$str},";
            }
            $i = $i + 1;
        }
        $str = "{$str}]";
        echo $str;
        ?>;
    var userList = [],
        templateParams = [];
    $.each(userMapping, function(i, item) {
        userList.push(item.name);
        templateParams.push({
            name: item.name,
            uid: item.uid
        });
    });

    function getUserUidFromName(name) {//TODO: use email instead of names (since names are not unique)
        for (var i = 0; i < userMapping.length; i++) {
            if (userMapping[i].name === name) {
                return userMapping[i].uid;
            }
        }
        return null;
    }

    function setupTypeAhead(typeaheadSelector) {
        var list = userList;
        if (typeaheadSelector === '#admin-typeahead') {
            list.push(EVERYONE); //include "EVERYONE" as an option for admins
        }
        var $typeahead = $(typeaheadSelector);
        $typeahead.typeahead('typeahead:active', buildTypeAheadOpts('users', list, 'No users found', templateParams));
        $typeahead.on('keydown', typeAheadOnKeydown);
    }

    // Setup typeaheads
    setupTypeAhead('#send-typeahead');
    setupTypeAhead('#request-typeahead');
    <?php if($isAdmin){ ?>
    setupTypeAhead('#admin-typeahead');
    <?php } ?>

    // Click handler for point buttons
    var maxPoints = parseInt(<?php echo $points; ?>);
    $('.point-btn').on('click', function(e) {
        var $btn = $(e.target),
            enforceMax = $btn.hasClass('enforce-max'),
            enforceMin = !$btn.hasClass('point-btn-admin'),
            $target = $($btn.attr('target')),
            $inputTarget = $($btn.attr('input-target')),
            value = parseInt($btn.attr('value'), 10),
            prev = parseInt($target.text(), 10),
            newValue = prev + value;
        if (enforceMin && newValue < 0) {
            newValue = 0;
        } else if (enforceMax && newValue > maxPoints) {
            newValue = maxPoints;
        }
        $target.text(newValue);
        $inputTarget.val(newValue);
    });

    // Click handler for sending points
    var preventingSendSpam = false;
    $('#send-points-btn').on('click', function(e) {
        if (preventingSendSpam) {
            alert("Sending won't work until the last send finishes processing.");
            return;
        }
        var $error = $('#send-points-error'),
            $success = $('#send-points-success'),
            numPoints = parseInt($('#send-points-amount').text()),
            toName = $('#send-typeahead').val(),
            toUid = getUserUidFromName(toName);

        // Validation
        if (numPoints <= 0) {
            $error.text("Ignoring send for non-positive point value");
            return;
        } else if (!toUid) {
            $error.text("Ignoring invalid recipient [" + toUid + "]");
            return;
        }
        preventingSendSpam = true;

        // Send the points via a POST
        $.ajax({
            url: "/members/utils/sendpoints.php",
            type: 'POST',
            data: "num_points=" + numPoints + "&to_uid=" + toUid
        }).done(function(resp) {
            updateMyPoints();
            clearSendForm();

            // Update the message
            if (resp === "SUCCESS") {
                $success.text("Sent " + numPoints + " points to " + toName + "!");
                updateMyHistory();
            } else {
                $error.text(resp);
            }
        }).always(function() {
            preventingSendSpam = false;
        });
    });

    // Click handler for requesting points
    var preventingRequestSpam = false;
    $('#request-points-btn').on('click', function(e) {
        if (preventingRequestSpam) {
            alert("Requesting won't work until the last request finishes processing.");
            return;
        }
        var $error = $('#request-points-error'),
            $success = $('#request-points-success'),
            numPoints = parseInt($('#request-points-amount').text()),
            toName = $('#request-typeahead').val(),
            toUid = getUserUidFromName(toName);

        // Validation
        if (numPoints <= 0) {
            $error.text("Ignoring request for non-positive point value");
            return;
        } else if (!toUid) {
            $error.text("Ignoring invalid recipient [" + toUid + "]");
            return;
        }
        preventingRequestSpam = true;

        // Request the points via a POST
        $.ajax({
            url: "/members/utils/pointsrequest_send.php",
            type: 'POST',
            data: "num_points=" + numPoints + "&target_uid=" + toUid
        }).done(function(resp) {
            updateMyPoints();
            clearRequestForm();

            // Update the message
            if (resp === "SUCCESS") {
                $success.text("Requested " + numPoints + " from " + toName + "!");
                updateMyHistory();
            } else {
                $error.text(resp);
            }
        }).always(function() {
            preventingRequestSpam = false;
        });
    });

    $('.collapser').click(function(e) {
        var $target = $(e.target),
            $toggleTargets = $target.parent().siblings();
        if ($target.hasClass('expanded')) {
            $toggleTargets.slideToggle(false);
            $target.text('+');
        } else {
            $toggleTargets.slideToggle(true);
            $target.text('-');
        }
        $target.toggleClass('expanded');
    });

    <?php if($isAdmin){ ?>
    // Click handler for admin points
    $('#admin-points-btn').on('click', function(e) {
        var $error = $('#admin-points-error'),
            $success = $('#admin-points-success'),
            numPoints = parseInt($('#admin-points-amount').text()),
            targetName = $('#admin-typeahead').val(),
            targetUid = getUserUidFromName(targetName);

        if (targetName === EVERYONE) {
            targetUid = -1;
        }

        // Validation
        if (numPoints == 0) {
            $error.text("Ignoring admin change with zero points");
            return;
        } else if (!targetUid) {
            $error.text("Ignoring invalid target [" + targetUid + "]");
            return;
        }

        // Clear the form
        clearAdminForm();

        // Change the points via a POST
        $.ajax({
            url: "/members/utils/sendpoints_admin.php",
            type: 'POST',
            data: "num_points=" + numPoints + "&target_uid=" + targetUid
        }).done(function(resp) {
            // Update the message
            if (resp === "SUCCESS") {
                $success.text("Changed " + targetName + "'s points by " + numPoints);
                updateMyHistory();
                updateMyPoints();
            } else {
                $error.text(resp);
            }
        });
    });
    <?php } ?>

    // Get data from database and update the page
    updateAllDataOnPage();
</script>
</body>
</html>