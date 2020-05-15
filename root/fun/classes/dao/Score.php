<?php

namespace dao;

use util\General as General;
use util\Sql as Sql;

class Score {

	public static function getChangeLogEntries() {
		$result = Sql::executeSqlForResult("SELECT * FROM scoreChanges");
		$entries = [];
		while ($row = Sql::getNextRow($result)) {
			$entry = [
					'updateTime'     => General::stringToDate($row['updateTime']),
					'teamIndex'      => intval($row['teamIndex']),
					'delta'          => intval($row['delta']),
					'challengeIndex' => null
			];
			if (!is_null($row['challengeIndex'] && is_numeric($row['challengeIndex']))) {
				$entry['challengeIndex'] = intval($row['challengeIndex']);
			}

			$entries[] = $entry;
		}
		return $entries;
	}

	public static function update($teamIndex, $delta, $challengeIndex = null) {
		if ($delta === 0) return false;

		// Build the SQL pieces
		$fields = ['teamIndex', 'delta'];
		$values = ['?', '?'];
		$types = 'ii';
		$params = [$teamIndex, $delta];
		if (!is_null($challengeIndex)) {
			$fields[] = "challengeIndex";
			$values[] = "?";
			$types .= 'i';
			$params[] = "$challengeIndex";
		}
		$fieldStr = join(", ", $fields);
		$valueStr = join(", ", $values);

		// Add an entry in the change log
		$query = "INSERT INTO scoreChanges ($fieldStr) VALUES ($valueStr)";
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
		if ($affectedRows !== 1) {
			return false;
		}

		// Update the team score to reflect the change
		$query = "UPDATE teams SET score = score + ? WHERE teamIndex = ?";
		Sql::executeSql($query, 'ii', $delta, $teamIndex);

		return true;
	}
}
