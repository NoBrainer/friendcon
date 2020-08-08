<?php
namespace fun\classes\util;

use fun\classes\bonsai\Session as BonsaiSession;
use fun\classes\util\Param as Param;

class Session {

	private const ONE_MINUTE = 60;
	private const ONE_HOUR = 60 * self::ONE_MINUTE;

	public static $userSession;
	public static $name;
	public static $isLoggedIn;

	public static $isAdmin;
	public static $isGameAdmin;
	public static $isSiteAdmin;

	public static function initialize(): void {
		BonsaiSession::start(self::ONE_HOUR);
		self::$userSession = $_SESSION['userSession'];
		self::$isLoggedIn = isset(self::$userSession) && !empty(self::$userSession);

		if (self::$isLoggedIn) {
			// Check the user's privileges
			$query = "SELECT * FROM admins WHERE uid = ?";
			$result = Sql::executeSqlForResult($query, 'i', self::$userSession);
			if (Sql::hasRows($result, 1)) {
				self::$isAdmin = true;
				$row = Sql::getNextRow($result);
				self::$isGameAdmin = Param::asBoolean($row['gameAdmin']);
				self::$isSiteAdmin = Param::asBoolean($row['siteAdmin']);
				self::$name = Param::asString($row['name']);
			}

			// Vince is all-powerful
			if (self::$userSession === 43) {
				self::$isAdmin = true;
				self::$isGameAdmin = true;
				self::$isSiteAdmin = true;
			}
		} else {
			// Default state
			self::$isAdmin = false;
			self::$isGameAdmin = false;
			self::$isSiteAdmin = false;
		}
	}

	public static function login(string $uid): void {
		$_SESSION['userSession'] = $uid;
		self::initialize();
	}

	public static function logout(): void {
		BonsaiSession::remove();
		self::initialize();
	}
}
