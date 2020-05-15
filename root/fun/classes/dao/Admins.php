<?php

namespace dao;

use util\General as General;
use util\Sql as Sql;

class Admins {

	public static function add($email) {
		$query = "INSERT INTO admins (email) VALUES (?)";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 's', $email);
		return $affectedRows === 1;
	}

	public static function checkPassword($admin, $password) {
		return md5($password) === $admin['hash'];
	}

	public static function delete($uid) {
		$query = "DELETE FROM admins WHERE uid = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 'i', $uid);
		return $affectedRows === 1;
	}

	public static function exists($uid) {
		$query = "SELECT * FROM admins WHERE uid = ?";
		$result = Sql::executeSqlForResult($query, 's', $uid);
		return Sql::hasRows($result, 1);
	}

	public static function existsWithEmail($email) {
		$query = "SELECT * FROM admins WHERE email = ?";
		$result = Sql::executeSqlForResult($query, 's', $email);
		return Sql::hasRows($result, 1);
	}

	public static function existsWithResetToken($email, $token) {
		//TODO: create a separate 'token' column
		//TODO: throttle attempts
		$query = "SELECT * FROM admins WHERE email = ? AND hash = ?";
		$result = Sql::executeSqlForResult($query, 'ss', $email, $token);
		return Sql::hasRows($result, 1);
	}

	public static function get($uid) {
		$query = "SELECT * FROM admins WHERE uid = ?";
		$result = Sql::executeSqlForResult($query, 'i', $uid);
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		return [
				'email'     => "" . $row['email'],
				'gameAdmin' => General::getBooleanValue($row['gameAdmin']),
				'hash'      => "" . $row['hash'],
				'name'      => "" . $row['name'],
				'siteAdmin' => General::getBooleanValue($row['siteAdmin']),
				'uid'       => intval($row['uid'])
		];
	}

	public static function getByEmail($email) {
		$query = "SELECT * FROM admins WHERE email = ?";
		$result = Sql::executeSqlForResult($query, 's', trim($email));
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		return [
				'email'     => "" . $row['email'],
				'gameAdmin' => General::getBooleanValue($row['gameAdmin']),
				'hash'      => "" . $row['hash'],
				'name'      => "" . $row['name'],
				'siteAdmin' => General::getBooleanValue($row['siteAdmin']),
				'uid'       => intval($row['uid'])
		];
	}

	public static function getResetToken($admin) {
		return $admin['hash']; //TODO: use a separate 'token' column
	}

	public static function updatePassword($email, $password) {
		$query = "UPDATE admins SET hash = ? WHERE email = ?";
		return Sql::executeSqlForAffectedRows($query, 'ss', md5($password), $email);
	}
}
