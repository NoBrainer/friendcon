<?php

namespace util;

use Constants as Constants;

class Captcha {

	public static $CAPTCHA_SECRET_V2_KEY = null;
	public static $CAPTCHA_SECRET_V3_KEY = null;
	public const HOSTNAME = "friendcon.com";
	public const THRESHOLD = 0.5;

	public static function initialize(bool $unsetSecrets = true): void {
		// Variables in this config file:
		// - CAPTCHA_SITE_V2_KEY - a string constant for the reCAPTCHA v2 Site Key
		// - CAPTCHA_SITE_V3_KEY - a string constant for the reCAPTCHA v3 Site Key
		// - $CAPTCHA_SECRET_V2_KEY - a string for the reCAPTCHA v2 Secret
		// - $CAPTCHA_SECRET_V3_KEY - a string for the reCAPTCHA v3 Secret
		$CAPTCHA_SECRET_V2_KEY = null;
		$CAPTCHA_SECRET_V3_KEY = null;
		include(Constants::captchaConfig());

		if ($unsetSecrets) {
			Captcha::unsetCaptchaSecrets();
		} else {
			Captcha::$CAPTCHA_SECRET_V2_KEY = $CAPTCHA_SECRET_V2_KEY;
			Captcha::$CAPTCHA_SECRET_V3_KEY = $CAPTCHA_SECRET_V3_KEY;
		}
		unset($CAPTCHA_SECRET_V2_KEY);
		unset($CAPTCHA_SECRET_V3_KEY);
	}

	public static function unsetCaptchaSecrets(): void {
		Captcha::$CAPTCHA_SECRET_V2_KEY = null;
		Captcha::$CAPTCHA_SECRET_V3_KEY = null;
	}

	public static function verify(string $token) {
		Captcha::initialize(false);
		$url = "https://www.google.com/recaptcha/api/siteverify?secret=" . Captcha::$CAPTCHA_SECRET_V3_KEY . "&response=$token";
		Captcha::unsetCaptchaSecrets();
		return json_decode(file_get_contents($url));
	}
}
