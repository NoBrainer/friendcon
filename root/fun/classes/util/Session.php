<?php

namespace util;
class Session {

	public static $userSession;
	public static $isLoggedIn;

	public static $isAdmin;
	public static $isGameAdmin;
	public static $isSiteAdmin;

	public static function initialize() {
		session_start();
		Session::$userSession = $_SESSION['userSession'];
		Session::$isLoggedIn = isset(Session::$userSession) && !empty(Session::$userSession);

		if (Session::$isLoggedIn) {
			if (Session::$userSession == 43) {
				// Vince is all-powerful
				Session::$isAdmin = true;
				Session::$isGameAdmin = true;
				Session::$isSiteAdmin = true;
			} else {
				// Check the user's privileges
				$query = "SELECT * FROM admins WHERE uid = ?";
				$result = Sql::executeSqlForResult($query, 'i', Session::$userSession);
				if (Sql::hasRows($result, 1)) {
					Session::$isAdmin = true;
					$row = Sql::getNextRow($result);
					Session::$isGameAdmin = General::getBooleanValue($row['gameAdmin']);
					Session::$isSiteAdmin = General::getBooleanValue($row['siteAdmin']);
				}
			}
		} else {
			// Default state
			Session::$isAdmin = false;
			Session::$isGameAdmin = false;
			Session::$isSiteAdmin = false;
		}
	}

	public static function login($uid) {
		$_SESSION['userSession'] = $uid;
		Session::initialize();
	}

	public static function logout() {
		session_destroy();
		Session::initialize();
	}
}
