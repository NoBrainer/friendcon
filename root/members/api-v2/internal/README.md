# Excluded Files

## `secrets/initDB.php`
```php
<?php
// Requirements: None
// Usage:
// 1. Connects to the database

$DB_host = "[REDACTED]";
$DB_user = "[REDACTED]";
$DB_pass = "[REDACTED]";
$DB_name = "[REDACTED]";

$MySQLi_CON = new MySQLi($DB_host, $DB_user, $DB_pass, $DB_name);

if ($MySQLi_CON->connect_error) {
    die("Error connecting to the database");
}

// Report all errors, converting them to the mysqli_sql_exception class
// Note: Since this is after the data connection error, we won't ever print the username/password.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set the specific UTF8 character set
$MySQLi_CON->set_charset("utf8mb4");
```

## `secrets/paypal.php`
```php
<?php
// Usage:
// 1. Sets $PAYPAL_CLIENT_ID - a string for the PayPal Client ID
// 2. Sets $PAYPAL_SECRET - a string for the PayPal Secret
// 3. Sets $PAYPAL_OAUTH_API_URL - the URL for getting the Access Token
// 4. Sets $PAYPAL_ORDER_API_URL - the URL for orders

// Sandbox for testing
//$PAYPAL_CLIENT_ID =     "AavBpGb_T2QxTTxaHfNqFC0OBP8p5PvSvhWB5t9a03f_xthlO5ooKvZ5hMkrIS5Km4rZAKNmiQfbK8Ot";
//$PAYPAL_SECRET =        "EFzCagYOUISIa_ijaM4jf7YCCCJLVAmrurrNu_4McZrCnzz8jpzEDQu_apnBuxx0bnsKAIR_rvsIOoIp";
//$PAYPAL_OAUTH_API_URL = "https://api.sandbox.paypal.com/v1/oauth2/token/";
//$PAYPAL_ORDER_API_URL = "https://api.sandbox.paypal.com/v2/checkout/orders/";

// Live
$PAYPAL_CLIENT_ID =     "[REDACTED]";
$PAYPAL_SECRET =        "[REDACTED]";
$PAYPAL_OAUTH_API_URL = "https://api.paypal.com/v1/oauth2/token/";
$PAYPAL_ORDER_API_URL = "https://api.paypal.com/v2/checkout/orders/";
```