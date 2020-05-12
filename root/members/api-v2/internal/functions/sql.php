<?php
/**
 * Convert $mysqli->info string format ("Rows matched: x Changed: y Warnings: z") into an array to make it more useful.
 *
 * @param mysqli $mysqli - mysqli connection
 * @return array
 */
function mysqliInfoArray($mysqli) {
	preg_match_all('/(\S[^:]+): (\d+)/', $mysqli->info, $matches);
	return array_combine($matches[1], $matches[2]);
}

/**
 * Execute MySQL query with prepared statement to prevent SQL injection.
 * Return the number of affected rows.
 *
 * @param mysqli $mysqli
 * @param string $query
 * @param string $types
 * @param mixed ...$params
 * @return integer
 * @see prepareSqlStatement()
 */
function executeSqlForAffectedRows($mysqli, $query, $types = '', ...$params) {
	$stmt = prepareSqlStatement($mysqli, $query, $types, ...$params);
	$stmt->execute();
	$affectedRows = $stmt->affected_rows;
	$stmt->close();
	return $affectedRows;
}

/**
 * Execute MySQL query with prepared statement to prevent SQL injection.
 * Return the info array.
 *
 * @param mysqli $mysqli
 * @param string $query
 * @param string $types
 * @param mixed ...$params
 * @return array
 * @see mysqliInfoArray()
 * @see prepareSqlStatement()
 */
function executeSqlForInfo($mysqli, $query, $types = '', ...$params) {
	$stmt = prepareSqlStatement($mysqli, $query, $types, ...$params);
	$stmt->execute();
	$stmt->close();
	$info = mysqliInfoArray($mysqli);
	return [
			"matched"  => intval($info["Rows matched"]),
			"changed"  => intval($info["Changed"]),
			"warnings" => intval($info["Warnings"])
	];
}

/**
 * Execute MySQL query with prepared statement to prevent SQL injection.
 * Return the result.
 *
 * @param mysqli $mysqli
 * @param string $query
 * @param string $types
 * @param mixed ...$params
 * @return mysqli_result
 * @see prepareSqlStatement()
 */
function executeSqlForResult($mysqli, $query, $types = '', ...$params) {
	$stmt = prepareSqlStatement($mysqli, $query, $types, ...$params);
	$stmt->execute();
	$result = $stmt->get_result();
	$stmt->close();
	return $result;
}

/**
 * Execute MySQL query with prepared statement to prevent SQL injection.
 *
 * @param mysqli $mysqli
 * @param string $query
 * @param string $types
 * @param mixed ...$params
 * @see prepareSqlStatement()
 */
function executeSql($mysqli, $query, $types = '', ...$params) {
	$stmt = prepareSqlStatement($mysqli, $query, $types, ...$params);
	$successful = $stmt->execute();
	$stmt->close();
	return $successful;
}

/**
 * Create a prepared statement and bind parameters if they're provided.
 *
 * @param mysqli $mysqli - mysqli connection
 * @param string $query - MySQL query string
 * @param string $types - A string that contains one or more characters which specify the types for the corresponding
 * bind variables: i=integer, d=double, s=string, b=blob. (Default: '')
 * For example: "iisd" is two integers followed by a string then a double.
 * @param mixed $params - The corresponding variables for each character in $types.
 * @return mysqli_stmt
 * @see mysqli::prepare()
 */
function prepareSqlStatement($mysqli, $query, $types = '', ...$params) {
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($types, ...$params);
	return $stmt;
}

/**
 * Get the next row from a MySQL call's result.
 *
 * @param mysqli_result $result - mysqli result
 * @return array
 */
function getNextRow($result = null) {
	return $result == null ? null : $result->fetch_assoc();
}

/**
 * Check if the result has 1+ rows. If a number is provided, check if the result has exactly that amount of rows.
 *
 * @param mysqli_result $result - mysqli result
 * @param integer $num - number of rows we're checking for (Default: -1, meaning we only care about 1+ rows)
 * @return boolean
 */
function hasRows($result = null, $num = -1) {
	if ($result == null) return false;
	return $num < 0 ? $result->num_rows > 0 : $result->num_rows == $num;
}
