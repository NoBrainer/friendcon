<?php
namespace util;

use util\Param as Param;

class Session {

	public static $userSession;
	public static $name;
	public static $isLoggedIn;

	public static $isAdmin;
	public static $isGameAdmin;
	public static $isSiteAdmin;

	public static function initialize(): void {
		session_start();
		Session::$userSession = $_SESSION['userSession'];
		Session::$isLoggedIn = isset(Session::$userSession) && !empty(Session::$userSession);

		if (Session::$isLoggedIn) {
			// Check the user's privileges
			$query = "SELECT * FROM admins WHERE uid = ?";
			$result = Sql::executeSqlForResult($query, 'i', Session::$userSession);
			if (Sql::hasRows($result, 1)) {
				Session::$isAdmin = true;
				$row = Sql::getNextRow($result);
				Session::$isGameAdmin = Param::asBoolean($row['gameAdmin']);
				Session::$isSiteAdmin = Param::asBoolean($row['siteAdmin']);
				Session::$name = Param::asString($row['name']);
			}

			// Vince is all-powerful
			if (Session::$userSession === 43) {
				Session::$isAdmin = true;
				Session::$isGameAdmin = true;
				Session::$isSiteAdmin = true;
			}
		} else {
			// Default state
			Session::$isAdmin = false;
			Session::$isGameAdmin = false;
			Session::$isSiteAdmin = false;
		}
	}

	public static function login(string $uid): void {
		$_SESSION['userSession'] = $uid;
		Session::initialize();
	}

	public static function logout(): void {
		session_destroy();
		Session::initialize();
	}
}
