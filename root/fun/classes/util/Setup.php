<?php

namespace util;
class Setup {

	public static function initialize() {
		// Forward http to https
		if (Http::forwardHttps()) return false;

		// Initialize the user session
		Session::initialize();

		// Load the private config so that its constants are available
		include($_SERVER['DOCUMENT_ROOT'] . '/../friendcon-private/config/config.php');

		// Setup the database connection
		Sql::initializeConnection();

		return true;
	}
}
