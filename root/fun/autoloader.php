<?php
use util\Setup as Setup;

// Autoload custom classes
spl_autoload_register(function ($fullClassName) {
	// Check for files relative to this file
	$file = $_SERVER['DOCUMENT_ROOT'] . '/fun/classes/' . $fullClassName . '.php';

	// Make sure all backslashes are replaced with DIRECTORY_SEPARATOR
	$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

	// Load the class
	if (file_exists($file)) include($file);
});

// Run the setup
if (!Setup::initialize()) exit;
