<?php

class Constants {

	// HTTP Header Content Type
	public const CONTENT_TYPE = [
			'HTML'   => 'text/html; charset=utf-8',
			'JSON'   => 'application/json',
			'STREAM' => 'application/octet-stream',
			'TEXT'   => 'text/plain; charset=utf-8'
	];

	// HTTP Response Codes
	public const HTTP = [
			'OK'                    => 200,
			'NOT_MODIFIED'          => 304,
			'PERMANENT_REDIRECT'    => 308,
			'BAD_REQUEST'           => 400,
			'NOT_AUTHORIZED'        => 401,
			'FORBIDDEN'             => 403,
			'NOT_FOUND'             => 404,
			'INTERNAL_SERVER_ERROR' => 500
	];

	public static function captchaConfig() {
		return $_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/captcha.php';
	}

	public static function dbConfig() {
		return $_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/db.php';
	}

	public static function uploadsDir() {
		return $_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/uploads';
	}
}
