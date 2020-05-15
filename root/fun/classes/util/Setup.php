<?php

namespace util;
class Setup {

	public static function initialize() {
		// Forward http to https
		if (Http::forwardHttps()) return false;

		// Initialize the user session
		Session::initialize();

		// Setup the database connection
		Sql::initializeConnection();

		return true;
	}
}
