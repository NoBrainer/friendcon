<?php
session_start();
$userSession = $_SESSION['userSession'];

if (isset($userSession) != "") {
    // If logged in, go to home instead
    header("Location: /members/home.php");
    exit;
}
include_once('../utils/dbconnect.php');

if (isset($_POST['btn-login'])) {
    $email = $MySQLi_CON->real_escape_string(trim($_POST['email']));
    $password = $MySQLi_CON->real_escape_string(trim($_POST['password']));

    $query = $MySQLi_CON->query("SELECT uid, email, password FROM users WHERE email='$email'");
    $row = $query->fetch_array();

    if (!empty($row)) {
        if (md5($password) === $row['password']) {
            $_SESSION['userSession'] = $row['uid'];
            header("Location: home.php");
        } else {
            echo $_SESSION['uid'];
            $msg = "<div class='alert alert-danger'>
						<span class='glyphicon glyphicon-info-sign'></span> &nbsp; Your password does not match!
					</div>";
        }
    } else {
        echo $_SESSION['uid'];
        $msg = "<div class='alert alert-danger'>
					<span class='glyphicon glyphicon-info-sign'></span> &nbsp; No registration entry with this email!
				</div>";
    }
    $MySQLi_CON->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendCon - Sign In</title>
    <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../lib/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>

<body>
<?php include_once('header.php'); ?>

<div class="container content">
    <div class="container content-card">
        <form method="post" id="login-form">
            <h2 class="form-signin-heading center">Sign In</h2>
            <h3 class="form-signin-heading">
                <a href="signup.php" class="btn btn-default btn-wide">Sign up for an account here!</a>
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
                        style=" display:block; margin-left: auto; margin-right: auto;"
                " >
                <span class="glyphicon glyphicon-log-in"></span> &nbsp; Sign In
                </button>
            </div>

            <a href="/members/forgotPassword.php">Forgot Your Password?</a>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script type="text/javascript" src="/js/jquery-1.11.1.min.js"></script>
<script src="/lib/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">
    /*console.log("<?php echo $userSession; ?>");
     console.log("<?php echo $userRow['name']; ?>");*/
</script>
</body>
</html>