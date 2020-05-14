<?php

namespace util;

use \Constants as Constants;
use \Exception as Exception;
use \RuntimeException as RuntimeException;

class General {

	/**
	 * Cast mixed boolean values into the strict bool.
	 *
	 * @param mixed $val
	 * @param bool $default
	 * @return bool
	 */
	public static function getBooleanValue($val, $default = false) {
		if (!isset($val)) return $default;
		if ($val === 0 || $val === false || $val === 'false') return false;
		if ($val === 1 || $val === true || $val === 'true') return true;
		return $default;
	}

	/**
	 * Check if a variable can be converted into a bool.
	 *
	 * @param mixed $val
	 * @return bool
	 */
	public static function isBooleanSet($val) {
		return isset($val) && ($val === 0 || $val === 1 || $val === 'true' || $val === 'false' || $val === true || $val === false);
	}

	/**
	 * Generate link HTML.
	 *
	 * @param string $text
	 * @param string $href
	 * @param string $target
	 * @return string
	 */
	public static function linkHtml($text, $href, $target = '_blank') {
		if (is_null($target)) return "<a href='$href'>$text</a>";
		return "<a href='$href' target='$target'>$text</a>";
	}

	/**
	 * Send an email from FriendCon Bot. This is an extension of the PHP mail() function with some useful features added.
	 *
	 * @param $to
	 * @param $subject
	 * @param array $lines
	 * @param null $headersObject
	 * @param null $parameters
	 * @return bool - whether or not the email was sent
	 */
	public static function sendEmailFromBot($to, $subject, $lines = [], $headersObject = null, $parameters = null) {
		// Convert the array of lines into a message string, putting each line inside of a div
		$message = "";
		foreach($lines as $line) {
			$message .= "<div>$line</div>";
		}

		// Append the FriendCon Bot signature to the message
		$message .= "<br/><div>&lt;3 FriendCon Bot (BEEP. BOOP)</div>";

		// Use these default headers
		$headers = [
				'From'         => 'FriendCon Bot <no-reply@friendcon.com>',
				'Content-Type' => Constants::CONTENT_TYPE['HTML']
		];
		if (!is_null($headersObject)) {
			if (!is_object($headersObject)) {
				throw new RuntimeException("Invalid input for sendMailFromBot function. The field 'headersObject' must be an object.");
			}
			// If we provide a headers object, override the default headers with its key-value pairs
			foreach($headersObject as $key => $val) {
				$headers[$key] = $val;
			}
		}
		return mail($to, $subject, $message, $headers, $parameters);
	}

	/**
	 * Check if a string starts with another string.
	 *
	 * @param string $string
	 * @param string $another
	 * @return bool - whether or not $string starts with $another
	 */
	public static function startsWith($string, $another) {
		return substr($string, 0, strlen($another)) === $another;
	}

	/**
	 * Cast date string into a date object, default to null or a provided default.
	 *
	 * @param string $str
	 * @param null $default
	 * @return false|null|string
	 */
	public static function stringToDate($str, $default = null) {
		if (!isset($str) || empty($str) || $str == '0000-00-00 00:00:00') return $default;
		try {
			return date($str);
		} catch(Exception $exception) {
			return $default;
		}
	}
}
