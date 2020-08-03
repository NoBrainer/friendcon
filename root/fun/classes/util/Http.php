<?php
namespace fun\classes\util;

use fun\classes\Constants as Constants;

class Http {

	public static function cacheControl(string $value): void {
		header("Cache-Control: $value");
	}

	public static function contentDescription(string $value): void {
		header("Content-Description: $value");
	}

	public static function contentDisposition(string $value): void {
		header("Content-Disposition: $value");
	}

	public static function contentLength(string $value): void {
		header("Content-Length: $value");
	}

	public static function contentType(string $type): void {
		header("Content-Type: " . Constants::CONTENT_TYPE[$type]);
	}

	public static function forward(string $path = "/", bool $replaceUrl = true, ?int $httpResponseCode = null): void {
		header("Location: $path", $replaceUrl, $httpResponseCode);
	}

	public static function forwardHttps(): bool {
		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
			Http::forward("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 308);
			return true;
		}
		return false;
	}

	public static function isRequestMethod(string $expectedMethod): bool {
		return $_SERVER['REQUEST_METHOD'] === $expectedMethod;
	}

	public static function isXHR(): bool {
		return $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	public static function responseCode(string $key): void {
		http_response_code(Constants::HTTP[$key]);
	}

	public static function return404(): void {
		Http::responseCode('NOT_FOUND');
		include($_SERVER['DOCUMENT_ROOT'] . '/index.php');
	}

	public static function return404IfNotGet(): bool {
		return Http::return404IfNotRequestMethod('GET');
	}

	public static function return404IfNotPost(): bool {
		return Http::return404IfNotRequestMethod('POST');
	}

	public static function return404IfNotRequestMethod(string $expectedMethod, ?bool $strictAccess = true): bool {
		$isAllowed = $strictAccess ? Http::isXHR() : true;
		if (!$isAllowed || !Http::isRequestMethod($expectedMethod)) {
			Http::return404();
			return true;
		}
		return false;
	}
}
