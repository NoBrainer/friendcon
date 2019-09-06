<?php
session_start();
$userSession = $_SESSION['userSession'];

// Short-circuit forwarding
include('utils/reroute_functions.php');
if (forwardHttps() || forwardHomeIfLoggedIn()) {
    exit;
}

include('utils/dbconnect.php');
include('utils/sql_functions.php');

if (isset($_POST['btn-login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $query = "SELECT uid, email, password FROM users WHERE email=?";
    $result = prepareSqlForResult($MySQLi_CON, $query, 's', $email);

    if (hasRows($result, 1)) {
        $row = getNextRow($result);
        if (md5($password) === $row['password']) {
            $_SESSION['userSession'] = $row['uid'];
            header("Location: /members/home.php");
        } else {
            $msg = "<div class='alert alert-danger'>
						<span class='glyphicon glyphicon-info-sign'></span>
						<span>Your password does not match!</span>
					</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>
					<span class='glyphicon glyphicon-info-sign'></span>
					<span>No registration entry with this email!</span>
				</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendCon - Sign In</title>
    <link href="/members/lib/bootstrap/css/bootstrap-3.3.4.min.css" rel="stylesheet" media="screen">
    <link href="/members/lib/bootstrap/css/bootstrap-theme-3.3.5.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="/members/css/style.css" type="text/css"/>
</head>

<body>
<?php include('header.php'); ?>

<div class="container content">
    <div class="container content-card">
        <form method="post" id="login-form">
            <h2 class="form-signin-heading center">Sign In</h2>
            <h3 class="form-signin-heading">
                <a href="/members/signup.php" class="btn btn-default btn-wide"
                   onclick="alert('User sign-up currently disabled.')">Sign up for an account here!</a>
            </h3>
            <hr/>

            <?php if (isset($msg))
                echo $msg; ?>

            <div class="form-group">
                <input type="email" class="form-control" placeholder="Email address" name="email" required/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" placeholder="Password" name="password" required/>
            </div>
            <hr/>

            <div class="form-group">
                <button type="submit" name="btn-login" id="btn-login"
                        style="display:block; margin-left: auto; margin-right: auto;">
                    <span class="glyphicon glyphicon-log-in"></span>
                    &nbsp;
                    <span>Sign In</span>
                </button>
            </div>

            <a href="/members/forgotPassword.php">Forgot Your Password?</a>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/members/lib/jquery/jquery-3.4.0.min.js"></script>
<script src="/members/lib/bootstrap/js/bootstrap-3.3.4.min.js"></script>
<script type="text/javascript">
    //console.log("<?php echo $userSession; ?>");
    //console.log("<?php echo $userRow['name']; ?>");
</script>
</body>
</html>