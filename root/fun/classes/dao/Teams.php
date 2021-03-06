<?php
namespace fun\classes\dao;

use fun\classes\util\{Param as Param, Sql as Sql};

class Teams {

	public static function add(string $name): bool {
		$query = "INSERT INTO teams (name) VALUES (?)";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 's', $name);
		return $affectedRows === 1;
	}

	public static function delete(int $teamIndex): bool {
		$query = "DELETE FROM teams WHERE teamIndex = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, 'i', $teamIndex);
		return $affectedRows === 1;
	}

	public static function deleteAllMembers(int $teamIndex): bool {
		$affectedRows = Sql::executeSqlForAffectedRows("DELETE FROM teamMembers WHERE teamIndex = ?", 'i', $teamIndex);
		return $affectedRows === 1;
	}

	public static function deleteMembers(int $teamIndex, array $memberNames = []): array {
		$deleteCount = 0;
		$failedNames = [];

		// Delete the team members (one at a time to support duplicates)
		foreach($memberNames as $name) {
			$name = trim($name);
			if (empty($name)) continue;

			$query = "DELETE FROM teamMembers WHERE teamIndex = ? AND name = ? LIMIT 1";
			$affectedRows = Sql::executeSqlForAffectedRows($query, 'is', $teamIndex, $name);
			if ($affectedRows === 1) {
				$deleteCount++;
			} else {
				$failedNames[] = $name;
			}
		}
		return [
				'deleteCount' => $deleteCount,
				'failedNames' => $failedNames,
				'total'       => count($memberNames)
		];
	}

	public static function exists(int $teamIndex): bool {
		$result = Sql::executeSqlForResult("SELECT * FROM teams WHERE teamIndex = ?", 'i', $teamIndex);
		return $result->num_rows > 0;
	}

	public static function existsWithName(string $name): bool {
		$result = Sql::executeSqlForResult("SELECT * FROM teams WHERE name = ?", 's', $name);
		return $result->num_rows > 0;
	}

	public static function get(int $teamIndex): ?array {
		$result = Sql::executeSqlForResult("SELECT * FROM teams WHERE teamIndex = ?", 'i', $teamIndex);
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		$team = [
				'teamIndex'  => Param::asInteger($row['teamIndex']),
				'name'       => Param::asString($row['name']),
				'score'      => Param::asInteger($row['score']),
				'updateTime' => Param::asTimestamp($row['updateTime']),
				'members'    => []
		];

		// Add the members to the team
		$result = Sql::executeSqlForResult("SELECT * FROM teamMembers WHERE teamIndex = ? ORDER BY name ASC", 'i', $teamIndex);
		while ($row = Sql::getNextRow($result)) {
			$team['members'][] = Param::asString($row['name']);
		}
		return $team;
	}

	public static function getAll(int $maxMemberCount = -1): array {
		$allTeams = [];
		$result = Sql::executeSqlForResult("SELECT * FROM teams");
		while ($row = Sql::getNextRow($result)) {
			$allTeams[] = [
					'teamIndex'  => Param::asInteger($row['teamIndex']),
					'name'       => Param::asString($row['name']),
					'score'      => Param::asInteger($row['score']),
					'updateTime' => Param::asTimestamp($row['updateTime']),
					'members'    => []
			];
		}

		// Add the members to the teams
		$result = Sql::executeSqlForResult("SELECT * FROM teamMembers ORDER BY name ASC");
		while ($row = Sql::getNextRow($result)) {
			$memberName = Param::asString($row['name']);
			$teamIndex = Param::asInteger($row['teamIndex']);

			// Add the member name to the team's members
			$key = array_search($teamIndex, array_column($allTeams, 'teamIndex'));
			$allTeams[$key]['members'][] = $memberName;
		}

		if ($maxMemberCount === -1) {
			return $allTeams;
		} else {
			$teams = [];
			foreach($allTeams as $team) {
				if (count($team['members']) <= $maxMemberCount) {
					$teams[] = $team;
				}
			}
			return $teams;
		}
	}

	public static function getInvalidMemberNames(array $memberNames): array {
		$invalidNames = [];
		foreach($memberNames as $memberName) {
			// Validate each name
			if (!self::isValidMemberName($memberName)) {
				$invalidNames[] = $memberName;
			}
		}
		return $invalidNames;
	}

	public static function getMinTeamMemberCount(): int {
		// Get the teams with member counts and figure out the least members on a single team
		$minMemberCount = null;
		$query = <<< SQL
			SELECT (
				SELECT COUNT(*)
				FROM teamMembers m
				WHERE m.teamIndex = t.teamIndex
			) AS memberCount
			FROM teams t
		SQL;
		$result = Sql::executeSqlForResult($query);
		while ($row = Sql::getNextRow($result)) {
			$memberCount = Param::asInteger($row['memberCount']);
			$minMemberCount = is_null($minMemberCount) ? $memberCount : min($minMemberCount, $memberCount);
		}
		return is_null($minMemberCount) ? 0 : $minMemberCount;
	}

	public static function getRandomTeamIndex(): int {
		// Build up the teams within 2 member count of the minimum member count
		$minMemberCount = self::getMinTeamMemberCount();
		$teamCandidates = self::getAll($minMemberCount + 2);

		// Randomly pick one of the candidates
		$randomTeam = $teamCandidates[array_rand($teamCandidates)];
		return $randomTeam['teamIndex'];
	}

	public static function hasApprovedUploads(int $teamIndex): bool {
		$query = "SELECT * FROM uploads WHERE teamIndex = ? AND state > 0";
		$result = Sql::executeSqlForResult($query, 'i', $teamIndex);
		return $result->num_rows > 0;
	}

	public static function hasMembers(int $teamIndex): bool {
		$query = "SELECT * FROM teamMembers WHERE teamIndex = ?";
		$result = Sql::executeSqlForResult($query, 'i', $teamIndex);
		return $result->num_rows > 0;
	}

	public static function isSetup(): bool {
		$result = Sql::executeSqlForResult("SELECT * FROM teams");
		return $result->num_rows > 0;
	}

	public static function isValidMemberName(?string $name): bool {
		return !preg_match("[,<>()&]", $name);
	}

	public static function isValidTeamIndex(?int $teamIndex): bool {
		return Param::isInteger($teamIndex) && $teamIndex >= 1;
	}

	public static function setMembers(int $teamIndex, array $memberNames): bool {
		// Build SQL pieces
		$valueStr = "";
		$types = "";
		$params = [];
		foreach($memberNames as $memberName) {
			$params[] = trim($memberName);
			$params[] = $teamIndex;
			$types .= 'si';
			if (!empty($valueStr)) $valueStr .= ", ";
			$valueStr .= "(?, ?)";
		}

		// Delete the previous members
		Sql::executeSql("DELETE FROM teamMembers WHERE teamIndex = ?", 'i', $teamIndex);

		// Add the updated members
		$query = "INSERT INTO teamMembers (name, teamIndex) VALUES $valueStr";
		$affectedMemberRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
		return $affectedMemberRows > 0;
	}

	public static function update(int $teamIndex, string $name = null, ?int $score = null): bool {
		// Build the SQL pieces
		$changes = [];
		$types = '';
		$params = [];
		if (!is_null($name)) {
			$changes[] = "name = ?";
			$types .= 's';
			$params[] = $name;
		}
		if (!is_null($score)) {
			$changes[] = "score = ?";
			$types .= 'i';
			$params[] = $score;
		}
		$changesStr = join(", ", $changes);
		$types .= 'i';
		$params[] = $teamIndex;

		// Make the changes
		$query = "UPDATE teams SET $changesStr WHERE teamIndex = ?";
		$affectedRows = Sql::executeSqlForAffectedRows($query, $types, ...$params);
		return $affectedRows >= 0;
	}
}
