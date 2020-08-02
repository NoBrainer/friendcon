<?php
namespace util;

use BadFunctionCallException as BadFunctionCallException;
use Constants as Constants;
use mysqli as mysqli;
use mysqli_result as mysqli_result;
use mysqli_stmt as mysqli_stmt;

class Sql {

	/* @var mysqli */
	private static $mysqli = null;

	/**
	 * Execute MySQL query with multiple queries joined by semicolons.
	 *
	 * @param string $query
	 * @return bool
	 * @see mysqli::multi_query()
	 */
	public static function executeMultipleSql(string $query): bool {
		return Sql::$mysqli->multi_query($query);
	}

	/**
	 * Execute MySQL query with prepared statement to prevent SQL injection.
	 *
	 * @param string $query
	 * @param string|null $types
	 * @param mixed ...$params
	 * @return bool
	 * @see Sql::prepareSqlStatement()
	 */
	public static function executeSql(string $query, ?string $types = '', ...$params): bool {
		if (count(...$params) === 0) {
			return !!Sql::$mysqli->query($query);
		}
		$stmt = Sql::prepareSqlStatement($query, $types, ...$params);
		$successful = $stmt->execute();
		$stmt->close();
		return $successful;
	}

	/**
	 * Execute MySQL query with prepared statement to prevent SQL injection.
	 * Return the number of affected rows.
	 *
	 * @param string $query
	 * @param string|null $types
	 * @param mixed ...$params
	 * @return int
	 * @see Sql::prepareSqlStatement()
	 */
	public static function executeSqlForAffectedRows(string $query, ?string $types = '', ...$params): int {
		if (count(...$params) === 0) {
			Sql::$mysqli->query($query);
			return Sql::$mysqli->affected_rows;
		}
		$stmt = Sql::prepareSqlStatement($query, $types, ...$params);
		$stmt->execute();
		$affectedRows = $stmt->affected_rows;
		$stmt->close();
		return $affectedRows;
	}

	/**
	 * Execute MySQL query with prepared statement to prevent SQL injection.
	 * Return the info array.
	 *
	 * @param string $query
	 * @param string|null $types
	 * @param mixed ...$params
	 * @return array
	 * @see Sql::mysqliInfoArray()
	 * @see Sql::prepareSqlStatement()
	 */
	public static function executeSqlForInfo(string $query, ?string $types = '', ...$params): array {
		if (count(...$params) === 0) {
			Sql::$mysqli->query($query);
		} else {
			$stmt = Sql::prepareSqlStatement($query, $types, ...$params);
			$stmt->execute();
			$stmt->close();
		}
		$info = Sql::mysqliInfoArray();
		return [
				'matched'  => Param::asInteger($info["Rows matched"]),
				'changed'  => Param::asInteger($info["Changed"]),
				'warnings' => Param::asInteger($info["Warnings"])
		];
	}

	/**
	 * Execute MySQL query with prepared statement to prevent SQL injection.
	 * Return the result.
	 *
	 * @param string $query
	 * @param string|null $types
	 * @param mixed ...$params
	 * @return mysqli_result
	 * @see Sql::prepareSqlStatement()
	 */
	public static function executeSqlForResult(string $query, ?string $types = '', ...$params): mysqli_result {
		if (count(...$params) === 0) {
			return Sql::$mysqli->query($query);
		}
		$stmt = Sql::prepareSqlStatement($query, $types, ...$params);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result;
	}

	/**
	 * Get the next row from a MySQL call's result.
	 *
	 * @param mysqli_result|null $result
	 * @return array|null
	 */
	public static function getNextRow(?mysqli_result $result = null): ?array {
		return is_null($result) ? null : $result->fetch_assoc();
	}

	/**
	 * Check if the result has 1+ rows. If a number is provided, check if the result has exactly that amount of rows.
	 *
	 * @param mysqli_result|null $result
	 * @param int $num - number of rows we're checking for (Default: -1, meaning we only care about 1+ rows)
	 * @return bool
	 */
	public static function hasRows(?mysqli_result $result = null, int $num = -1): bool {
		if (is_null($result)) return false;
		return $num < 0 ? $result->num_rows > 0 : $result->num_rows == $num;
	}

	/**
	 * Initialize the database connection.
	 * @see mysqli::__construct
	 */
	public static function initializeConnection(): void {
		// Variables in this config file:
		// - $DB - an object with database initialization parameters
		$DB = null;
		include(Constants::dbConfig());

		Sql::$mysqli = new mysqli($DB['HOST'], $DB['USER'], $DB['PASS'], $DB['NAME']);
		unset($DB); //Remove sensitive info from memory

		if (Sql::$mysqli->connect_error) {
			die("Error connecting to the database");
		}

		// Report all errors, converting them to the mysqli_sql_exception class
		// Note: Since this is after the data connection error, we won't ever print the username/password.
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		// Set the specific UTF8 character set
		Sql::$mysqli->set_charset("utf8mb4");
	}

	/**
	 * Convert $mysqli->info string format ("Rows matched: x Changed: y Warnings: z") into an array to make it more useful.
	 *
	 * @return array
	 * @see mysqli::$info
	 */
	public static function mysqliInfoArray(): array {
		preg_match_all('/(\S[^:]+): (\d+)/', Sql::$mysqli->info, $matches);
		return array_combine($matches[1], $matches[2]);
	}

	/**
	 * Create a prepared statement and bind parameters if they're provided.
	 *
	 * @param string $query - MySQL query string
	 * @param string|null $types - A string that contains one or more characters which specify the types for the corresponding
	 * bind variables: i=integer, d=double, s=string, b=blob. (Default: '')
	 * For example: "iisd" is two integers followed by a string then a double.
	 * @param mixed $params - The corresponding variables for each character in $types.
	 * @return mysqli_stmt
	 * @see mysqli::prepare()
	 */
	public static function prepareSqlStatement(string $query, ?string $types = '', ...$params): mysqli_stmt {
		$stmt = Sql::$mysqli->prepare($query);
		if ($stmt === false) throw new BadFunctionCallException("Error preparing SQL statement [$query]");
		$stmt->bind_param($types, ...$params);
		return $stmt;
	}
}
