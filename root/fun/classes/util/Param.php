<?php

namespace fun\classes\util;

use DateTime as DateTime;
use Exception as Exception;

class Param {

	private const VALID_FALSE_VALUES = [0, false, "0", "false"];
	private const VALID_TRUE_VALUES = [1, true, "1", "true"];
	private const VALID_BOOLEAN_VALUES = [0, 1, false, true, "0", "1", "false", "true"];

	public static function asBoolean($value, ?bool $default = null): ?bool {
		if (!isset($value)) return $default;
		if (in_array($value, self::VALID_FALSE_VALUES, true)) return false;
		if (in_array($value, self::VALID_TRUE_VALUES, true)) return true;
		return $default;
	}

	public static function asInteger($value, ?int $default = null): ?int {
		return self::isInteger($value) ? intval($value) : $default;
	}

	public static function asString($value, bool $trim = true): ?string {
		return $trim ? trim("$value") : "$value";
	}

	public static function asTimestamp($value, ?string $default = null): ?string {
		if (!isset($value) || empty($value)) return $default;
		try {
			$dateTime = new DateTime($value);
			return $dateTime->format('Y-m-d H:i:s'); //YYYY-MM-DD hh:mm:ss
		} catch(Exception $exception) {
			return $default;
		}
	}

	public static function isBlankString($value): bool {
		return !self::isPopulatedString($value);
	}

	public static function isBoolean($value): bool {
		return isset($value) && in_array($value, self::VALID_BOOLEAN_VALUES);
	}

	public static function isEmptyString($value): bool {
		return strlen(trim($value)) === 0;
	}

	public static function isInteger($value): bool {
		// Has type integer OR has all digits OR its negative has all digits
		return is_integer($value) || ctype_digit($value) || ctype_digit(strval($value * -1));
	}

	public static function isPopulatedString($value): bool {
		return self::isString($value) && !self::isEmptyString($value);
	}

	public static function isString($value): bool {
		return isset($value) && is_string($value);
	}
}
