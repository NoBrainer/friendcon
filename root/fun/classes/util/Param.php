<?php

namespace util;

use DateTime as DateTime;
use Exception as Exception;

class Param {

	public static function asBoolean($value, $default = null) {
		if (!isset($value)) return $default;
		if ($value === 0 || $value === false || $value === 'false') return false;
		if ($value === 1 || $value === true || $value === 'true') return true;
		return $default;
	}

	public static function asInteger($value, $default = null) {
		return Param::isInteger($value) ? intval($value) : $default;
	}

	public static function asString($value) {
		return "$value";
	}

	public static function asTimestamp($value, $default = null) {
		if (!isset($value) || empty($value)) return $default;
		try {
			$dateTime = new DateTime($value);
			return $dateTime->format('Y-m-d H:i:s'); //YYYY-MM-DD hh:mm:ss
		} catch(Exception $exception) {
			return $default;
		}
	}


	public static function isBlankString($value) {
		return !Param::isPopulatedString($value);
	}

	public static function isBoolean($value) {
		return isset($value) && ($value === 0 || $value === 1 || $value === 'true' || $value === 'false' || $value === true || $value === false);
	}

	public static function isEmptyString($value) {
		return empty($value) || empty(trim($value));
	}

	public static function isInteger($value) {
		// Has type integer OR has all digits OR its negative has all digits
		return is_integer($value) || ctype_digit($value) || ctype_digit(strval($value * -1));
	}

	public static function isPopulatedString($value) {
		return Param::isString($value) && !empty($value) && !empty(trim($value));
	}

	public static function isString($value) {
		return isset($value) && is_string($value);
	}
}
