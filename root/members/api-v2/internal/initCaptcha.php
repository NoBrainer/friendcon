<?php
// Requirements: The CAPTCHA object is configured in the included PHP file.
// Usage:
// 1. Sets CAPTCHA_SITE_V2_KEY constant, a string for the reCAPTCHA v2 Site Key
// 2. Sets CAPTCHA_SITE_V3_KEY constant, a string for the reCAPTCHA v3 Site Key
// 3. Sets $CAPTCHA_SECRET_V2_KEY - a string for the reCAPTCHA v2 Secret (Make sure to unset)
// 4. Sets $CAPTCHA_SECRET_V3_KEY - a string for the reCAPTCHA v3 Secret (Make sure to unset)
//
// IMPORTANT: After using $CAPTCHA_SECRET_V2_KEY & $CAPTCHA_SECRET_V3_KEY, unset it:
// unset($CAPTCHA_SECRET_V2_KEY);
// unset($CAPTCHA_SECRET_V3_KEY);

include($_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/captcha.php');
