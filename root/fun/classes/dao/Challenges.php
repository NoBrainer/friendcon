<?php

namespace dao;

use util\General as General;
use util\Sql as Sql;

class Challenges {

	public static function add($name, $startTime = null, $endTime = null) {
		// Build the SQL pieces
		$fields = ["name"];
		$values = ["?"];
		$types = 's';
		$params = ["$name"];
		if (is_null($startTime)) {
			$fields[] = "startTime";
			$values[] = "?";
			$types .= 's';
			$params[] = General::stringToDate($startTime);
		}
		if (is_null($startTime)) {
			$fields[] = "endTime";
			$values[] = "?";
			$types .= 's';
			$params[] = General::stringToDate($endTime);
		}
		$fieldStr = join(", ", $fields);
		$valueStr = join(", ", $values);

		// Make the changes
		$query = "INSERT INTO challenges ($fieldStr) VALUES ($valueStr)";
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
		return $affectedRows === 1;
	}

	public static function delete($challengeIndex) {
		$query = "DELETE FROM challenges WHERE challengeIndex = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 'i', $challengeIndex);
		return $affectedRows === 1;
	}

	public static function exists($challengeIndex) {
		$result = Sql::executeSqlForResult("SELECT * FROM challenges WHERE challengeIndex = ?", 's', $challengeIndex);
		return $result->num_rows > 0;
	}

	public static function existsWithName($name) {
		$result = Sql::executeSqlForResult("SELECT * FROM challenges WHERE name = ?", 's', $name);
		return $result->num_rows > 0;
	}

	public static function get($challengeIndex) {
		$query = "SELECT * FROM challenges WHERE challengeIndex = ?";
		$result = Sql::executeSqlForResult($query, 's', $challengeIndex);
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		return [
				'challengeIndex' => intval($row['challengeIndex']),
				'name'           => "" . $row['name'],
				'startTime'      => General::stringToDate($row['startTime']),
				'endTime'        => General::stringToDate($row['endTime']),
				'published'      => boolval($row['published'])
		];
	}

	public static function getAll($currentOnly = true) {
		$isWithinTimeConstraints = "(startTime <= NOW() OR startTime = '0000-00-00 00:00:00' OR startTime IS NULL) AND (endTime >= NOW() OR endTime = '0000-00-00 00:00:00' OR endTime IS NULL)";
		$query = "SELECT * FROM challenges" . ($currentOnly ? " WHERE published = 1 OR ($isWithinTimeConstraints)" : "");
		$result = Sql::executeSqlForResult($query);

		// Build the data array
		$challenges = [];
		while ($row = Sql::getNextRow($result)) {
			// Build and append the entry
			$challenges[] = [
					'challengeIndex' => intval($row['challengeIndex']),
					'startTime'      => General::stringToDate($row['startTime']),
					'endTime'        => General::stringToDate($row['endTime']),
					'name'           => "" . $row['name'],
					'published'      => boolval($row['published'])
			];
		}
		return $challenges;
	}

	public static function getByName($name) {
		$query = "SELECT * FROM challenges WHERE name = ?";
		$result = Sql::executeSqlForResult($query, 's', $name);
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		return [
				'challengeIndex' => intval($row['challengeIndex']),
				'name'           => "" . $row['name'],
				'startTime'      => General::stringToDate($row['startTime']),
				'endTime'        => General::stringToDate($row['endTime']),
				'published'      => boolval($row['published'])
		];
	}

	public static function hasApprovedUploads($challengeIndex) {
		$query = "SELECT * FROM uploads WHERE challengeIndex = ? AND state > 0";
		$result = Sql::executeSqlForResult($query, 'i', $challengeIndex);
		return $result->num_rows > 0;
	}

	public static function publish($challengeIndex, $isPublished) {
		return Challenges::update($challengeIndex, null, null, null, $isPublished);
	}

	public static function update($challengeIndex, $name = null, $startTime = null, $endTime = null, $isPublished = null) {
		// Build the SQL pieces
		$changes = [];
		$types = '';
		$params = [];
		if (!is_null($name)) {
			$changes[] = "name = ?";
			$types .= 's';
			$params[] = "$name";
		}
		if (!is_null($startTime)) {
			$changes[] = "startTime = ?";
			$types .= 's';
			$params[] = General::stringToDate($startTime);
		}
		if (!is_null($endTime)) {
			$changes[] = "endTime = ?";
			$types .= 's';
			$params[] = General::stringToDate($endTime);
		}
		if (!is_null($isPublished)) {
			$changes[] = "published = ?";
			$types .= 'i';
			$params[] = General::getBooleanValue($isPublished);
		}
		$changesStr = join(", ", $changes);
		$types .= 'i';
		$params[] = $challengeIndex;

		// Make the changes
		$query = "UPDATE challenges SET $changesStr WHERE challengeIndex = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
		return $affectedRows === 1;
	}
}
