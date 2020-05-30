<?php
namespace dao;

use BadFunctionCallException as BadFunctionCallException;
use LogicException as LogicException;
use util\Param as Param;
use util\Session as Session;
use util\Sql as Sql;

class Globals {

	// Game Globals
	public const IS_GAME_ENABLED = 'isGameEnabled';
	public const IS_GAME_SCORE_ENABLED = 'isGameScoreEnabled';
	public const IS_GAME_TEAMS_ENABLED = 'isGameTeamsEnabled';
	public const GAME_GLOBALS = [Globals::IS_GAME_ENABLED, Globals::IS_GAME_SCORE_ENABLED,
			Globals::IS_GAME_TEAMS_ENABLED];

	// Site Globals
	// TBD
	public const SITE_GLOBALS = [];

	// Types
	private const BOOLEAN = 'boolean';
	private const INTEGER = 'integer';
	private const STRING = 'string';
	private const VALID_TYPES = [Globals::BOOLEAN, Globals::INTEGER, Globals::STRING];

	public static function asType($value, $type) {
		if ($type === Globals::BOOLEAN) {
			return Param::asBoolean($value);
		} else if ($type === Globals::INTEGER) {
			return Param::asInteger($value);
		} else {
			return Param::asString($value);
		}
	}

	public static function create($name, $value, $type, $description) {
		if (!Session::$isSiteAdmin) return null;
		if (!Globals::isValidType($type)) throw new BadFunctionCallException("Invalid type [$type].");
		$query = "INSERT INTO globals (name, value, type, description) VALUES (?, ?, ?, ?)";
		$type = strtolower($type);
		$value = Globals::asType($value, $type);
		if ($type === Globals::BOOLEAN || $type === Globals::INTEGER) {
			$types = 'siss';
		} else {
			$types = 'ssss';
		}
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, $name, $value, $type, $description);
		return $affectedRows === 1;
	}

	public static function delete($name) {
		if (!Session::$isSiteAdmin) return null;
		$query = "DELETE FROM globals WHERE name = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 's', $name);
		return $affectedRows === 1;
	}

	public static function exists($name) {
		$query = "SELECT * FROM globals WHERE name = ?";
		$result = Sql::executeSqlForResult($query, 's', $name);
		return Sql::hasRows($result);
	}

	public static function get($name = null) {
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
		$obj['value'] = Globals::asType($row['value'], $obj['type']);
		return $obj;
	}

	public static function getAll($asMap = false) {
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
			$obj['value'] = Globals::asType($row['value'], $obj['type']);

			if ($asMap) {
				$name = $obj['name'];
				$globals[$name] = $obj;
			} else {
				$globals[] = $obj;
			}
		}
		return $globals;
	}

	public static function isBooleanType($type) {
		return $type === Globals::BOOLEAN;
	}

	public static function isIntegerType($type) {
		return $type === Globals::INTEGER;
	}

	public static function isStringType($type) {
		return $type === Globals::STRING;
	}

	public static function isValidType($type = null) {
		return in_array($type, Globals::VALID_TYPES);
	}

	public static function update($name, $type, $value, $description) {
		if (is_null($name)) throw new BadFunctionCallException("Cannot set a global without a name.");
		if (!Globals::exists($name)) throw new LogicException("Must create a global before updating it [$name].");
		if (!Globals::isValidType($type)) throw new BadFunctionCallException("Invalid type [$type].");
		$query = "UPDATE globals SET value = ?, description = ? WHERE name = ?";
		$type = strtolower($type);
		$types = ($type === Globals::INTEGER || $type === Globals::BOOLEAN) ? 'iss' : 'sss';
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, $value, $description, $name);
		return $affectedRows === 1;
	}
}
