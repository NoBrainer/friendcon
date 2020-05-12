<?php
// Requirements: The PAYPAL object is configured in the included PHP file.
// Usage:
// 1. Sets PAYPAL constant with CLIENT_ID, a string for the PayPal Client ID
// 2. Sets PAYPAL constant with OAUTH_API_URL, the URL for getting the Access Token
// 3. Sets PAYPAL constant with ORDER_API_URL, the URL for orders
// 4. Sets $PAYPAL_SECRET - a string for the PayPal Secret (Make sure to unset)
//
// IMPORTANT: After using $PAYPAL_SECRET, unset it:
// unset($PAYPAL_SECRET); //Remove sensitive info from memory

// Include the private config file
include($_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/paypal.php');
