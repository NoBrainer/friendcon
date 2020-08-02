<?php

namespace util;
class Setup {

	public static function initialize(): bool {
		// Forward http to https
		if (Http::forwardHttps()) return false;

		// Setup the database connection
		Sql::initializeConnection();

		// Initialize the user session
		Session::initialize();

		return true;
	}
}
