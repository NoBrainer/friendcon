<?php
namespace fun\classes\dao;

use fun\classes\util\{Param as Param, Sql as Sql};

class Admins {

	public static function add(string $name, string $email, bool $isSiteAdmin = false, bool $isGameAdmin = false): bool {
		$hash = Admins::generateRandomHash($email);
		$query = "INSERT INTO admins (name, email, siteAdmin, gameAdmin, hash) VALUES (?, ?, ?, ?, ?)";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 'ssiis', $name, $email, $isSiteAdmin, $isGameAdmin, $hash);
		return $affectedRows === 1;
	}

	public static function checkPassword(array $admin, string $password): bool {
		return md5($password) === $admin['hash'];
	}

	public static function delete(string $uid): bool {
		$query = "DELETE FROM admins WHERE uid = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 'i', $uid);
		return $affectedRows === 1;
	}

	public static function exists(string $uid): bool {
		$query = "SELECT * FROM admins WHERE uid = ?";
		$result = Sql::executeSqlForResult($query, 'i', $uid);
		return Sql::hasRows($result, 1);
	}

	public static function existsWithEmail(string $email): bool {
		$query = "SELECT * FROM admins WHERE email = ?";
		$result = Sql::executeSqlForResult($query, 's', $email);
		return Sql::hasRows($result, 1);
	}

	public static function existsWithResetToken(string $email, string $token): bool {
		//TODO: create a separate 'token' column
		//TODO: throttle attempts
		$query = "SELECT * FROM admins WHERE email = ? AND hash = ?";
		$result = Sql::executeSqlForResult($query, 'ss', $email, $token);
		return Sql::hasRows($result, 1);
	}

	private static function generateRandomHash(string $str = ""): string {
		$uid = uniqid("$str");
		return md5($uid);
	}

	public static function get(string $uid, bool $includeHash = false): ?array {
		$query = "SELECT * FROM admins WHERE uid = ?";
		$result = Sql::executeSqlForResult($query, 'i', $uid);
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		$admin = [
				'email'     => Param::asString($row['email']),
				'gameAdmin' => Param::asBoolean($row['gameAdmin']),
				'name'      => Param::asString($row['name']),
				'siteAdmin' => Param::asBoolean($row['siteAdmin']),
				'uid'       => Param::asInteger($row['uid'])
		];
		if ($includeHash) $admin['hash'] = Param::asString($row['hash']);
		return $admin;
	}

	public static function getAll(): array {
		$query = "SELECT * FROM admins";
		$result = Sql::executeSqlForResult($query);

		// Build the data array
		$admins = [];
		while ($row = Sql::getNextRow($result)) {
			// Build and append the entry
			$admins[] = [
					'uid'       => Param::asInteger($row['uid']),
					'name'      => Param::asString($row['name']),
					'email'     => Param::asString($row['email']),
					'gameAdmin' => Param::asBoolean($row['gameAdmin']),
					'siteAdmin' => Param::asBoolean($row['siteAdmin'])
			];
		}
		return $admins;
	}

	public static function getByEmail(string $email, bool $includeHash = false): ?array {
		$query = "SELECT * FROM admins WHERE email = ?";
		$result = Sql::executeSqlForResult($query, 's', trim($email));
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		$admin = [
				'email'     => Param::asString($row['email']),
				'gameAdmin' => Param::asBoolean($row['gameAdmin']),
				'name'      => Param::asString($row['name']),
				'siteAdmin' => Param::asBoolean($row['siteAdmin']),
				'uid'       => Param::asInteger($row['uid'])
		];
		if ($includeHash) $admin['hash'] = Param::asString($row['hash']);
		return $admin;
	}

	public static function getResetToken(array $admin): string {
		return $admin['hash']; //TODO: use a separate 'token' column
	}

	public static function getResetTokenByEmail(string $email): string {
		$admin = Admins::getByEmail($email, true);
		return Admins::getResetToken($admin);
	}

	public static function updatePassword(string $email, string $password): bool {
		$query = "UPDATE admins SET hash = ? WHERE email = ?";
		return Sql::executeSqlForAffectedRows($query, 'ss', md5($password), $email);
	}

	public static function update(string $uid, string $name, string $email, bool $isSiteAdmin, bool $isGameAdmin): bool {
		$query = "UPDATE admins SET name = ?, email = ?, siteAdmin =?, gameAdmin = ? WHERE uid = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 'ssiii', $name, $email, $isSiteAdmin, $isGameAdmin, $uid);
		return $affectedRows === 1;
	}
}
