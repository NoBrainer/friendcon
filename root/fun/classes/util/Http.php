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
		$contentType = Constants::CONTENT_TYPE[$type];
		header("Content-Type: $contentType");
	}

	public static function forward($path = "/", $replaceUrl = true, $httpResponseCode = null) {
		header("Location: $path", $replaceUrl, $httpResponseCode);
	}

	public static function forwardHttps() {
		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
			Http::forward("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 308);
			return true;
		}
		return false;
	}

	public static function responseCode($key) {
		http_response_code(Constants::HTTP[$key]);
	}
}
