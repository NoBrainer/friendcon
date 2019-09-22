<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/members/home.php">FriendCon Members Home</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li>
                    <a href="/">Back to FriendCon Site</a>
                </li>
                <li>
                    <a target="_blank" href="https://www.facebook.com/friendconofficial/">
                        <span>FriendCon Facebook Page</span>
                        <i class="fa fa-external-link-alt"></i>
                    </a>
                </li>
            </ul>
            <?php if (isset($userSession) && $userSession != "" && $userRow) { ?>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="/members/profile.php" title="My Profile">
                            <span class="fa fa-user"></span>
                            <span><?php echo $userRow['name']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/NoBrainer/friendcon/issues" target="_blank">
                            <span class="fa fa-bug"></span>
                            <span>Report a Bug</span>
                        </a>
                    </li>
                    <li>
                        <a href="/members/utils/logout.php?logout&dest=members">
                            <span class="fa fa-sign-out-alt"></span>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            <?php } else { ?>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="https://github.com/NoBrainer/friendcon/issues" target="_blank">
                            <span class="fa fa-bug"></span>
                            <span>Report a Bug</span>
                        </a>
                    </li>
                </ul>
            <?php } ?>
        </div>
    </div>
</nav>