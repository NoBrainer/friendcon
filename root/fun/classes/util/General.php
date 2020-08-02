<?php
namespace util;

use Constants as Constants;
use RuntimeException as RuntimeException;

class General {

	/**
	 * Generate link HTML.
	 *
	 * @param string $text
	 * @param string $href
	 * @param string $target
	 * @return string
	 */
	public static function linkHtml(string $text, string $href, string $target = '_blank'): string {
		if (is_null($target)) return "<a href='$href'>$text</a>";
		return "<a href='$href' target='$target'>$text</a>";
	}

	/**
	 * Send an email from FriendCon Bot. This is an extension of the PHP mail() function with some useful features added.
	 *
	 * @param string $to
	 * @param string $subject
	 * @param array $lines
	 * @param array|null $headersObject
	 * @param string|null $parameters
	 * @return bool - whether or not the email was sent
	 */
	public static function sendEmailFromBot(string $to, string $subject, array $lines = [], ?array $headersObject = null, ?string $parameters = null): bool {
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
	public static function startsWith(string $string, string $another): bool {
		return substr($string, 0, strlen($another)) === $another;
	}
}
