<?php
/**
 * Check if a string starts with another string.
 *
 * @param $string string
 * @param $another string
 * @return bool - whether or not $string starts with $another
 */
function startsWith($string, $another) {
    return substr($string, 0, strlen($another)) === $another;
}
