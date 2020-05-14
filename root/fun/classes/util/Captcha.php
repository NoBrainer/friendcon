<?php

namespace util;
class Captcha {

	public static $CAPTCHA_SECRET_V2_KEY = null;
	public static $CAPTCHA_SECRET_V3_KEY = null;

	/**
	 * Initialize the CAPTCHA configuration from the private config file.
	 * Usage:
	 * 1. Sets CAPTCHA_SITE_V2_KEY constant, a string for the reCAPTCHA v2 Site Key
	 * 2. Sets CAPTCHA_SITE_V3_KEY constant, a string for the reCAPTCHA v3 Site Key
	 * 3. Sets $CAPTCHA_SECRET_V2_KEY - a string for the reCAPTCHA v2 Secret
	 * 4. Sets $CAPTCHA_SECRET_V3_KEY - a string for the reCAPTCHA v3 Secret
	 */
	public static function initialize($unsetSecrets = false) {
		$CAPTCHA_SECRET_V2_KEY = null;
		$CAPTCHA_SECRET_V3_KEY = null;
		include($_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/captcha.php');
		if ($unsetSecrets) {
			Captcha::unsetCaptchaSecrets();
		} else {
			self::$CAPTCHA_SECRET_V2_KEY = $CAPTCHA_SECRET_V2_KEY;
			self::$CAPTCHA_SECRET_V3_KEY = $CAPTCHA_SECRET_V3_KEY;
		}
		unset($CAPTCHA_SECRET_V2_KEY);
		unset($CAPTCHA_SECRET_V3_KEY);
	}

	/**
	 * After using the CAPTCHA secrets, unset them for security purposes.
	 */
	public static function unsetCaptchaSecrets() {
		self::$CAPTCHA_SECRET_V2_KEY = null;
		self::$CAPTCHA_SECRET_V3_KEY = null;
	}
}
