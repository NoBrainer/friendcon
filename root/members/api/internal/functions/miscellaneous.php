<?php
/**
 * Cast mixed boolean values into the strict bool.
 *
 * @param mixed $val
 * @param bool $default
 * @return bool
 */
function getBooleanValue($val, $default = false) {
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
function isBooleanSet($val) {
	return isset($val) && ($val === 0 || $val === 1 || $val === 'true' || $val === 'false' || $val === true || $val === false);
}

/**
 * Check if a string starts with another string.
 *
 * @param string $string
 * @param string $another
 * @return bool - whether or not $string starts with $another
 */
function startsWith($string, $another) {
	return substr($string, 0, strlen($another)) === $another;
}

/**
 * Cast date string into a date object, default to null or a provided default.
 *
 * @param string $str
 * @param null $default
 * @return false|null|string
 */
function stringToDate($str, $default = null) {
	if (!isset($str) || empty($str) || $str == '0000-00-00 00:00:00') return $default;
	try {
		return date($str);
	} catch(Exception $exception) {
		return $default;
	}
}
