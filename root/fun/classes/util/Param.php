<?php

namespace util;

use DateTime as DateTime;
use Exception as Exception;

class Param {

	private const VALID_FALSE_VALUES = [0, false, "0", "false"];
	private const VALID_TRUE_VALUES = [1, true, "1", "true"];
	private const VALID_BOOLEAN_VALUES = [0, 1, false, true, "0", "1", "false", "true"];

	public static function asBoolean($value, $default = null) {
		if (!isset($value)) return $default;
		if (in_array($value, Param::VALID_FALSE_VALUES, true)) return false;
		if (in_array($value, Param::VALID_TRUE_VALUES, true)) return true;
		return $default;
	}

	public static function asInteger($value, $default = null) {
		return Param::isInteger($value) ? intval($value) : $default;
	}

	public static function asString($value, $trim = true) {
		return $trim ? trim("$value") : "$value";
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
		return isset($value) && in_array($value, Param::VALID_BOOLEAN_VALUES);
	}

	public static function isEmptyString($value) {
		return sizeof(trim($value)) === 0;
	}

	public static function isInteger($value) {
		// Has type integer OR has all digits OR its negative has all digits
		return is_integer($value) || ctype_digit($value) || ctype_digit(strval($value * -1));
	}

	public static function isPopulatedString($value) {
		return Param::isString($value) && !Param::isEmptyString($value);
	}

	public static function isString($value) {
		return isset($value) && is_string($value);
	}
}
