<?php
namespace dao;

use util\Sql as Sql;

class DangerZone {

	public static function resetGameData(): bool {
		$query = <<< SQL
			TRUNCATE challenges;
			TRUNCATE scoreChanges;
			TRUNCATE teamMembers;
			TRUNCATE teams;
			TRUNCATE uploads;
			ALTER TABLE challenges AUTO_INCREMENT = 1;
			ALTER TABLE teams AUTO_INCREMENT = 1;
		SQL;
		return Sql::executeMultipleSql($query);
	}
}
