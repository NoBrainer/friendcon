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

if ($MySQLi_CON->connect_errno) {
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

## `paypal.php`
```
<?php
// Usage: 
// 1. Sets $PAYPAL_CLIENT_ID - a string for the PayPal Client ID
// 2. Sets $PAYPAL_SECRET - a string for the PayPal Secret
// 3. Sets $PAYPAL_OAUTH_API_URL - the URL for getting the Access Token
// 4. Sets $PAYPAL_ORDER_API_URL - the URL for orders

$PAYPAL_CLIENT_ID = "[REDACTED]";
$PAYPAL_SECRET = "[REDACTED]";
$PAYPAL_OAUTH_API_URL = "https://api.sandbox.paypal.com/v1/oauth2/token/";
$PAYPAL_ORDER_API_URL = "https://api.sandbox.paypal.com/v2/checkout/orders/";
?>
```
