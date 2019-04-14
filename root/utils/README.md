# Excluded Files

## `dbconnect.php`
```
<?php
// Requirements: $userSession must be defined before this is included/required
// Usage: 
// 1. Connects to the database
// 2. Sets $isAdmin - a boolean whether or not the current user is an admin

$DB_host = "[REDACTED]";
$DB_user = "[REDACTED]";
$DB_pass = "[REDACTED]";
$DB_name = "[REDACTED]";

$MySQLi_CON = new MySQLi($DB_host, $DB_user, $DB_pass, $DB_name);

if($MySQLi_CON->connect_errno){
	die("ERROR : ->".$MySQLi_CON->connect_error);
}

/*
// I commented this out to fix the login bug. - Vince

//Since dbconnect.php is included on every page that matters, the session expiry code lives here.
$expiry = 12600 ; //Session will expire after 12600 seconds (3 hours and 30 minutes)
    if (isset($userSession['LAST']) && (time() - $userSession['LAST'] > $expiry)) {
        session_unset();
        session_destroy();
    }
    $userSession['LAST'] = time();
*/

// Escape the user session number
$userSession = $MySQLi_CON->real_escape_string($userSession);
?>
```