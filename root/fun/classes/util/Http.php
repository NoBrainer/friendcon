<?php
namespace util;

use Constants as Constants;

class Http {

	public static function cacheControl($value) {
		header("Cache-Control: $value");
	}

	public static function contentDescription($value) {
		header("Content-Description: $value");
	}

	public static function contentDisposition($value) {
		header("Content-Disposition: $value");
	}

	public static function contentLength($value) {
		header("Content-Length: $value");
	}

	public static function contentType($type) {
		header("Content-Type: " . Constants::CONTENT_TYPE[$type]);
	}

	public static function forward($path = "/", $replaceUrl = true, $httpResponseCode = null) {
		header("Location: $path", $replaceUrl, $httpResponseCode);
	}

	public static function forwardHttps() {
		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
			Http::forward("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 308);
			return true;
		}
		return false;
	}

	public static function isRequestMethod($expectedMethod) {
		return $_SERVER['REQUEST_METHOD'] === $expectedMethod;
	}

	public static function isXHR() {
		return $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	public static function responseCode($key) {
		http_response_code(Constants::HTTP[$key]);
	}

	public static function return404() {
		Http::responseCode('NOT_FOUND');
		include($_SERVER['DOCUMENT_ROOT'] . '/index.php');
	}

	public static function return404IfNotGet() {
		return Http::return404IfNotRequestMethod('GET');
	}

	public static function return404IfNotPost() {
		return Http::return404IfNotRequestMethod('POST');
	}

	public static function return404IfNotRequestMethod($expectedMethod, $strictAccess = true) {
		$isAllowed = $strictAccess ? Http::isXHR() : true;
		if (!$isAllowed || !Http::isRequestMethod($expectedMethod)) {
			Http::return404();
			return true;
		}
		return false;
	}
}
