<?php
namespace fun\classes\dao;

use BadFunctionCallException as BadFunctionCallException;
use fun\classes\util\{Param as Param, Session as Session, Sql as Sql};
use LogicException as LogicException;

class Globals {

	// Game Globals
	public const IS_GAME_ENABLED = 'isGameEnabled';
	public const IS_GAME_SCORE_ENABLED = 'isGameScoreEnabled';
	public const IS_GAME_TEAMS_ENABLED = 'isGameTeamsEnabled';
	public const GAME_GLOBALS = [self::IS_GAME_ENABLED, self::IS_GAME_SCORE_ENABLED,
			self::IS_GAME_TEAMS_ENABLED];

	// Site Globals
	// TBD
	public const SITE_GLOBALS = [];

	// Types
	private const BOOLEAN = 'boolean';
	private const INTEGER = 'integer';
	private const STRING = 'string';
	private const VALID_TYPES = [self::BOOLEAN, self::INTEGER, self::STRING];

	public static function asType($value, string $type) {
		if ($type === self::BOOLEAN) {
			return Param::asBoolean($value);
		} else if ($type === self::INTEGER) {
			return Param::asInteger($value);
		} else {
			return Param::asString($value);
		}
	}

	public static function create(string $name, $value, string $type, string $description): ?bool {
		if (!Session::$isSiteAdmin) return null;
		if (!self::isValidType($type)) throw new BadFunctionCallException("Invalid type [$type].");
		$query = "INSERT INTO globals (name, value, type, description) VALUES (?, ?, ?, ?)";
		$type = strtolower($type);
		$value = self::asType($value, $type);
		if ($type === self::BOOLEAN || $type === self::INTEGER) {
			$types = 'siss';
		} else {
			$types = 'ssss';
		}
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, $name, $value, $type, $description);
		return $affectedRows === 1;
	}

	public static function delete(string $name): ?bool {
		if (!Session::$isSiteAdmin) return null;
		$query = "DELETE FROM globals WHERE name = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 's', $name);
		return $affectedRows === 1;
	}

	public static function exists(string $name): bool {
		$query = "SELECT * FROM globals WHERE name = ?";
		$result = Sql::executeSqlForResult($query, 's', $name);
		return Sql::hasRows($result);
	}

	public static function get(?string $name = null) {
		if (is_null($name)) throw new BadFunctionCallException("Cannot get a global without a name.");
		$query = "SELECT * FROM globals WHERE name = ?";
		$result = Sql::executeSqlForResult($query, 's', $name);
		if (!Sql::hasRows($result)) return false;
		$row = Sql::getNextRow($result);
		$obj = [
				'name'        => Param::asString($row['name']),
				'type'        => Param::asString($row['type']),
				'description' => Param::asString($row['description'])
		];
		$obj['value'] = self::asType($row['value'], $obj['type']);
		return $obj;
	}

	public static function getAll(bool $asMap = false): ?array {
		if (!Session::$isAdmin) return null;
		$query = "SELECT * FROM globals";
		$result = Sql::executeSqlForResult($query);

		// Build the data array/map
		$globals = [];
		while ($row = Sql::getNextRow($result)) {
			$obj = [
					'name'        => Param::asString($row['name']),
					'type'        => Param::asString($row['type']),
					'description' => Param::asString($row['description'])
			];
			$obj['value'] = self::asType($row['value'], $obj['type']);

			if ($asMap) {
				$name = $obj['name'];
				$globals[$name] = $obj;
			} else {
				$globals[] = $obj;
			}
		}
		return $globals;
	}

	public static function isBooleanType(string $type): bool {
		return $type === self::BOOLEAN;
	}

	public static function isIntegerType(string $type): bool {
		return $type === self::INTEGER;
	}

	public static function isStringType(string $type): bool {
		return $type === self::STRING;
	}

	public static function isValidType(?string $type = null): bool {
		return in_array($type, self::VALID_TYPES);
	}

	public static function update(?string $name, string $type, $value, string $description): bool {
		if (is_null($name)) throw new BadFunctionCallException("Cannot set a global without a name.");
		if (!self::exists($name)) throw new LogicException("Must create a global before updating it [$name].");
		if (!self::isValidType($type)) throw new BadFunctionCallException("Invalid type [$type].");
		$query = "UPDATE globals SET value = ?, description = ? WHERE name = ?";
		$type = strtolower($type);
		$types = ($type === self::INTEGER || $type === self::BOOLEAN) ? 'iss' : 'sss';
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, $value, $description, $name);
		return $affectedRows === 1;
	}
}
